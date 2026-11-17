<?php

declare(strict_types=1);

namespace CupNoodles\PriceByWeight\Http\Requests;

use Igniter\System\Classes\FormRequest;

class LocationSettingsRequest extends FormRequest
{
    public function attributes(): array
    {
        return [
            'disable_delivery' => lang('cupnoodles.pricebyweight::default.label_disable_delivery'),
            'payments' => lang('cupnoodles.pricebyweight::default.label_payments'),
        ];
    }

    public function rules(): array
    {
        return [
            'disable_delivery' => ['nullable', 'boolean'],
            'payments' => ['nullable', 'array'],
            'payments.*' => ['string'],
        ];
    }
}
