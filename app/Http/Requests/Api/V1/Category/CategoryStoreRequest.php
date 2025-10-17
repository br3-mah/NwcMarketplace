<?php

namespace App\Http\Requests\Api\V1\Category;

use App\Http\Requests\Api\ApiRequest;

class CategoryStoreRequest extends ApiRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->has('status')) {
            $status = $this->input('status');

            if (is_string($status)) {
                $normalized = strtolower($status);

                $status = match ($normalized) {
                    '1', 'true', 'active', 'enabled' => 1,
                    '0', 'false', 'inactive', 'disabled' => 0,
                    default => $status,
                };
            }

            $this->merge([
                'status' => is_numeric($status) ? (int) $status : $status,
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:191'],
            'status' => ['sometimes', 'nullable', 'in:0,1'],
            'slug' => ['sometimes', 'nullable', 'string', 'max:191'],
            'language_id' => ['sometimes', 'nullable', 'integer', 'exists:languages,id'],
        ];
    }
}

