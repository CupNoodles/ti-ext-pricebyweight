<?php

namespace CupNoodles\PriceByWeight\Components;

use Igniter\Cart\Components\Checkout;
use CupNoodles\PriceByWeight\Classes\OrderManagerByWeight as OrderManager;
use CupNoodles\PriceByWeight\Classes\CartManagerByWeight as CartManager;    

class CheckoutByWeight extends Checkout{
    public function initialize()
    {
        $this->orderManager = OrderManager::instance();
        $this->cartManager = CartManager::instance();
    }

    public function onRender()
    {
        foreach ($this->getPaymentGateways() as $paymentGateway) {
            $paymentGateway->beforeRenderPaymentForm($paymentGateway, $this->controller);
        }

        $this->addJs('$/igniter/cart/assets/js/checkout.js', 'checkout-js');
    }


}