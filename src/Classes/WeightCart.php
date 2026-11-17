<?php

declare(strict_types=1);

namespace CupNoodles\PriceByWeight\Classes;

use Igniter\Cart\Cart;
use Igniter\Cart\CartItem;
use Override;

/**
 * Core's cart types quantities as int in add() and createCartItem(), and count() returns an int
 * sum of quantities. Items sold by weight need decimal quantities, so this widens those two
 * methods (bodies unchanged from igniter.cart v4.5) and counts each weighed line as one item.
 */
class WeightCart extends Cart
{
    #[Override]
    public function add($buyable, int|float $qty = 0, array $options = [], $comment = null): array|CartItem
    {
        if ($this->isMulti($buyable)) {
            return array_map(fn($item): CartItem|array => $this->add($item), $buyable);
        }

        $cartItem = $this->createCartItem($buyable, $qty, $options, $comment);

        $this->fireEvent('adding', $cartItem);

        $content = $this->getContent();

        if ($content->has($cartItem->rowId)) {
            $cartItem->qty += $content->get($cartItem->rowId)->qty;
        }

        $this->applyAllConditionsToItem($cartItem);

        $content->put($cartItem->rowId, $cartItem);

        $this->putSession('content', $content);

        $this->fireEvent('added', $cartItem);

        return $cartItem;
    }

    /**
     * Number of items: weighed lines count once each (1.5 lb of steak is one item).
     */
    #[Override]
    public function count(): int
    {
        return (int)$this->getContent()->sum(
            fn(CartItem $cartItem): int|float => Weight::isWeighedCartItem($cartItem) ? 1 : $cartItem->qty,
        );
    }

    /**
     * Core builds the item; only the quantity (which core types as int) is set here.
     */
    #[Override]
    protected function createCartItem($buyable, int|float $qty = 0, array $options = [], $comment = null): CartItem
    {
        $cartItem = parent::createCartItem($buyable, 0, $options, $comment);

        if (!is_array($buyable) || !array_key_exists('qty', $buyable)) {
            $cartItem->setQuantity($qty);
        }

        return $cartItem;
    }
}
