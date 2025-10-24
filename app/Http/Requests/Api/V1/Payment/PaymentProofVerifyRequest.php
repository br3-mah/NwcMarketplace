<?php

namespace App\Http\Requests\Api\V1\Payment;

use App\Http\Requests\Api\ApiRequest;

class PaymentProofVerifyRequest extends ApiRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'status' => strtolower((string) $this->input('status', 'verified')),
            'notes' => $this->input('notes'),
        ]);
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:verified,rejected,pending'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}

