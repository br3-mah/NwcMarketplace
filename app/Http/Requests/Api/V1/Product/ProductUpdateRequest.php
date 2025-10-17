<?php

namespace App\Http\Requests\Api\V1\Product;

use App\Http\Requests\Api\ApiRequest;
use Illuminate\Validation\Rule;

class ProductUpdateRequest extends ApiRequest
{
    protected function prepareForValidation(): void
    {
        $payload = [];

        foreach ([
            'SellerId' => 'seller_id',
            'Title' => 'title',
            'Description' => 'description',
            'CategoryIds' => 'category_ids',
            'Price' => 'price',
            'Currency' => 'currency',
            'Sku' => 'sku',
            'StockQty' => 'stock_qty',
        ] as $source => $target) {
            if ($this->has($source)) {
                $payload[$target] = $this->input($source);
            }
        }

        if (!empty($payload)) {
            $this->merge($payload);
        }
    }

    public function rules(): array
    {
        $product = $this->route('product');
        $productId = is_object($product) ? $product->id : $product;

        return [
            'seller_id' => ['sometimes', 'integer', 'exists:users,id'],
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'category_ids' => ['sometimes', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'currency' => ['sometimes', 'string', 'max:10'],
            'sku' => [
                'sometimes',
                'nullable',
                'string',
                'max:100',
                Rule::unique('products', 'sku')->ignore($productId),
            ],
            'stock_qty' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}

