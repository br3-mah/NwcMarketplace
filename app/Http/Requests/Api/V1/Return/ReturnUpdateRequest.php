<?php

namespace App\Http\Requests\Api\V1\Return;

use App\Http\Requests\Api\ApiRequest;

class ReturnUpdateRequest extends ApiRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'status' => strtolower((string) $this->input('status')),
            'notes' => $this->input('notes'),
            'attachments' => $this->input('attachments', []),
            'resolved_at' => $this->input('resolvedAt', $this->input('resolved_at')),
        ]);
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', 'string', 'in:pending,approved,rejected,received,refunded,closed'],
            'notes' => ['nullable', 'string'],
            'attachments' => ['nullable', 'array'],
            'resolved_at' => ['nullable', 'date'],
        ];
    }
}

