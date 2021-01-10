<?php

namespace CupNoodles\PriceByWeight\Requests;

use System\Classes\FormRequest;

class UnitsOfMeasure extends FormRequest
{
    public function rules()
    {
        return [
            ['backend_name', 'cupnoodles.priceByWeight::default.backend_name', 'required|between:2,128'],
            ['long_name', 'cupnoodles.priceByWeight::default.long_name', 'required|between:1,128'],
            ['short_name', 'cupnoodles.priceByWeight::default.short_name', 'required|between:1,128'],
            ['decimal_places', 'cupnoodles.priceByWeight::default.decimal_places', 'required|numeric'],
            ['step_size', 'cupnoodles.priceByWeight::default.step_size', 'required|numeric'],
        ];
    }

}
