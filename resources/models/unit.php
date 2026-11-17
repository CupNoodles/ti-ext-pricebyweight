<?php

$config['list']['toolbar'] = [
    'buttons' => [
        'create' => [
            'label' => 'lang:igniter::admin.button_new',
            'class' => 'btn btn-primary',
            'href' => 'cupnoodles/pricebyweight/units/create',
        ],
    ],
];

$config['list']['bulkActions'] = [
    'delete' => [
        'label' => 'lang:igniter::admin.button_delete',
        'class' => 'btn btn-light text-danger',
        'data-request-confirm' => 'lang:igniter::admin.alert_warning_confirm',
    ],
];

$config['list']['columns'] = [
    'edit' => [
        'type' => 'button',
        'iconCssClass' => 'fa fa-pencil',
        'attributes' => [
            'class' => 'btn btn-edit',
            'href' => 'cupnoodles/pricebyweight/units/edit/{uom_id}',
        ],
    ],
    'name' => [
        'label' => 'lang:cupnoodles.pricebyweight::default.label_name',
        'type' => 'text',
    ],
    'abbreviation' => [
        'label' => 'lang:cupnoodles.pricebyweight::default.label_abbreviation',
        'type' => 'text',
    ],
    'decimal_places' => [
        'label' => 'lang:cupnoodles.pricebyweight::default.label_decimal_places',
        'type' => 'number',
    ],
];

$config['form']['toolbar'] = [
    'buttons' => [
        'save' => [
            'label' => 'lang:igniter::admin.button_save',
            'context' => ['create', 'edit'],
            'partial' => 'form/toolbar_save_button',
            'class' => 'btn btn-primary',
            'data-request' => 'onSave',
            'data-progress-indicator' => 'igniter::admin.text_saving',
        ],
        'delete' => [
            'label' => 'lang:igniter::admin.button_icon_delete',
            'context' => ['edit'],
            'class' => 'btn btn-danger',
            'data-request' => 'onDelete',
            'data-request-submit' => 'true',
            'data-request-confirm' => 'lang:igniter::admin.alert_warning_confirm',
        ],
    ],
];

$config['form']['fields'] = [
    'name' => [
        'label' => 'lang:cupnoodles.pricebyweight::default.label_name',
        'type' => 'text',
        'span' => 'left',
        'comment' => 'lang:cupnoodles.pricebyweight::default.help_name',
    ],
    'abbreviation' => [
        'label' => 'lang:cupnoodles.pricebyweight::default.label_abbreviation',
        'type' => 'text',
        'span' => 'right',
        'comment' => 'lang:cupnoodles.pricebyweight::default.help_abbreviation',
    ],
    'decimal_places' => [
        'label' => 'lang:cupnoodles.pricebyweight::default.label_decimal_places',
        'type' => 'number',
        'span' => 'left',
        'default' => 2,
        'comment' => 'lang:cupnoodles.pricebyweight::default.help_decimal_places',
    ],
];

return $config;
