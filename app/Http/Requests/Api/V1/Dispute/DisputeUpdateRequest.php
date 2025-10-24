<?php

namespace App\Http\Requests\Api\V1\Dispute;

use App\Http\Requests\Api\ApiRequest;

class DisputeUpdateRequest extends ApiRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'status' => strtolower((string) $this->input('status')),
            'resolution_notes' => $this->input('resolutionNotes', $this->input('resolution_notes')),
            'attachments' => $this->input('attachments', []),
        ]);
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', 'string', 'in:open,pending,escalated,resolved,closed'],
            'resolution_notes' => ['nullable', 'string'],
            'attachments' => ['nullable', 'array'],
        ];
    }
}

