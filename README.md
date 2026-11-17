# Price By Weight for TastyIgniter

Sell menu items by weight (lb, oz, kg, g, or your own units) for butcher shops, delis and produce
stands. Customers order an amount, eg. 0.33 lb of ribeye, and pay the price per unit for it.

Works with the stock Orange theme: no theme changes needed.

> Using TastyIgniter v3? Use the [`v3` branch](https://github.com/CupNoodles/ti-ext-pricebyweight/tree/v3).

## Requirements

- TastyIgniter v4.5 or later with the Cart, Local and PayRegister extensions (installed by default)

## Installation

### From GitHub (git clone)

From your TastyIgniter root folder, clone the extension into `extensions/cupnoodles/pricebyweight`.
The folder name matters: TastyIgniter finds extensions at `extensions/<vendor>/<name>`.

```bash
git clone https://github.com/CupNoodles/ti-ext-pricebyweight.git extensions/cupnoodles/pricebyweight
php artisan igniter:up
```

To update later:

```bash
cd extensions/cupnoodles/pricebyweight
git pull
cd ../../..
php artisan igniter:up
```

### With Composer, from GitHub

```bash
composer config repositories.cupnoodles-pricebyweight vcs https://github.com/CupNoodles/ti-ext-pricebyweight
composer require cupnoodles/ti-ext-pricebyweight:dev-master
php artisan igniter:up
```

Once a version is tagged, you can require `^4.0` instead of `dev-master`.

## Setting up

### Units of measure

**Tools > Units of Measure** lists the units items can be sold by. Pounds, ounces, grams and
kilograms are set up for you. Each unit has:

- **Name**, for staff, eg. "Pounds"
- **Abbreviation**, shown to customers, eg. "lb"
- **Decimal places**, how precisely customers can order: 2 allows 0.33 lb, 3 allows 0.125 kg,
  0 allows whole grams only

### Items sold by weight

On a menu item's edit page, open the **Price by weight** tab:

- **Sold by weight**: switch on.
- **Unit**: eg. Pounds.
- **Minimum order**: the smallest amount a customer can order, eg. 0.25 (lb).
- **Step**: how much the + and - buttons add or remove, eg. 0.25. Customers can still type any
  amount at the unit's precision, eg. 0.33.

The item's normal **price** is the price per unit (eg. $12.99 per lb).

### Delivery and payment for weighed orders

If the final price is set after the item is cut and weighed, you may not want weighed orders
delivered or paid for online up front. Under **Locations > (your location) > Settings > Price by
weight**:

- **No delivery for orders with items sold by weight**: when the cart contains a weighed item,
  only pickup is offered.
- **Payment methods for orders with items sold by weight**: when the cart contains a weighed item,
  only the ticked methods are offered (eg. Cash/pay at pickup). Leave all unticked to allow every
  method.

Carts without weighed items aren't affected.

## What customers see

On the menu, weighed items show their price per unit, eg. "$12.99 / lb". Clicking one opens a
popup where the customer types or steps the weight, picks any menu options and sees the total.
In the cart the item shows its weight, eg. "0.33 lb", and the cart's + and - buttons change the
weight by the item's step.

Any wording about estimated prices or final weighing is up to your theme and shop.

## Orders

Orders store the real weight as the order line's quantity at the price per unit, so reports,
emails, invoices, the API and payment gateways see "0.33" of an item at $12.99, and order totals
are exact. Each weighed order line also records its unit (`uom_tag`, eg. "lb", and
`uom_decimals`) for integrations such as POS systems.

TastyIgniter's admin order view, invoice and order emails show the weight as the quantity without
the unit, eg. "0.5 x Ribeye".

Stock: if you switch on stock tracking for an item sold by weight, its stock is counted in the
unit's smallest step (eg. hundredths of a lb: 0.33 lb sold subtracts 33).

## For theme developers

The extension replaces two Orange views so weighed items work out of the box:
`includes/menu/item` and `includes/cartbox/items`. Its copies mark every change with
`pricebyweight`. If your theme has its own copies of these views, yours take precedence, so add the
weighed-item handling to them. Helpers on menu items:

```blade
@if ($menuItem->isSoldByWeight())
    {{ $menuItem->formatWeight(0.5) }}  {{-- "0.5 lb" --}}
@endif
```

The weight popup is the Livewire component `cupnoodles-pricebyweight::weight-item-modal`, opened
with Orange's modal: `data-toggle="orange-modal"`,
`data-component="cupnoodles-pricebyweight::weight-item-modal"` and
`data-arguments='{"menuId": 12}'` (add `"rowId"` to edit a cart line).

### How it fits into TastyIgniter

TastyIgniter's cart assumes whole-number quantities in a few places. The extension adjusts only
those, through Laravel's service container and TastyIgniter's events:

- the cart (`cart` binding) accepts decimal quantities and counts a weighed line as one item
- the cart manager uses the item's own minimum and step for weighed items
- order lines with a fractional quantity read back as decimals; others keep core's whole numbers
- core's stock subtraction is given whole units for weighed lines

Everything else is core TastyIgniter.

## Upgrading from v3

Run `php artisan igniter:up`. Your units and weighed items are converted: v3's per-unit step size
becomes each item's step, the item's minimum quantity becomes its minimum order, and existing
orders keep their weights. The `cartBoxByWeight`, `checkoutByWeight` and `menuByWeight` components
are gone: switch your theme back to the standard components (or use stock Orange).

## License

MIT. See [LICENSE](LICENSE).
