<?php

namespace App\Http\Requests\Api\V1\Checkout;

use App\Http\Requests\Api\ApiRequest;

class CheckoutCreateOrderRequest extends ApiRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'items' => $this->input('items', []),
            'shipping_method' => $this->input('shippingMethod', $this->input('shipping_method')),
            'shipping_cost' => $this->input('shippingCost', $this->input('shipping_cost')),
            'payment_method' => $this->input('paymentMethod', $this->input('payment_method')),
            'currency' => $this->input('currency'),
            'customer' => $this->input('customer', []),
            'shipping' => $this->input('shipping', []),
            'billing' => $this->input('billing', []),
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
            'payment_method' => ['required', 'string', 'max:191'],
            'currency' => ['nullable', 'string', 'max:3'],
            'notes' => ['nullable', 'string', 'max:2000'],

            'customer' => ['required', 'array'],
            'customer.name' => ['required', 'string', 'max:191'],
            'customer.email' => ['required', 'string', 'email', 'max:191'],
            'customer.phone' => ['nullable', 'string', 'max:191'],
            'customer.address' => ['nullable', 'string', 'max:500'],
            'customer.city' => ['nullable', 'string', 'max:191'],
            'customer.state' => ['nullable', 'string', 'max:191'],
            'customer.zip' => ['nullable', 'string', 'max:50'],
            'customer.country' => ['nullable', 'string', 'max:191'],

            'shipping' => ['nullable', 'array'],
            'shipping.name' => ['nullable', 'string', 'max:191'],
            'shipping.email' => ['nullable', 'string', 'email', 'max:191'],
            'shipping.phone' => ['nullable', 'string', 'max:191'],
            'shipping.address' => ['nullable', 'string', 'max:500'],
            'shipping.city' => ['nullable', 'string', 'max:191'],
            'shipping.state' => ['nullable', 'string', 'max:191'],
            'shipping.zip' => ['nullable', 'string', 'max:50'],
            'shipping.country' => ['nullable', 'string', 'max:191'],

            'billing' => ['nullable', 'array'],
        ];
    }
}

