<?php

declare(strict_types=1);

namespace CupNoodles\PriceByWeight\Classes;

use Igniter\Cart\CartItem;
use Igniter\Cart\Classes\CartManager;
use Igniter\Cart\Models\Menu;
use Igniter\Flame\Exception\ApplicationException;
use Override;

/**
 * Decimal quantities for items sold by weight. Everything else is left to core.
 *
 * Core checks minimum quantities with the integer % operator and steps the cart's +/- buttons by
 * minimum_qty; for weighed items the item's own weight minimum and step are used instead.
 */
class WeightCartManager extends CartManager
{
    #[Override]
    public function addOrUpdateCartItem(array $postData)
    {
        $menuId = array_get($postData, 'menuId');
        $menu = is_numeric($menuId) ? $this->findMenuItem($menuId) : null;

        if (Weight::isWeighed($menu) && is_numeric($quantity = array_get($postData, 'quantity'))) {
            $postData['quantity'] = Weight::round($menu, $quantity);
        }

        return parent::addOrUpdateCartItem($postData);
    }

    #[Override]
    public function updateCartItemQty(string $rowId, $quantityOrAction = 0): CartItem
    {
        $cartItem = $this->getCartItem($rowId);
        $menu = $this->findMenuItem($cartItem->id);

        if (!Weight::isWeighed($menu)) {
            return parent::updateCartItemQty($rowId, $quantityOrAction);
        }

        $quantity = match ($quantityOrAction) {
            'plus' => $cartItem->qty + Weight::step($menu),
            'minus' => $cartItem->qty - Weight::step($menu),
            default => (float)$quantityOrAction,
        };

        // Going below the minimum removes the item, as core does for normal items
        $quantity = Weight::round($menu, $quantity);
        if ($quantity < Weight::minimum($menu)) {
            $quantity = 0;
        }

        return $this->cart->update($rowId, $quantity);
    }

    #[Override]
    public function validateMenuItemMinQty(Menu $menuItem, $quantity): void
    {
        if (!Weight::isWeighed($menuItem)) {
            parent::validateMenuItemMinQty($menuItem, $quantity);

            return;
        }

        $minimum = Weight::minimum($menuItem);
        if ((float)$quantity > 0 && (float)$quantity + 1e-9 < $minimum) {
            throw new ApplicationException(sprintf(
                lang('igniter.cart::default.alert_qty_is_below_min_qty'),
                Weight::format($menuItem, $minimum),
            ));
        }
    }
}
