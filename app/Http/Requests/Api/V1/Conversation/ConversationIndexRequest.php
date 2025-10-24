<?php

namespace App\Http\Requests\Api\V1\Conversation;

use App\Http\Requests\Api\ApiRequest;

class ConversationIndexRequest extends ApiRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'order_id' => $this->input('orderId', $this->input('order_id')),
            'user_id' => $this->input('userId', $this->input('user_id')),
            'per_page' => $this->input('perPage', $this->input('per_page')),
        ]);
    }

    public function rules(): array
    {
        return [
            'order_id' => ['nullable', 'string', 'max:191'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}

