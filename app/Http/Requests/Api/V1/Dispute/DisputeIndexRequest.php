<?php

namespace App\Http\Requests\Api\V1\Dispute;

use App\Http\Requests\Api\ApiRequest;

class DisputeIndexRequest extends ApiRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'status' => strtolower((string) $this->input('status')),
            'order_id' => $this->input('orderId', $this->input('order_id')),
            'seller_id' => $this->input('sellerId', $this->input('seller_id')),
            'per_page' => $this->input('perPage', $this->input('per_page')),
        ]);
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', 'string', 'max:50'],
            'order_id' => ['nullable', 'integer', 'exists:orders,id'],
            'seller_id' => ['nullable', 'integer', 'exists:users,id'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}

