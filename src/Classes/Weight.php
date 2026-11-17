<?php

declare(strict_types=1);

namespace CupNoodles\PriceByWeight\Classes;

use CupNoodles\PriceByWeight\Models\Unit;
use Igniter\Cart\CartItem;
use Igniter\Cart\Models\Menu;

/**
 * Rules for items sold by weight, shared by the cart, the weight popup and the order shims.
 */
class Weight
{
    public static function isWeighed(?Menu $menu): bool
    {
        return $menu && $menu->price_by_weight && $menu->weight_unit;
    }

    public static function unit(Menu $menu): ?Unit
    {
        return self::isWeighed($menu) ? $menu->weight_unit : null;
    }

    public static function minimum(Menu $menu): float
    {
        $unit = self::unit($menu);

        return max((float)$menu->weight_minimum, $unit?->precision() ?? 0);
    }

    /** Amount the +/- buttons add or remove */
    public static function step(Menu $menu): float
    {
        return (float)$menu->weight_step ?: (self::unit($menu)?->precision() ?? 1);
    }

    /** Round a weight to what the unit allows, eg. 0.333 lb -> 0.33 */
    public static function round(Menu $menu, float|int|string $weight): float
    {
        return round((float)$weight, self::unit($menu)?->decimal_places ?? 0);
    }

    public static function format(Menu $menu, float|int|string $weight): string
    {
        return self::unit($menu)?->format($weight) ?? (string)$weight;
    }

    public static function isWeighedCartItem(CartItem $cartItem): bool
    {
        $menu = $cartItem->model;

        return $menu instanceof Menu && self::isWeighed($menu);
    }

    public static function cartHasWeighedItems(): bool
    {
        foreach (resolve('cart')->content() as $cartItem) {
            if (self::isWeighedCartItem($cartItem)) {
                return true;
            }
        }

        return false;
    }
}
