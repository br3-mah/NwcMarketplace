<?php

namespace App\Http\Requests\Api\V1\Checkout;

use App\Http\Requests\Api\ApiRequest;

class CheckoutEstimateRequest extends ApiRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'items' => $this->input('items', []),
            'shipping_method' => $this->input('shippingMethod', $this->input('shipping_method')),
            'shipping_cost' => $this->input('shippingCost', $this->input('shipping_cost')),
            'currency' => $this->input('currency'),
            'notes' => $this->input('notes'),
        ]);
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.price' => ['nullable', 'numeric', 'min:0'],
            'items.*.options' => ['nullable', 'array'],
            'shipping_method' => ['nullable', 'string', 'max:191'],
            'shipping_cost' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:3'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}

