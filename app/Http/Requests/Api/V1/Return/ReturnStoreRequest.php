<?php

namespace App\Http\Requests\Api\V1\Return;

use App\Http\Requests\Api\ApiRequest;

class ReturnStoreRequest extends ApiRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'order_id' => $this->input('orderId', $this->input('order_id')),
            'reason' => $this->input('reason'),
            'notes' => $this->input('notes'),
            'items' => $this->input('items', []),
            'attachments' => $this->input('attachments', []),
        ]);
    }

    public function rules(): array
    {
        return [
            'order_id' => ['required', 'integer', 'exists:orders,id'],
            'reason' => ['required', 'string', 'max:191'],
            'notes' => ['nullable', 'string'],
            'items' => ['nullable', 'array'],
            'attachments' => ['nullable', 'array'],
        ];
    }
}

