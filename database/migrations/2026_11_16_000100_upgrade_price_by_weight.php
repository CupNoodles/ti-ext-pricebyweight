<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * v4 structure:
 * - units_of_measure: name, abbreviation and decimal places. v3's separate admin label is merged
 *   into the name, and the +/- step moves onto each menu item.
 * - menus: weighed items get their own minimum and step (weight_minimum, weight_step), so core's
 *   minimum_qty goes back to a whole number for every item, as core expects.
 * - order_menus: quantity stays decimal (real weights); uom_tag/uom_decimals become nullable so
 *   core can insert order lines on strict MySQL.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('units_of_measure', function(Blueprint $table): void {
            $table->renameColumn('long_name', 'name');
            $table->renameColumn('short_name', 'abbreviation');
        });

        Schema::table('menus', function(Blueprint $table): void {
            $table->decimal('weight_minimum', 15, 4)->nullable();
            $table->decimal('weight_step', 15, 4)->nullable();
            $table->unsignedInteger('uom_id')->nullable()->default(null)->change();
            $table->boolean('price_by_weight')->default(false)->change();
        });

        // Carry over v3's per-item minimum and per-unit step
        DB::table('menus')->where('uom_id', 0)->update(['uom_id' => null]);
        DB::table('menus')
            ->join('units_of_measure', 'units_of_measure.uom_id', '=', 'menus.uom_id')
            ->where('menus.price_by_weight', true)
            ->update([
                'menus.weight_minimum' => DB::raw(DB::getTablePrefix().'menus.minimum_qty'),
                'menus.weight_step' => DB::raw(DB::getTablePrefix().'units_of_measure.step_size'),
            ]);

        // Name gets v3's admin label (eg. "Pounds"), which is what staff picked from
        DB::table('units_of_measure')->update(['name' => DB::raw('backend_name')]);

        Schema::table('units_of_measure', function(Blueprint $table): void {
            $table->dropColumn(['backend_name', 'step_size']);
            $table->unsignedTinyInteger('decimal_places')->default(2)->change();
        });

        if (!DB::table('units_of_measure')->where('abbreviation', 'kg')->exists()) {
            DB::table('units_of_measure')->insert(['name' => 'Kilograms', 'abbreviation' => 'kg', 'decimal_places' => 3]);
        }

        DB::table('menus')->where('minimum_qty', '<', 1)->update(['minimum_qty' => 1]);
        DB::table('menus')->update(['minimum_qty' => DB::raw('CEIL(minimum_qty)')]);
        Schema::table('menus', function(Blueprint $table): void {
            $table->integer('minimum_qty')->default(1)->change();
        });

        Schema::table('order_menus', function(Blueprint $table): void {
            $table->string('uom_tag')->nullable()->default(null)->change();
            $table->unsignedTinyInteger('uom_decimals')->nullable()->default(null)->change();
        });
        DB::table('order_menus')->where('uom_tag', '')->update(['uom_tag' => null, 'uom_decimals' => null]);
    }

    public function down(): void
    {
        DB::table('order_menus')->whereNull('uom_tag')->update(['uom_tag' => '', 'uom_decimals' => 0]);
        Schema::table('order_menus', function(Blueprint $table): void {
            $table->string('uom_tag')->default('')->change();
            $table->integer('uom_decimals')->default(0)->change();
        });

        Schema::table('menus', function(Blueprint $table): void {
            $table->decimal('minimum_qty', 15, 4)->change();
        });
        DB::table('menus')->where('price_by_weight', true)->whereNotNull('weight_minimum')
            ->update(['minimum_qty' => DB::raw('weight_minimum')]);

        Schema::table('units_of_measure', function(Blueprint $table): void {
            $table->string('backend_name')->default('');
            $table->decimal('step_size')->default(1);
            $table->integer('decimal_places')->change();
        });
        DB::table('units_of_measure')->update(['backend_name' => DB::raw('name')]);
        DB::table('units_of_measure')
            ->join('menus', 'menus.uom_id', '=', 'units_of_measure.uom_id')
            ->whereNotNull('menus.weight_step')
            ->update(['units_of_measure.step_size' => DB::raw(DB::getTablePrefix().'menus.weight_step')]);

        DB::table('menus')->whereNull('uom_id')->update(['uom_id' => 0]);
        Schema::table('menus', function(Blueprint $table): void {
            $table->dropColumn(['weight_minimum', 'weight_step']);
            $table->integer('uom_id')->default(0)->change();
        });

        Schema::table('units_of_measure', function(Blueprint $table): void {
            $table->renameColumn('name', 'long_name');
            $table->renameColumn('abbreviation', 'short_name');
        });
    }
};
