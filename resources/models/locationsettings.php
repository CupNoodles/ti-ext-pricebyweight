<?php

use Igniter\PayRegister\Models\Payment;

return [
    'form' => [
        'fields' => [
            'disable_delivery' => [
                'label' => 'lang:cupnoodles.pricebyweight::default.label_disable_delivery',
                'type' => 'switch',
                'default' => false,
                'comment' => 'lang:cupnoodles.pricebyweight::default.help_disable_delivery',
            ],
            'payments' => [
                'label' => 'lang:cupnoodles.pricebyweight::default.label_payments',
                'type' => 'checkboxlist',
                'options' => [Payment::class, 'listDropdownOptions'],
                'commentAbove' => 'lang:cupnoodles.pricebyweight::default.help_payments',
            ],
        ],
    ],
];
