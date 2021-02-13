<?php

namespace CupNoodles\PriceByWeight\Controllers;

use AdminMenu;

class Units extends \Admin\Classes\AdminController
{
    public $implement = [
        'Admin\Actions\ListController',
        'Admin\Actions\FormController'
    ];

    public $listConfig = [
        'list' => [
            'model' => 'CupNoodles\PriceByWeight\Models\Units',
            'title' => 'cupnoodles.pricebyweight::default.text_title',
            'emptyMessage' => 'cupnoodles.pricebyweight::default.text_empty',
            'defaultSort' => ['uom_id', 'DESC'],
            'configFile' => 'uom_config',
        ],
    ];

    public $formConfig = [
        'name' => 'cupnoodles.pricebyweight::default.text_form_name',
        'model' => 'CupNoodles\PriceByWeight\Models\Units',
        'request' => 'CupNoodles\PriceByWeight\Requests\UnitsOfMeasure',
        'create' => [
            'title' => 'lang:admin::lang.form.create_title',
            'redirect' => 'cupnoodles/pricebyweight/units/edit/{uom_id}',
            'redirectClose' => 'cupnoodles/pricebyweight/units',
        ],
        'edit' => [
            'title' => 'lang:admin::lang.form.edit_title',
            'redirect' => 'cupnoodles/pricebyweight/units/edit/{uom_id}',
            'redirectClose' => 'cupnoodles/pricebyweight/units',
        ],
        'preview' => [
            'title' => 'lang:admin::lang.form.preview_title',
            'redirect' => 'cupnoodles/pricebyweight/units',
        ],
        'delete' => [
            'redirect' => 'cupnoodles/pricebyweight/units',
        ],
        'configFile' => 'uom_config',
    ];

    //protected $requiredPermissions = 'Admin.UnitsOfMeasure';

    public function __construct()
    {
        parent::__construct();

        AdminMenu::setContext('units', 'localisation');
    }



}
