<?php

namespace App\Http\Requests\Api\V1\Notification;

use App\Http\Requests\Api\ApiRequest;

class NotificationSendRequest extends ApiRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'user_ids' => $this->input('userIds', $this->input('user_ids', [])),
            'vendor_ids' => $this->input('vendorIds', $this->input('vendor_ids', [])),
            'order_id' => $this->input('orderId', $this->input('order_id')),
            'product_id' => $this->input('productId', $this->input('product_id')),
            'title' => $this->input('title'),
            'message' => $this->input('message'),
            'data' => $this->input('data', []),
        ]);
    }

    public function rules(): array
    {
        return [
            'user_ids' => ['nullable', 'array'],
            'user_ids.*' => ['integer', 'exists:users,id'],
            'vendor_ids' => ['nullable', 'array'],
            'vendor_ids.*' => ['integer', 'exists:users,id'],
            'order_id' => ['nullable', 'integer', 'exists:orders,id'],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'title' => ['required', 'string', 'max:191'],
            'message' => ['required', 'string', 'max:5000'],
            'data' => ['nullable', 'array'],
        ];
    }
}

