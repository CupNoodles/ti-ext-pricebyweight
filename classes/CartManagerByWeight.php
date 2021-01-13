<?php

namespace CupNoodles\PriceByWeight\Classes;

use Igniter\Cart\Classes\CartManager;

use Igniter\Cart\Models\Menus_model;
use Igniter\Flame\Exception\ApplicationException;

class CartManagerByWeight extends CartManager
{

    public function findMenuItem($menuId)
    {
        if (!is_numeric($menuId))
            throw new ApplicationException(lang('igniter.cart::default.alert_no_menu_selected'));

        if (!$menuItem = Menus_model::find($menuId))
            throw new ApplicationException(lang('igniter.cart::default.alert_menu_not_found'));

        return $menuItem;
    }

    public function updateCartItemQty($rowId, $quantity = 0)
    {
        $cartItem = $this->getCartItem($rowId);
        $menuItem = $this->findMenuItem($cartItem->id);

        $quantity = $quantity > $menuItem->minimum_qty ? $quantity : $cartItem->qty - $menuItem->minimum_qty;

        return $this->cart->update($rowId, $quantity);
    }



    public function validateMenuItemMinQty($menuItem, $quantity)
    {
        if ($quantity == 0 OR $menuItem->minimum_qty == 0)
            return;

        // Quantity is valid if its divisive by the minimum quantity
        if (fmod($quantity,$menuItem->step_qty) > 0) {   // This s the only difference needed from CartManager::validateMenuItemMinQty
            throw new ApplicationException(sprintf(
                lang('igniter.cart::default.alert_qty_is_invalid'), $menuItem->minimum_qty
            ));
        }

        // if cart quantity is less than minimum quantity
        if (!$menuItem->checkMinQuantity($quantity)) {
            throw new ApplicationException(sprintf(
                lang('igniter.cart::default.alert_qty_is_below_min_qty'), $menuItem->minimum_qty
            ));
        }
    }

}

