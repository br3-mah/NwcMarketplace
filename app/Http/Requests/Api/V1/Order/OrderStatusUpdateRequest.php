<?php

namespace App\Http\Requests\Api\V1\Order;

use App\Http\Requests\Api\ApiRequest;

class OrderStatusUpdateRequest extends ApiRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'status' => strtolower((string) $this->input('status')),
            'note' => $this->input('note', $this->input('notes')),
        ]);
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:packed,shipped,delivered,failed,processing,completed,pending,declined'],
            'note' => ['nullable', 'string', 'max:2000'],
        ];
    }
}

