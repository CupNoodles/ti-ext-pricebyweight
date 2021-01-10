<?php

namespace CupNoodles\PriceByWeight\Models;

use Igniter\Cart\Models\Menus_model as BaseMenusModel;

/**
 * Menus Model Class
 */
class Menus_model extends BaseMenusModel
{
    protected $casts = [
        'menu_price' => 'float',
        'menu_category_id' => 'integer',
        'stock_qty' => 'float',
        'minimum_qty' => 'float',
        'subtract_stock' => 'boolean',
        'order_restriction' => 'integer',
        'menu_status' => 'boolean',
        'menu_priority' => 'integer',
    ];

    
}