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
| -------- | ------------------------------ | ------------------------------------------------ | igniter.cart cartBox |
| CartBoxByWight  | `@component('cartBoxByWeight')`  | Cartbox that shows unit of measure if set, and increment/decrements by step size. | igniter.cart checkout |
| CheckoutByWeight | `@component('checkoutByWeight')` | Checkout form that shows unit of measure if set.. |
| MenuByWeight | `@component('menuByWeight')` | Menu List that shows unit of measure if set, and increment/decrements by step size. | igniter.local menu |

