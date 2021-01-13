<?php
namespace CupNoodles\PriceByWeight\Components;

use Igniter\Cart\Components\CartBox;

use Exception;

use CupNoodles\PriceByWeight\Classes\CartManagerByWeight as CartManager;
use Request;

class CartBoxByWeight extends CartBox
{
    public function initialize()
    {
        $this->cartManager = CartManager::instance()->checkStock(
            (bool)$this->property('checkStockCheckout', TRUE)
        );
    }

    public function onRun()
    {
        $this->addJs('$/igniter/cart/assets/js/cartbox.js', 'cart-box-js');
        $this->addJs('$/igniter/cart/assets/js/cartitem.js', 'cart-item-js');
        $this->addJs('$/igniter/cart/assets/js/cartbox.modal.js', 'cart-box-modal-js');

        $this->prepareVars();
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