<?php 

namespace CupNoodles\PriceByWeight;


use System\Classes\BaseExtension;


// Admin-UI
use Event;
use Admin\Models\Menus_model;

use Admin\Widgets\Form;
use Admin\Classes\AdminController;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\DB;

/**
 * Butcher Extension Information File
 */
class Extension extends BaseExtension
{
    /**
     * Returns information about this extension.
     *
     * @return array
     */
    public function extensionMeta()
    {
        return [
            'name'        => 'PriceByWeight',
            'author'      => 'CupNoodles',
            'description' => 'Price selected menu items by weight, for use in butcher shops and produce stands.',
            'icon'        => 'fa-balance-scale',
            'version'     => '1.0.0'
        ];
    }

    /**
     * Register method, called when the extension is first registered.
     *
     * @return void
     */
    public function register()
    {

    }

    /**
     * Boot method, called right before the request route.
     *
     * @return void
     */
    public function boot()
    {

        Menus_Model::extend(function ($model) {
            $model->relation['belongsTo']['uom'] = ['CupNoodles\PriceByWeight\Models\Units', 'foreignKey' => 'uom_id'];
            
            // Model::add_class() uses array_merge, and can therefore be used to set the protected $casts parameter
            $model->addCasts(['stock_qty' => 'float']);
            $model->addCasts(['minimum_qty' => 'float']);
        });

        Event::listen('system.formRequest.extendValidator', function ($dataholder, $request){

            if(get_class($dataholder) == 'Admin\Requests\Menu'){
                
                if(isset($request->rules['stock_qty'])){
                    $request->rules['stock_qty'] = ['nullable', 'numeric']; 
                }
                if(isset($request->rules['minimum_qty'])){
                    $request->rules['minimum_qty'] = ['nullable', 'numeric']; 
                }
            }

        });

        Event::listen('admin.form.extendFieldsBefore', function (Form $form) {

            if($form->model instanceof Menus_model){
                

                $pricebyweight = ['price_by_weight' => [
                    'label' => 'lang:cupnoodles.pricebyweight::default.label_price_by_weight',
                    'type' => 'switch',
                    'span' => 'left',

                ]];
                $priceper = ['uom' => [
                    'label' => 'lang:cupnoodles.pricebyweight::default.label_unit_of_measure',
                    'type' => 'relation',
                    'span' => 'right',
                    'relationFrom' => 'uom',
                    'nameFrom' => 'backend_name',
                    'valueFrom' => 'uom_id',
                    'trigger' => [
                        'action' => 'show',
                        'field' => 'price_by_weight',
                        'condition' => 'checked',
                    ]

                ]];
                $form->tabs['fields'] = $this->array_insert_after($form->tabs['fields'], 'menu_priority', $priceper);
                $form->tabs['fields'] = $this->array_insert_after($form->tabs['fields'], 'menu_priority', $pricebyweight);

            }

        });

        // immediately after an order is created, fill the orders_menu table with each item's UOM information, if applicable
        Event::listen('igniter.checkout.afterSaveOrder', function($order){
            $menus = DB::table('order_menus as om')
                ->leftJoin('menus as m', 'om.menu_id', '=', 'm.menu_id')
                ->leftJoin('units_of_measure as uom', 'm.uom_id', '=', 'uom.uom_id')
                ->where('order_id', $order->order_id)
                ->where('price_by_weight','1')
                //->update(['om.uom_tag' => 'uom.short_name', 'om.uom_decimals' => 'uom.decimal_places' ]);
                ->update(['om.uom_tag' => DB::raw("`".DB::getTablePrefix()."uom`.`short_name`"), 'om.uom_decimals' => DB::raw("`".DB::getTablePrefix()."uom`.`decimal_places`")]);                
        });
        

        Relation::morphMap([
            'units_of_measure' => 'CupNoodles\PriceByWeight\Models\Units_model',
        ]);



        // Inject our own template for Order Menu.
        // Overide the template for invoices
        // Note these will be overridden again if you have the OrderMenuEdit extension installed
        AdminController::extend(function ($controller) {
            if( in_array('~/app/admin/views/orders', $controller->partialPath)){
                array_unshift($controller->partialPath, '~/extensions/cupnoodles/pricebyweight/views');
                array_unshift($controller->viewPath, '~/extensions/cupnoodles/pricebyweight/views/orders');
            }
        });
    
    }

    function array_insert_after( array $array, $key, array $new ) {
        $keys = array_keys( $array );
        $index = array_search( $key, $keys );
        $pos = false === $index ? count( $array ) : $index + 1;
    
        return array_merge( array_slice( $array, 0, $pos ), $new, array_slice( $array, $pos ) );
    }

    public function registerFormWidgets()
    {

    }

    /**
     * Registers any front-end components implemented in this extension.
     *
     * @return array
     */
    public function registerComponents()
    {


        return [
            'CupNoodles\PriceByWeight\Components\MenuByWeight' => [
                'code' => 'localMenuByWeight',
                'name' => 'lang:igniter.local::default.menu.component_title',
                'description' => 'lang:igniter.local::default.menu.component_desc',
            ],
            'CupNoodles\PriceByWeight\Components\CartBoxByWeight' => [
                'code' => 'cartBoxByWeight',
                'name' => 'lang:igniter.cart::default.text_component_title',
                'description' => 'lang:igniter.cart::default.text_component_desc',
            ],
            'CupNoodles\PriceByWeight\Components\CheckoutByWeight' => [
                'code' => 'checkoutByWeight',
                'name' => 'lang:igniter.cart::default.text_checkout_component_title',
                'description' => 'lang:igniter.cart::default.text_checkout_component_desc',
            ],
        ];
    }

    /**
     * Registers any admin permissions used by this extension.
     *
     * @return array
     */
    public function registerPermissions()
    {
        return [
            'Admin.UnitsOfMeasure' => [
                'label' => 'cupnoodles.priceByWeight::default.permissions',
                'group' => 'admin::lang.permissions.name',
            ],
        ];
    }

    public function registerNavigation()
    {
        return [
            'localisation' => [
                'child' => [
                    'units' => [
                        'priority' => 20,
                        'class' => 'Units',
                        'href' => admin_url('cupnoodles/pricebyweight/units'),
                        'title' => lang('cupnoodles.pricebyweight::default.side_menu'),
                        'permission' => 'Admin.UnitOfMeasure',
                    ],
                ],
            ],
        ];
    }
}
