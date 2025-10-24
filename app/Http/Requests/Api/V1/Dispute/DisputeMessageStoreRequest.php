<?php

namespace App\Http\Requests\Api\V1\Dispute;

use App\Http\Requests\Api\ApiRequest;

class DisputeMessageStoreRequest extends ApiRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'message' => $this->input('message'),
            'attachments' => $this->input('attachments', []),
        ]);
    }

    public function rules(): array
    {
        return [
            'message' => ['required', 'string'],
            'attachments' => ['nullable', 'array'],
        ];
    }
}

