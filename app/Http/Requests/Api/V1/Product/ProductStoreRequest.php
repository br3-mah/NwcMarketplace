<?php

namespace App\Http\Requests\Api\V1\Product;

use App\Http\Requests\Api\ApiRequest;
use Illuminate\Validation\Rule;

class ProductStoreRequest extends ApiRequest
{
    protected function prepareForValidation(): void
    {
        $payload = [];

        if ($this->has('SellerId')) {
            $payload['seller_id'] = $this->input('SellerId');
        }

        if ($this->has('Title')) {
            $payload['title'] = $this->input('Title');
        }

        if ($this->has('Description')) {
            $payload['description'] = $this->input('Description');
        }

        if ($this->has('CategoryIds')) {
            $payload['category_ids'] = $this->input('CategoryIds');
        }

        if ($this->has('Price')) {
            $payload['price'] = $this->input('Price');
        }

        if ($this->has('Currency')) {
            $payload['currency'] = $this->input('Currency');
        }

        if ($this->has('Sku')) {
            $payload['sku'] = $this->input('Sku');
        }

        if ($this->has('StockQty')) {
            $payload['stock_qty'] = $this->input('StockQty');
        }

        if ($this->has('Images')) {
            $payload['images'] = $this->input('Images');
        }

        if (!empty($payload)) {
            $this->merge($payload);
        }
    }

    public function rules(): array
    {
        return [
            'seller_id' => ['required', 'integer', 'exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'category_ids' => ['sometimes', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
            'price' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'max:10'],
            'sku' => ['sometimes', 'nullable', 'string', 'max:100', Rule::unique('products', 'sku')],
            'stock_qty' => ['required', 'integer', 'min:0'],
            'images' => ['sometimes', 'array'],
            'images.*' => ['string', 'max:255'],
        ];
    }
}

