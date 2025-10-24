<?php

namespace App\Http\Requests\Api\V1\Dispute;

use App\Http\Requests\Api\ApiRequest;

class DisputeStoreRequest extends ApiRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'order_id' => $this->input('orderId', $this->input('order_id')),
            'seller_id' => $this->input('sellerId', $this->input('seller_id')),
            'subject' => $this->input('subject'),
            'reason' => $this->input('reason'),
            'description' => $this->input('description'),
            'attachments' => $this->input('attachments', []),
        ]);
    }

    public function rules(): array
    {
        return [
            'order_id' => ['required', 'integer', 'exists:orders,id'],
            'seller_id' => ['nullable', 'integer', 'exists:users,id'],
            'subject' => ['required', 'string', 'max:191'],
            'reason' => ['nullable', 'string', 'max:191'],
            'description' => ['nullable', 'string'],
            'attachments' => ['nullable', 'array'],
        ];
    }
}

