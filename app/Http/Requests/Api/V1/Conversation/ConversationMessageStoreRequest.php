<?php

namespace App\Http\Requests\Api\V1\Conversation;

use App\Http\Requests\Api\ApiRequest;

class ConversationMessageStoreRequest extends ApiRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'message' => $this->input('message'),
        ]);
    }

    public function rules(): array
    {
        return [
            'message' => ['required', 'string', 'max:5000'],
        ];
    }
}

