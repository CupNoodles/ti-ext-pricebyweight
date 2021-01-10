<?php
namespace CupNoodles\PriceByWeight\Components;

use Igniter\Cart\Components\CartBox;

use ApplicationException;
use Cart;
use Exception;
//use Igniter\Cart\Classes\CartManager;
use CupNoodles\PriceByWeight\Classes\CartManagerByWeight as CartManager;
use Igniter\Cart\Models\CartSettings;
use Location;
use Redirect;
use Request;

class CartBoxByWeight extends CartBox
{
    public function initialize()
    {
        $this->cartManager = CartManager::instance()->checkStock(
            (bool)$this->property('checkStockCheckout', TRUE)
        );
    }

    public function onRemoveItem()
    {
        try {
            $rowId = (string)post('rowId');
            $quantity = (float)post('quantity');

            $this->cartManager->updateCartItemQty($rowId, $quantity);

            $this->controller->pageCycle();

            return $this->fetchPartials();
        }
        catch (Exception $ex) {
            if (Request::ajax()) throw $ex;
            else flash()->alert($ex->getMessage());
        }
    }

}