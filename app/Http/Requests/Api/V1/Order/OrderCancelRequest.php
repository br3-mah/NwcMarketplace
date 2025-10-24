<?php

namespace App\Http\Requests\Api\V1\Order;

use App\Http\Requests\Api\ApiRequest;

class OrderCancelRequest extends ApiRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'reason' => $this->input('reason', $this->input('Reason')),
        ]);
    }

    public function rules(): array
    {
        return [
            'reason' => ['nullable', 'string', 'max:2000'],
        ];
    }
}

