<?php

namespace App\Http\Requests\Api\V1\Shipping;

use App\Http\Requests\Api\ApiRequest;

class ShipmentStoreRequest extends ApiRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'order_id' => $this->input('orderId', $this->input('order_id')),
            'service_code' => $this->input('serviceCode', $this->input('service_code')),
            'service_name' => $this->input('serviceName', $this->input('service_name')),
            'tracking_number' => $this->input('trackingNumber', $this->input('tracking_number')),
            'cost' => $this->input('cost'),
            'currency_sign' => $this->input('currencySign', $this->input('currency_sign')),
            'metadata' => $this->input('metadata', []),
            'expected_delivery_at' => $this->input('expectedDeliveryAt', $this->input('expected_delivery_at')),
        ]);
    }

    public function rules(): array
    {
        return [
            'order_id' => ['nullable', 'integer', 'exists:orders,id'],
            'service_code' => ['nullable', 'string', 'max:191'],
            'service_name' => ['nullable', 'string', 'max:191'],
            'tracking_number' => ['required', 'string', 'max:191', 'unique:shipments,tracking_number'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'currency_sign' => ['nullable', 'string', 'max:10'],
            'metadata' => ['nullable', 'array'],
            'expected_delivery_at' => ['nullable', 'date'],
        ];
    }
}

