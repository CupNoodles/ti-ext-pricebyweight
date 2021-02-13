<?php

namespace CupNoodles\PriceByWeight\Models;

use Igniter\Cart\Models\Orders_model as IgniterCartOrdersModel;

class Orders_model extends IgniterCartOrdersModel{

   public function mailGetData()
   {
       
       $data = parent::mailGetData();
       
       $menus = $this->getOrderMenus();
       
       foreach($menus as $ix=>$menu){
        
            if(isset($menu->uom_tag) && $menu->uom_tag != '' 
            && isset($menu->uom_decimals)){
                $data['order_menus'][$ix]['menu_quantity'] = number_format($data['order_menus'][$ix]['menu_quantity'], $menu->uom_decimals) . ' ' . $menu->uom_tag;
                $data['order_menus'][$ix]['menu_price'] = $data['order_menus'][$ix]['menu_price'] . '/' . $menu->uom_tag;
            }
            else{
                $data['order_menus'][$ix]['menu_quantity'] = number_format($data['order_menus'][$ix]['menu_quantity'], 0);
            }
       }
       
       
       return $data;
   }
}