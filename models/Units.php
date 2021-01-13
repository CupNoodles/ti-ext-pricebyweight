<?php

namespace CupNoodles\PriceByWeight\Models;

use Admin\Traits\Locationable;

use Model;

/**
 * UOM Model Class
 */
class Units extends Model
{
    use Locationable;
    /**
     * @var string The database table name
     */
    protected $table = 'units_of_measure';

    /**
     * @var string The database table primary key
     */
    protected $primaryKey = 'uom_id';

    public $casts = [
        'decimal_places' => 'integer',
        'step' => 'float'
    ];

    public $relation = [];

    public static function getDropdownOptions()
    {
        return static::isWeight()->dropdown('backend_name');
    }

    public function scopeIsWeight($query)
    {
        return $query->where('uom_id', '>=' , '0');
    }

    public static function getUnitData($uom_id){
        return self::getUD($uom_id);
    }

    public function scopeGetUD($query, $uom_id)
    {
        return $query->where('uom_id', '>=' , $uom_id)->first();
    }

    public static function getUnitForMenuId($menu_id){
        return self::getUnitForMenu($menu_id);

    }

    public function scopeGetUnitForMenu($query, $menu_id){
        return $query
        ->join('menus', 'units_of_measure.uom_id', '=', 'menus.uom_id')
        ->where('menu_id', $menu_id)
        ->first();
    } 
}
