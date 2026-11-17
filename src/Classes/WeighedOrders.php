<?php

declare(strict_types=1);

namespace CupNoodles\PriceByWeight\Classes;

use Igniter\Cart\Models\Menu;
use Igniter\Cart\Models\Order;
use Igniter\Cart\Models\OrderMenu;
use Illuminate\Support\Facades\Event;
use WeakMap;

/**
 * Keeps orders with items sold by weight looking normal to everything else.
 *
 * Order lines store the real weight (order_menus.quantity is decimal), so reports, emails,
 * invoices, the API and payment integrations see "0.33" at the price per unit. Three places in
 * core assume whole numbers and are handled here:
 * - OrderMenu casts quantity to int: fractional quantities are returned as floats instead.
 * - total_items is a SUM of quantities: recounted with each weighed line as one item.
 * - Stock::updateStockSold() only takes ints: while core subtracts stock, weighed lines are
 *   given whole units of the unit's precision (0.33 lb -> 33), then restored. Stock for items
 *   sold by weight is therefore counted in those units if tracking is ever switched on.
 */
class WeighedOrders
{
    public const string PAYMENT_EVENT = 'admin.order.beforePaymentProcessed';

    protected static ?WeakMap $realQuantities = null;

    public static function boot(): void
    {
        OrderMenu::extend(function(OrderMenu $model): void {
            $model->bindEvent('model.getAttribute', function($key, $value) use ($model) {
                return $key === 'quantity' ? self::realQuantity($model) : null;
            });

            $model->bindEvent('model.beforeCreate', function() use ($model): void {
                self::recordUnit($model);
            });
        });

        Event::listen('igniter.checkout.afterSaveOrder', function(Order $order): void {
            self::recountItems($order);
        });

        // Run before core's stock listener on this event, and restore after it
        app()->booted(function(): void {
            $events = app('events');
            $existing = $events->getRawListeners()[self::PAYMENT_EVENT] ?? [];
            $events->forget(self::PAYMENT_EVENT);
            $events->listen(self::PAYMENT_EVENT, fn(Order $order) => self::useStockUnits($order));
            foreach ($existing as $listener) {
                $events->listen(self::PAYMENT_EVENT, $listener);
            }
            $events->listen(self::PAYMENT_EVENT, fn(Order $order) => self::restoreQuantities($order));
        });
    }

    /** Float for fractional quantities (0.33), otherwise null so core's int cast applies */
    public static function realQuantity(OrderMenu $orderMenu): ?float
    {
        $raw = $orderMenu->getAttributes()['quantity'] ?? null;
        if (!is_numeric($raw) || fmod((float)$raw, 1.0) == 0.0) {
            return null;
        }

        return (float)$raw;
    }

    protected static function recordUnit(OrderMenu $orderMenu): void
    {
        $menu = Menu::find($orderMenu->menu_id);
        if (!Weight::isWeighed($menu)) {
            return;
        }

        $unit = Weight::unit($menu);
        $orderMenu->uom_tag = $unit->abbreviation;
        $orderMenu->uom_decimals = $unit->decimal_places;
    }

    protected static function recountItems(Order $order): void
    {
        $totalItems = $order->menus()->get()->sum(
            fn(OrderMenu $line): int|float => $line->uom_tag ? 1 : $line->quantity,
        );

        $order->newQuery()->whereKey($order->getKey())->update(['total_items' => (int)$totalItems]);
        $order->total_items = (int)$totalItems;
    }

    protected static function useStockUnits(Order $order): void
    {
        self::$realQuantities ??= new WeakMap;

        foreach ($order->menus as $line) {
            $real = self::realQuantity($line);
            if ($real === null) {
                continue;
            }

            self::$realQuantities[$line] = $line->getAttributes()['quantity'];
            $units = (int)round($real * 10 ** (int)($line->uom_decimals ?? 2));
            $line->setRawAttributes(array_merge($line->getAttributes(), ['quantity' => $units]), true);
        }
    }

    protected static function restoreQuantities(Order $order): void
    {
        foreach ($order->menus as $line) {
            if (self::$realQuantities !== null && isset(self::$realQuantities[$line])) {
                $line->setRawAttributes(array_merge($line->getAttributes(), ['quantity' => self::$realQuantities[$line]]), true);
                unset(self::$realQuantities[$line]);
            }
        }
    }
}
