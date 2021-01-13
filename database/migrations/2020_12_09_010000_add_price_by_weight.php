<?php

namespace CupNoodles\PriceByWeight\Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Schema;

/**
 * 
 */
class AddPriceByWeight extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('units_of_measure')) {
            Schema::create('units_of_measure', function (Blueprint $table) {
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
            Schema::table('menus', function (Blueprint $table) {
                $table->boolean('price_by_weight');
            });
        }
        
        if (!Schema::hasColumn('menus', 'uom_id')) {
            Schema::table('menus', function (Blueprint $table) {
                $table->integer('uom_id');
            });
        }

        Schema::table('menus', function (Blueprint $table) {
            $table->decimal('stock_qty', '15', '4')->change();
            $table->decimal('minimum_qty', '15', '4')->change();
        });

        Schema::table('order_menus', function (Blueprint $table) {

            $table->string('uom_tag');
            $table->integer('uom_decimals');


            $table->decimal('quantity', '15', '4')->change();
            
        });


        $this->seedUOM();
    }

    public function down()
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->dropColumn(['price_by_weight', 'uom_id']);
        });
        Schema::dropIfExists('units_of_measure');

        Schema::table('menus', function (Blueprint $table) {
            $table->integer('stock_qty')->change();
            $table->integer('minimum_qty')->change();
        });

        Schema::table('order_menus', function (Blueprint $table) {
            $table->dropColumn(['uom_tag', 'uom_decimals']);

            $table->integer('quantity')->change();
        });

    }

    protected function seedUOM()
    {
        if (DB::table('units_of_measure')->count())
            return;

        DB::table('units_of_measure')->insert(json_decode(file_get_contents(__DIR__.'/../records/uom_seed.json'), TRUE));
    }

}
