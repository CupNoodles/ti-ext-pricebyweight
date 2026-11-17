<?php

declare(strict_types=1);

namespace CupNoodles\PriceByWeight\Http\Controllers;

use CupNoodles\PriceByWeight\Http\Requests\UnitRequest;
use CupNoodles\PriceByWeight\Models\Unit;
use Igniter\Admin\Classes\AdminController;
use Igniter\Admin\Facades\AdminMenu;
use Igniter\Admin\Http\Actions\FormController;
use Igniter\Admin\Http\Actions\ListController;

class Units extends AdminController
{
    public array $implement = [
        ListController::class,
        FormController::class,
    ];

    public array $listConfig = [
        'list' => [
            'model' => Unit::class,
            'title' => 'lang:cupnoodles.pricebyweight::default.text_title',
            'emptyMessage' => 'lang:cupnoodles.pricebyweight::default.text_empty',
            'defaultSort' => ['name', 'ASC'],
            'configFile' => 'unit',
        ],
    ];

    public array $formConfig = [
        'name' => 'lang:cupnoodles.pricebyweight::default.text_form_name',
        'model' => Unit::class,
        'request' => UnitRequest::class,
        'create' => [
            'title' => 'lang:igniter::admin.form.create_title',
            'redirect' => 'cupnoodles/pricebyweight/units/edit/{uom_id}',
            'redirectClose' => 'cupnoodles/pricebyweight/units',
            'redirectNew' => 'cupnoodles/pricebyweight/units/create',
        ],
        'edit' => [
            'title' => 'lang:igniter::admin.form.edit_title',
            'redirect' => 'cupnoodles/pricebyweight/units/edit/{uom_id}',
            'redirectClose' => 'cupnoodles/pricebyweight/units',
            'redirectNew' => 'cupnoodles/pricebyweight/units/create',
        ],
        'preview' => [
            'title' => 'lang:igniter::admin.form.preview_title',
            'back' => 'cupnoodles/pricebyweight/units',
        ],
        'delete' => [
            'redirect' => 'cupnoodles/pricebyweight/units',
        ],
        'configFile' => 'unit',
    ];

    protected null|string|array $requiredPermissions = 'CupNoodles.PriceByWeight.ManageUnits';

    public function __construct()
    {
        parent::__construct();

        AdminMenu::setContext('units', 'tools');
    }
}
