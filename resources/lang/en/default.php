<?php

return [
    'side_menu' => 'Units of Measure',
    'permission_manage_units' => 'Create, modify and delete units of measure',

    'text_title' => 'Units of Measure',
    'text_form_name' => 'Unit of Measure',
    'text_empty' => 'No units of measure yet.',

    'label_name' => 'Name',
    'label_abbreviation' => 'Abbreviation',
    'label_decimal_places' => 'Decimal places',
    'help_name' => 'For staff, eg. "Pounds".',
    'help_abbreviation' => 'Shown to customers after weights and prices, eg. "lb".',
    'help_decimal_places' => 'How precisely customers can order. 2 allows 0.33 lb, 0 allows whole grams only.',

    // Menu item form
    'text_tab_weight' => 'Price by weight',
    'label_price_by_weight' => 'Sold by weight',
    'help_price_by_weight' => 'Customers order an amount (eg. 0.75 lb) instead of a number of items. The menu price is the price per unit.',
    'label_unit' => 'Unit',
    'label_weight_minimum' => 'Minimum order',
    'help_weight_minimum' => 'Smallest amount a customer can order, in the unit above.',
    'label_weight_step' => 'Step',
    'help_weight_step' => 'How much the + and - buttons add or remove. Customers can still type any amount.',

    // Storefront and location settings
    'component_weight_item_modal' => 'Weight popup',
    'component_weight_item_modal_desc' => 'Add-to-cart popup for items sold by weight.',
    'label_weight' => 'Weight',
    'text_location_settings' => 'Price by weight',
    'text_location_settings_desc' => 'Delivery and payment rules for orders with items sold by weight.',
    'label_disable_delivery' => 'No delivery for orders with items sold by weight',
    'help_disable_delivery' => 'When the cart has an item sold by weight, only pickup is offered.',
    'label_payments' => 'Payment methods for orders with items sold by weight',
    'help_payments' => 'When the cart has an item sold by weight, only these payment methods are offered (eg. pay at pickup, since the final price is set after weighing). Leave all unticked to allow every method.',
];
