<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Original v3 schema, kept so existing installs keep their migration history.
 * 2026_11_16_000100_upgrade_price_by_weight brings it to the v4 structure.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('units_of_measure')) {
            Schema::create('units_of_measure', function(Blueprint $table): void {
                $table->engine = 'InnoDB';
                $table->increments('uom_id');
                $table->string('long_name');
                $table->string('short_name');
                $table->string('backend_name');
                $table->integer('decimal_places');
                $table->decimal('step_size');
            });
        }

        if (!Schema::hasColumn('menus', 'price_by_weight')) {
            Schema::table('menus', function(Blueprint $table): void {
                $table->boolean('price_by_weight')->default(false);
            });
        }

        if (!Schema::hasColumn('menus', 'uom_id')) {
            Schema::table('menus', function(Blueprint $table): void {
                $table->integer('uom_id')->default(0);
            });
        }

        Schema::table('menus', function(Blueprint $table): void {
            $table->decimal('minimum_qty', 15, 4)->change();
        });

        if (!Schema::hasColumn('order_menus', 'uom_tag')) {
            Schema::table('order_menus', function(Blueprint $table): void {
                $table->string('uom_tag')->default('');
                $table->integer('uom_decimals')->default(0);
            });
        }

        Schema::table('order_menus', function(Blueprint $table): void {
            $table->decimal('quantity', 15, 4)->change();
        });

        if (!DB::table('units_of_measure')->count()) {
            DB::table('units_of_measure')->insert([
                ['long_name' => 'pound', 'short_name' => 'lb', 'backend_name' => 'Pounds', 'decimal_places' => 2, 'step_size' => 0.25],
                ['long_name' => 'ounce', 'short_name' => 'oz', 'backend_name' => 'Ounces', 'decimal_places' => 0, 'step_size' => 1],
                ['long_name' => 'gram', 'short_name' => 'g', 'backend_name' => 'Grams', 'decimal_places' => 0, 'step_size' => 1],
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('order_menus', function(Blueprint $table): void {
            $table->dropColumn(['uom_tag', 'uom_decimals']);
            $table->integer('quantity')->change();
        });

        Schema::table('menus', function(Blueprint $table): void {
            $table->dropColumn(['price_by_weight', 'uom_id']);
            $table->integer('minimum_qty')->change();
        });

        Schema::dropIfExists('units_of_measure');
    }
};
