<?php

namespace CupNoodles\PriceByWeight\Components;

use Igniter\Local\Components\Menu;

use CupNoodles\PriceByWeight\Models\Units;

class MenuByWeight extends Menu{


    public function onRun()
    {   
        parent::onRun();
        $this->addComponent('localMenu', 'menuAlias', $this->properties);
        $this->page['menuList'] = $this->loadList();
        $this->page['menuListCategories'] = $this->menuListCategories;
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
