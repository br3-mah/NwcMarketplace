<?php

namespace App\Http\Requests\Api\V1\Product;

use App\Http\Requests\Api\ApiRequest;

class ProductInventoryUpdateRequest extends ApiRequest
{
    protected function prepareForValidation(): void
    {
        $payload = [];

        if ($this->has('StockQty')) {
            $payload['stock_qty'] = $this->input('StockQty');
        }

        if ($this->has('LowStockThreshold')) {
            $payload['low_stock_threshold'] = $this->input('LowStockThreshold');
        }

        if (!empty($payload)) {
            $this->merge($payload);
        }
    }

    public function rules(): array
    {
        return [
            'stock_qty' => ['required_without:StockQty', 'integer', 'min:0'],
            'StockQty' => ['required_without:stock_qty', 'integer', 'min:0'],
            'low_stock_threshold' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'LowStockThreshold' => ['sometimes', 'nullable', 'integer', 'min:0'],
        ];
    }
}

