## Price By Weight 

Price By Weight extends components from igniter.cart and igniter.local in order to allow for fractional stock qty orders.

- Migrates (int) columns to mysql decimal columns where needed, including menus.stock_qty and order_menus.quantity
- 3 components extended to handle fractional stock qtys.
- Admin control page for new Localization option 'Units of Measure'
- Admin Order Menus view and Invoice view overridden to include units of measure. 
- igniter.cart mail templates updated to include units of measure where applicable. 

### Admin Panel
Manage item unit of measure labels and step size. Step size determines the increment/decrement steps when +/- is clicked in the front-facing components

### Components

| Name     | Page variable                  | Description                                      | Extends |
| -------- | ------------------------------ | ------------------------------------------------ | ------- |
| CartBoxByWeight  | `@component('cartBoxByWeight')`  | Cartbox that shows unit of measure if set, and increment/decrements by step size. | [igniter.cart cartBox](git@github.com:CupNoodles/ti-ext-pricebyweight.git) |
| CheckoutByWeight | `@component('checkoutByWeight')` | Checkout form that shows unit of measure if set.. | [igniter.cart checkout](git@github.com:CupNoodles/ti-ext-pricebyweight.git)
| MenuByWeight | `@component('menuByWeight')` | Menu List that shows unit of measure if set, and increment/decrements by step size. | [igniter.local menu](git@github.com:CupNoodles/ti-ext-pricebyweight.git) |


### Usage within a Theme

In your tempate layout's front-matter sections, replace any invocations to `cartBox`, `checkout`, `localMenu` with `cartBoxByWeight`, `checkoutByWeight`, `localMenuByWeight` respectively. 

For instance, in tastyigniter-orange, `_layouts/local.blade` could have

```
'[cartBox]':
    checkStockCheckout: 1
    showCartItemThumb: 1
    pageIsCheckout: 0
    pageIsCart: 0
    checkoutPage: checkout/checkout
```

replaced with 

```
'[cartBoxByWeight]':
    checkStockCheckout: 1
    showCartItemThumb: 1
    pageIsCheckout: 0
    pageIsCart: 0
    checkoutPage: checkout/checkout
```


From there, you'll need to replace any invocations to those specific templates. For the example above, replace `@partial('cartBox::container')` with `@partial('cartBoxByWeight::container')`.