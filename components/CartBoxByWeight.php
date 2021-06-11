<?php
namespace CupNoodles\PriceByWeight\Components;

use Igniter\Cart\Components\CartBox;

use Exception;

use CupNoodles\PriceByWeight\Classes\CartManagerByWeight as CartManager;
use Request;

use Cart;
use Event;

class CartBoxByWeight extends CartBox
{
    public function initialize()
    {
        $this->cartManager = CartManager::instance()->checkStock(
            (bool)$this->property('checkStockCheckout', TRUE)
        );

        $this->addComponent('cartBox', 'cartBoxAlias',$this->properties);

        //$this->prepareVars();
    }

    public function onRun()
    {
        $this->addJs('$/igniter/cart/assets/js/cartbox.js', 'cart-box-js');
        $this->addJs('$/igniter/cart/assets/js/cartitem.js', 'cart-item-js');
        $this->addJs('$/igniter/cart/assets/js/cartbox.modal.js', 'cart-box-modal-js');

        $this->prepareVars();
    }

    
    public function onLoadItemPopup()
    {
        $menuItem = $this->cartManager->findMenuItem(post('menuId'));

        $cartItem = null;
        if (strlen($rowId = post('rowId'))) {
            $cartItem = $this->cartManager->getCartItem($rowId);
            $menuItem = $cartItem->model;
        }

        $this->cartManager->validateMenuItem($menuItem);

        $this->cartManager->validateMenuItemStockQty($menuItem, $cartItem ? $cartItem->qty : 0);

        $this->controller->pageCycle();

        return $this->renderPartial('cartBoxByWeight::item_modal', [
            'formHandler' => $this->getEventHandler('onUpdateCart'),
            'cartItem' => $cartItem,
            'menuItem' => $menuItem,
        ]);
    }


    public function fetchPartials()
    {
        $this->prepareVars();

        return [
            '#cart-items' => $this->renderPartial('cartBoxByWeight::items'),
            '#cart-coupon' => $this->renderPartial('cartBoxAlias::coupon_form'),
            '#cart-tip' => $this->renderPartial('cartBoxAlias::tip_form'),
            '#cart-totals' => $this->renderPartial('cartBoxByWeight::totals'),
            '#cart-buttons' => $this->renderPartial('cartBoxByWeight::buttons'),
            '[data-cart-total]' => currency_format(Cart::total()),
            '#notification' => $this->renderPartial('flash'),
        ];
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

    public function onProceedToCheckout()
    {
        Event::fire('cupnoodles.cartBoxByWeight.onProceedToCheckout', [$this->cartManager->getCart()]);
        return parent::onProceedToCheckout();
        
    }

}