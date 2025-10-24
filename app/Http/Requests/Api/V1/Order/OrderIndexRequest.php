<?php

namespace App\Http\Requests\Api\V1\Order;

use App\Http\Requests\Api\ApiRequest;

class OrderIndexRequest extends ApiRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'role' => strtolower((string) $this->input('role')),
            'status' => $this->input('status'),
            'q' => $this->input('q'),
            'per_page' => $this->input('perPage', $this->input('per_page')),
        ]);
    }

    public function rules(): array
    {
        return [
            'role' => ['nullable', 'string', 'in:buyer,seller,admin'],
            'status' => ['nullable', 'string', 'max:191'],
            'q' => ['nullable', 'string', 'max:191'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}

