<?php

namespace App\Http\Requests\Api\V1\Shipping;

use App\Http\Requests\Api\ApiRequest;

class ShippingQuoteRequest extends ApiRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'service_code' => $this->input('serviceCode', $this->input('service_code')),
            'items' => $this->input('items', []),
            'destination' => $this->input('destination', []),
        ]);
    }

    public function rules(): array
    {
        return [
            'service_code' => ['nullable', 'string', 'max:191'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.weight' => ['nullable', 'numeric', 'min:0'],
            'items.*.price' => ['nullable', 'numeric', 'min:0'],
            'items.*.quantity' => ['nullable', 'integer', 'min:1'],
            'destination' => ['nullable', 'array'],
            'destination.country' => ['nullable', 'string', 'max:191'],
            'destination.city' => ['nullable', 'string', 'max:191'],
            'destination.postal_code' => ['nullable', 'string', 'max:20'],
        ];
    }
}

