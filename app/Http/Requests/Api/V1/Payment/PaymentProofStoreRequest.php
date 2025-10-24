<?php

namespace App\Http\Requests\Api\V1\Payment;

use App\Http\Requests\Api\ApiRequest;

class PaymentProofStoreRequest extends ApiRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'order_number' => $this->input('orderNumber', $this->input('order_number')),
            'reference' => $this->input('reference'),
            'details' => $this->input('details'),
            'attachments' => $this->input('attachments', []),
        ]);
    }

    public function rules(): array
    {
        return [
            'order_number' => ['required', 'string', 'max:191'],
            'reference' => ['nullable', 'string', 'max:191'],
            'details' => ['nullable', 'array'],
            'attachments' => ['nullable', 'array'],
        ];
    }
}

