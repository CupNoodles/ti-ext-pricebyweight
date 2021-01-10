<?php

namespace CupNoodles\PriceByWeight\Components;

use Igniter\Cart\Components\Checkout;
use Igniter\Cart\Classes\OrderManager;
use CupNoodles\PriceByWeight\Classes\CartManagerByWeight as CartManager;    

class CheckoutByWeight extends Checkout{
    public function initialize()
    {
        $this->orderManager = OrderManager::instance();
        $this->cartManager = CartManager::instance();
    }
}