<?php

namespace App\Http\Requests\Api\V1\Shipping;

use App\Http\Requests\Api\ApiRequest;

class ProofOfDeliveryRequest extends ApiRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'signed_by' => $this->input('signedBy', $this->input('signed_by')),
            'signed_at' => $this->input('signedAt', $this->input('signed_at')),
            'attachments' => $this->input('attachments', []),
        ]);
    }

    public function rules(): array
    {
        return [
            'signed_by' => ['required', 'string', 'max:191'],
            'signed_at' => ['nullable', 'date'],
            'attachments' => ['nullable', 'array'],
        ];
    }
}

