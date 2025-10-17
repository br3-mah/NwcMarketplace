<?php

namespace App\Http\Requests\Api\V1\Product;

use App\Http\Requests\Api\ApiRequest;

class ProductIndexRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'sellerId' => ['sometimes', 'string', 'max:36'],
            'category' => ['sometimes', 'string', 'max:191'],
            'q' => ['sometimes', 'string', 'max:191'],
            'min' => ['sometimes', 'numeric', 'min:0'],
            'max' => ['sometimes', 'numeric', 'min:0'],
            'sort' => ['sometimes', 'string', 'max:50'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}

