<?php

namespace App\Http\Requests\Api\V1\Integration;

use App\Http\Requests\Api\ApiRequest;

class LmsShipmentSyncRequest extends ApiRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'shipment_id' => $this->input('shipmentId', $this->input('shipment_id')),
            'payload' => $this->input('payload', []),
        ]);
    }

    public function rules(): array
    {
        return [
            'shipment_id' => ['nullable', 'integer', 'exists:shipments,id'],
            'payload' => ['required', 'array'],
        ];
    }
}

