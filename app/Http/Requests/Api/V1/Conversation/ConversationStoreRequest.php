<?php

namespace App\Http\Requests\Api\V1\Conversation;

use App\Http\Requests\Api\ApiRequest;

class ConversationStoreRequest extends ApiRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'recipient_id' => $this->input('recipientId', $this->input('recipient_id')),
            'subject' => $this->input('subject'),
            'message' => $this->input('message'),
            'order_id' => $this->input('orderId', $this->input('order_id')),
        ]);
    }

    public function rules(): array
    {
        return [
            'recipient_id' => ['required', 'integer', 'exists:users,id'],
            'subject' => ['nullable', 'string', 'max:191'],
            'message' => ['required', 'string', 'max:5000'],
            'order_id' => ['nullable', 'string', 'max:191'],
        ];
    }
}

