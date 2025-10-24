<?php

namespace App\Http\Requests\Api\V1\Order;

use App\Http\Requests\Api\ApiRequest;

class OrderConfirmDeliveryRequest extends ApiRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'confirmation' => $this->boolean('Confirmation', $this->input('confirmation', true)),
            'note' => $this->input('note', $this->input('notes')),
        ]);
    }

    public function rules(): array
    {
        return [
            'confirmation' => ['required', 'boolean'],
            'note' => ['nullable', 'string', 'max:2000'],
        ];
    }

    private function boolean($key, $default = null): bool
    {
        $value = $this->input($key, $default);

        if (is_bool($value)) {
            return $value;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
    }
}

