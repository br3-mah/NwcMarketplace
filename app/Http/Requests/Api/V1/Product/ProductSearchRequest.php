<?php

namespace App\Http\Requests\Api\V1\Product;

use App\Http\Requests\Api\ApiRequest;

class ProductSearchRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'q' => ['sometimes', 'string', 'max:191'],
            'category' => ['sometimes', 'string', 'max:191'],
            'min' => ['sometimes', 'numeric', 'min:0'],
            'max' => ['sometimes', 'numeric', 'min:0'],
            'sort' => ['sometimes', 'string', 'max:50'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}

