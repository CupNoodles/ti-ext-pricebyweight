<?php


$config['list']['toolbar'] = [
    'buttons' => [
        'create' => [
            'label' => 'lang:admin::lang.button_new',
            'class' => 'btn btn-primary',
            'href' => admin_url('cupnoodles/pricebyweight/units/create'),
        ],
        'delete' => [
            'label' => 'lang:admin::lang.button_delete',
            'class' => 'btn btn-danger',
            'data-attach-loading' => '',
            'data-request' => 'onDelete',
            'data-request-form' => '#list-form',
            'data-request-data' => "_method:'DELETE'",
            'data-request-confirm' => 'lang:admin::lang.alert_warning_confirm',
        ],
    ],
];

$config['list']['columns'] = [
    'edit' => [
        'type' => 'button',
        'iconCssClass' => 'fa fa-pencil',
        'attributes' => [
            'class' => 'btn btn-edit',
            'href' => admin_url('cupnoodles/pricebyweight/units/edit/{uom_id}'),
        ],
    ],
    'backend_name' => [
        'label' => 'lang:cupnoodles.pricebyweight::default.backend_name',
        'type' => 'text'
    ],
    'long_name' => [
        'label' => 'lang:cupnoodles.pricebyweight::default.long_name',
        'type' => 'text'
    ],
    'short_name' => [
        'label' => 'lang:cupnoodles.pricebyweight::default.short_name',
        'type' => 'text'
    ],
    'decimal_places' => [
        'label' => 'lang:cupnoodles.pricebyweight::default.decimal_places',
        'type' => 'text'
    ],
    'step_size' => [
        'label' => 'lang:cupnoodles.pricebyweight::default.step_size',
        'type' => 'text'
    ],
    'uom_id' => [
        'label' => 'lang:admin::lang.column_id',
        'invisible' => TRUE,
    ],

];

$config['form']['toolbar'] = [
    'buttons' => [
        'save' => [
            'label' => 'lang:admin::lang.button_save',
            'class' => 'btn btn-primary',
            'data-request' => 'onSave',
            'data-progress-indicator' => 'admin::lang.text_saving',
        ],
        'saveClose' => [
            'label' => 'lang:admin::lang.button_save_close',
            'class' => 'btn btn-default',
            'data-request' => 'onSave',
            'data-request-data' => 'close:1',
            'data-progress-indicator' => 'admin::lang.text_saving',
        ],
        'delete' => [
            'label' => 'lang:admin::lang.button_icon_delete',
            'class' => 'btn btn-danger',
            'data-request' => 'onDelete',
            'data-request-data' => "_method:'DELETE'",
            'data-request-confirm' => 'lang:admin::lang.alert_warning_confirm',
            'data-progress-indicator' => 'admin::lang.text_deleting',
            'context' => ['edit'],
        ],
    ],
];

$config['form']['tabs'] = [
    'defaultTab' => 'lang:cupnoodles.pricebyweight::default.text_tab_general',
    'fields' => [
        'backend_name' => [
            'label' => 'lang:cupnoodles.pricebyweight::default.backend_name',
            'type' => 'text',
            'span' => 'left',
        ],
        'long_name' => [
            'label' => 'lang:cupnoodles.pricebyweight::default.long_name',
            'type' => 'text',
            'span' => 'right',
        ],
        'short_name' => [
            'label' => 'lang:cupnoodles.pricebyweight::default.short_name',
            'type' => 'text',
            'span' => 'left',
        ],
        'decimal_places' => [
            'label' => 'lang:cupnoodles.pricebyweight::default.decimal_places',
            'type' => 'number',
            'span' => 'right',
        ],
        'step_size' => [
            'label' => 'lang:cupnoodles.pricebyweight::default.step_size',
            'type' => 'number',
            'span' => 'left',
        ]
    ],
];

return $config;
