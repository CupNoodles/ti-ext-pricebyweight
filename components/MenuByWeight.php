<?php

namespace CupNoodles\PriceByWeight\Components;

use Igniter\Local\Components\Menu;

//use Admin\Models\Menus_model;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Location;

use CupNoodles\PriceByWeight\Models\Units;
use CupNoodles\PriceByWeight\Models\Menus_model;


class MenuByWeight extends Menu{


    protected function loadList()
    {
        $list = Menus_model::with([
            'mealtimes', 'menu_options',
            'categories', 'categories.media',
            'special', 'allergens', 'media', 'allergens.media',
        ])->listFrontEnd([
            'page' => $this->param('page'),
            'pageLimit' => $this->property('menusPerPage'),
            'sort' => $this->property('sort', 'menu_priority asc'),
            'location' => $this->getLocation(),
            'category' => $this->param('category'),
            'search' => $this->getSearchTerm(),
        ]);

        $this->mapIntoObjects($list);

        if ($this->property('isGrouped'))
            $this->groupListByCategory($list);



        return $list;
    }

    public function createMenuItemObject($menuItem){
        $menuItem->casts = null;

        $object = parent::createMenuItemObject($menuItem);
        
        $object->isPriceByWeight = $object->model->price_by_weight;

        $abbr = Units::getUnitData($object->model->uom_id);
        $object->priceUnitAbbr   = $abbr->short_name;
        $casts = $object->model->casts;
        $casts['stock_qty'] = 'float';
        $object->model->casts = null;

        return $object;

    }
}
