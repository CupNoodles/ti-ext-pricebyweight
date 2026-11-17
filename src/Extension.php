<?php

declare(strict_types=1);

namespace CupNoodles\PriceByWeight;

use CupNoodles\PriceByWeight\Classes\WeighedOrderRules;
use CupNoodles\PriceByWeight\Classes\WeighedOrders;
use CupNoodles\PriceByWeight\Classes\Weight;
use CupNoodles\PriceByWeight\Classes\WeightCart;
use CupNoodles\PriceByWeight\Classes\WeightCartManager;
use CupNoodles\PriceByWeight\Http\Requests\LocationSettingsRequest;
use CupNoodles\PriceByWeight\Livewire\WeightItemModal;
use CupNoodles\PriceByWeight\Models\Unit;
use Igniter\Admin\Widgets\Form;
use Igniter\Cart\Classes\CartManager;
use Igniter\Cart\Http\Controllers\Menus;
use Igniter\Cart\Http\Requests\MenuRequest;
use Igniter\Cart\Models\Menu;
use Igniter\Local\Models\Location;
use Igniter\System\Classes\BaseExtension;
use Igniter\System\Classes\FormRequest;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Override;

class Extension extends BaseExtension
{
    protected array $morphMap = [
        'units_of_measure' => Unit::class,
    ];

    #[Override]
    public function register(): void
    {
        parent::register();

        // Decimal quantities for items sold by weight (see the classes for what they change)
        $this->app->extend('cart', fn($cart, $app): WeightCart => new WeightCart($app['session'], $app['events']));
        $this->app->extend(CartManager::class, fn(): WeightCartManager => new WeightCartManager);
    }

    #[Override]
    public function boot(): void
    {
        $this->extendMenuModel();
        $this->extendMenuForm();
        WeighedOrders::boot();

        // Orange's menu item and cart item views, with weighed items opening the weight popup.
        // A theme's own copies of these views still take precedence.
        View::prependNamespace('igniter-orange', __DIR__.'/../resources/views/orange');

        Location::extend(function(Location $model): void {
            $model->bindEvent('model.getAttribute', function($key, $value) {
                return $key === 'grouped_settings' && is_array($value) ? WeighedOrderRules::apply($value) : null;
            });
        });
    }

    #[Override]
    public function registerComponents(): array
    {
        return [
            WeightItemModal::class => [
                'code' => WeightItemModal::CODE,
                'name' => 'lang:cupnoodles.pricebyweight::default.component_weight_item_modal',
                'description' => 'lang:cupnoodles.pricebyweight::default.component_weight_item_modal_desc',
            ],
        ];
    }

    public function registerLocationSettings(): array
    {
        return [
            'pricebyweight' => [
                'label' => 'lang:cupnoodles.pricebyweight::default.text_location_settings',
                'description' => 'lang:cupnoodles.pricebyweight::default.text_location_settings_desc',
                'icon' => 'fa fa-balance-scale',
                'priority' => 50,
                'form' => 'cupnoodles.pricebyweight::/models/locationsettings',
                'request' => LocationSettingsRequest::class,
            ],
        ];
    }

    #[Override]
    public function registerPermissions(): array
    {
        return [
            'CupNoodles.PriceByWeight.ManageUnits' => [
                'description' => 'lang:cupnoodles.pricebyweight::default.permission_manage_units',
                'group' => 'igniter::admin.permissions.name',
            ],
        ];
    }

    #[Override]
    public function registerNavigation(): array
    {
        return [
            'tools' => [
                'child' => [
                    'units' => [
                        'priority' => 20,
                        'class' => 'units',
                        'href' => admin_url('cupnoodles/pricebyweight/units'),
                        'title' => lang('cupnoodles.pricebyweight::default.side_menu'),
                        'permission' => 'CupNoodles.PriceByWeight.ManageUnits',
                    ],
                ],
            ],
        ];
    }

    protected function extendMenuModel(): void
    {
        Menu::extend(function(Menu $model): void {
            $model->relation['belongsTo']['weight_unit'] = [Unit::class, 'foreignKey' => 'uom_id'];
            $model->addCasts([
                'price_by_weight' => 'boolean',
                'weight_minimum' => 'float',
                'weight_step' => 'float',
            ]);

            // For themes: $menu->isSoldByWeight(), $menu->formatWeight(1.5) -> "1.5 lb"
            $model->addDynamicMethod('isSoldByWeight', fn(): bool => Weight::isWeighed($model));
            $model->addDynamicMethod('formatWeight', fn($weight): string => Weight::format($model, $weight));
        });
    }

    protected function extendMenuForm(): void
    {
        Menus::extendFormFields(function(Form $form, $model): void {
            if (!$model instanceof Menu) {
                return;
            }

            $tab = 'lang:cupnoodles.pricebyweight::default.text_tab_weight';
            $shownWhenByWeight = ['action' => 'show', 'field' => 'price_by_weight', 'condition' => 'checked'];

            $form->addTabFields([
                'price_by_weight' => [
                    'tab' => $tab,
                    'label' => 'lang:cupnoodles.pricebyweight::default.label_price_by_weight',
                    'type' => 'switch',
                    'comment' => 'lang:cupnoodles.pricebyweight::default.help_price_by_weight',
                ],
                'uom_id' => [
                    'tab' => $tab,
                    'label' => 'lang:cupnoodles.pricebyweight::default.label_unit',
                    // Button group rather than a select: there are only a few units, and a select's
                    // open list here is drawn underneath the fields below it
                    'type' => 'radiotoggle',
                    'options' => [Unit::class, 'getDropdownOptions'],
                    'trigger' => $shownWhenByWeight,
                ],
                'weight_minimum' => [
                    'tab' => $tab,
                    'label' => 'lang:cupnoodles.pricebyweight::default.label_weight_minimum',
                    // Not 'number': core's form widget casts number fields to integers
                    'type' => 'text',
                    'span' => 'left',
                    'attributes' => ['inputmode' => 'decimal'],
                    'comment' => 'lang:cupnoodles.pricebyweight::default.help_weight_minimum',
                    'trigger' => $shownWhenByWeight,
                ],
                'weight_step' => [
                    'tab' => $tab,
                    'label' => 'lang:cupnoodles.pricebyweight::default.label_weight_step',
                    'type' => 'text',
                    'span' => 'right',
                    'attributes' => ['inputmode' => 'decimal'],
                    'comment' => 'lang:cupnoodles.pricebyweight::default.help_weight_step',
                    'trigger' => $shownWhenByWeight,
                ],
            ]);
        });

        // The menu form only saves fields its request validates
        Event::listen('system.formRequest.extendValidator', function(FormRequest $request, object $dataHolder): void {
            if (!$request instanceof MenuRequest) {
                return;
            }

            $dataHolder->rules['price_by_weight'] = ['nullable', 'boolean'];
            $dataHolder->rules['uom_id'] = ['nullable', 'required_if:price_by_weight,1', 'integer', 'exists:units_of_measure,uom_id'];
            $dataHolder->rules['weight_minimum'] = ['nullable', 'numeric', 'min:0'];
            $dataHolder->rules['weight_step'] = ['nullable', 'numeric', 'gt:0'];

            foreach (['price_by_weight', 'weight_minimum', 'weight_step'] as $field) {
                $dataHolder->attributes[$field] = lang('cupnoodles.pricebyweight::default.label_'.$field);
            }
            $dataHolder->attributes['uom_id'] = lang('cupnoodles.pricebyweight::default.label_unit');
        });
    }
}
