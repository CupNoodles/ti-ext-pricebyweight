<?php

namespace CupNoodles\PriceByWeight\Components;

use Igniter\Cart\Components\Checkout;
use CupNoodles\PriceByWeight\Classes\OrderManagerByWeight as OrderManager;
use CupNoodles\PriceByWeight\Classes\CartManagerByWeight as CartManager;

use Admin\Models\Payment_profiles_model;

use Redirect;
use Location;

use Illuminate\Support\Facades\App;

class CheckoutByWeight extends Checkout{

    protected $location;

    public function initialize()
    {
        $this->orderManager = OrderManager::instance();
        $this->cartManager = CartManager::instance();
        $this->location = App::make('location');

    }

    public function onRender()
    {
        foreach ($this->getPaymentGateways() as $paymentGateway) {
            $paymentGateway->beforeRenderPaymentForm($paymentGateway, $this->controller);
        }

        $this->addJs('$/igniter/cart/assets/js/checkout.js', 'checkout-js');
    }

    protected function prepareVars()
    {
        parent::prepareVars();

        $this->page['orderManager'] = $this->orderManager;
        $this->page['cartManager'] = $this->cartManager;

    }

    public function onChoosePayment()
    {
        $paymentCode = post('code');
        

        
        if (!$payment = $this->orderManager->getPayment($paymentCode))
            throw new ApplicationException(lang('igniter.cart::default.checkout.error_invalid_payment'));


        $this->orderManager->applyCurrentPaymentFee($payment->code);

        $this->controller->pageCycle();

        $result = $this->fetchPartials();

        if ($cartBox = $this->controller->findComponentByAlias($this->property('cartBoxByWeight'))) {
            $result = array_merge($result, $cartBox->fetchPartials());
        }

        return $result;
    }

    protected function createRules()
    {

        $namedRules = [
            ['first_name', 'lang:igniter.cart::default.checkout.label_first_name', 'required|between:1,48'],
            ['last_name', 'lang:igniter.cart::default.checkout.label_last_name', 'required|between:1,48'],
            ['email', 'lang:igniter.cart::default.checkout.label_email', 'sometimes|required|email:filter|max:96|unique:customers'],
            ['telephone', 'lang:igniter.cart::default.checkout.label_telephone', 'required|between:10,20'],
            ['comment', 'lang:igniter.cart::default.checkout.label_comment', 'max:500'],
            ['payment', 'lang:igniter.cart::default.checkout.label_payment_method', 'sometimes|required|alpha_dash'],
            ['terms_condition', 'lang:button_agree_terms', 'sometimes|integer'],
        ];

        if (Location::orderTypeIsDelivery()) {
            $namedRules[] = ['address.address_1', 'lang:igniter.cart::default.checkout.label_address_1', 'required|min:3|max:128'];
            $namedRules[] = ['address.address_2', 'lang:igniter.cart::default.checkout.label_address_2', 'sometimes|min:1|max:128'];
            $namedRules[] = ['address.city', 'lang:igniter.cart::default.checkout.label_city', 'sometimes|min:2|max:128'];
            $namedRules[] = ['address.state', 'lang:igniter.cart::default.checkout.label_state', 'sometimes|max:128'];
            $namedRules[] = ['address.postcode', 'lang:igniter.cart::default.checkout.label_postcode', 'string'];
            $namedRules[] = ['address.country_id', 'lang:igniter.cart::default.checkout.label_country', 'sometimes|required|integer'];
        }

        return $namedRules;
    }




    public function onConfirm()
    {

        if ($redirect = $this->isOrderMarkedAsProcessed())
            return $redirect;

        $data = post();
        $data['cancelPage'] = $this->property('redirectPage');
        $data['successPage'] = $this->property('successPage');

        $data = $this->processDeliveryAddress($data);
        
        $this->validateCheckoutSecurity();

        try {
            $this->validate($data, $this->createRules(), [
                'email.unique' => lang('igniter.cart::default.checkout.error_email_exists'),
            ]);

            $order = $this->getOrder();
            
            if ($order->isDeliveryType()) {
                $this->orderManager->validateDeliveryAddress(array_get($data, 'address', []));
            }
            elseif($data['address']['address_id'] == 0){ // pickup orders
                $data['address'] = $this->location->current()->getAddress();
            }

            $this->orderManager->saveOrder($order, $data);
            
            if (($redirect = $this->orderManager->processPayment($order, $data)) === FALSE)
                return;
            
            if ($redirect instanceof RedirectResponse)
                return $redirect;

            if ($redirect = $this->isOrderMarkedAsProcessed())
                return $redirect;

            if ($redirect = $this->orderHasPaymentProfile()){
                return $redirect;
            }
        }
        catch (Exception $ex) {
            flash()->warning($ex->getMessage())->important();

            return Redirect::back()->withInput();
        }
    }

    protected function orderHasPaymentProfile()
    {
        $order = $this->getOrder();
        $profile = Payment_profiles_model::where('order_id', $order->order_id)->get();
        if(count($profile) >0){
            $redirectUrl = $order->getUrl($this->property('successPage'));
            return Redirect::to($redirectUrl);
        }
        return false;
    }

}