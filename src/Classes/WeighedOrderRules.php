<?php

declare(strict_types=1);

namespace CupNoodles\PriceByWeight\Classes;

use Igniter\Cart\Models\Menu;
use Igniter\Flame\Support\Facades\Igniter;

/**
 * Per-location rules for carts with items sold by weight (Restaurant > Locations > Settings >
 * Price by weight): no delivery, and/or only some payment methods (eg. pay at pickup), because
 * the final price is only known once the item is cut and weighed.
 *
 * Applied by adjusting the location's settings as the storefront reads them, so Orange and
 * core's checkout hide delivery and the other payment methods on their own.
 */
class WeighedOrderRules
{
    /** menu_id => sold by weight, for this request */
    protected static array $weighedMenus = [];

    public static function apply(array $settings): ?array
    {
        $rules = (array)($settings['pricebyweight'] ?? []);
        if (!$rules || Igniter::runningInAdmin() || !self::cartHasWeighedItems()) {
            return null;
        }

        if (!empty($rules['disable_delivery'])) {
            $settings['delivery'] = array_merge((array)($settings['delivery'] ?? []), ['is_enabled' => 0]);
        }

        if ($allowed = array_values(array_filter((array)($rules['payments'] ?? [])))) {
            $current = (array)($settings['checkout']['payments'] ?? []);
            // Keep the location's own restriction too, if it has one
            $payments = $current ? array_values(array_intersect($current, $allowed)) : $allowed;
            $settings['checkout'] = array_merge((array)($settings['checkout'] ?? []), [
                'payments' => $payments ?: ['__none__'],
            ]);
        }

        return $settings;
    }

    protected static function cartHasWeighedItems(): bool
    {
        foreach (resolve('cart')->content() as $cartItem) {
            $menuId = (int)$cartItem->id;
            self::$weighedMenus[$menuId] ??= Weight::isWeighed(Menu::find($menuId));

            if (self::$weighedMenus[$menuId]) {
                return true;
            }
        }

        return false;
    }
}
