<?php

namespace CupNoodles\PriceByWeight\Classes;

use Igniter\Cart\Classes\OrderManager as IgniterCartOrderManager;

use CupNoodles\PriceByWeight\Models\Orders_model;

class OrderManagerByWeight extends IgniterCartOrderManager
{
    /**
     * @return CupNoodles\PriceByWeight\Models\Orders_Model
     */
    public function loadOrder()
    {
        $id = $this->getCurrentOrderId();

        $customerId = $this->customer
            ? $this->customer->customer_id
            : null;

        $order = Orders_model::find($id);

        // Only users can view their own orders
        if (!$order OR $order->customer_id != $customerId)
            $order = Orders_model::make($this->getCustomerAttributes());

        return $order;
    }
}
