<?php

declare(strict_types=1);

namespace CupNoodles\PriceByWeight\Http\Requests;

use Igniter\System\Classes\FormRequest;

class UnitRequest extends FormRequest
{
    public function attributes(): array
    {
        return [
            'name' => lang('cupnoodles.pricebyweight::default.label_name'),
            'abbreviation' => lang('cupnoodles.pricebyweight::default.label_abbreviation'),
            'decimal_places' => lang('cupnoodles.pricebyweight::default.label_decimal_places'),
        ];
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'between:1,128'],
            'abbreviation' => ['required', 'string', 'between:1,16'],
            'decimal_places' => ['required', 'integer', 'between:0,4'],
        ];
    }
}
