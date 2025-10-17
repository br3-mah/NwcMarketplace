<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Product\ProductInventoryUpdateRequest;
use App\Models\Product;
use App\Services\Product\ProductService;
use Illuminate\Http\JsonResponse;

class ProductInventoryController extends Controller
{
    public function __construct(private readonly ProductService $products)
    {
    }

    public function show(Product $product): JsonResponse
    {
        return response()->json([
            'message' => 'Inventory retrieved successfully.',
            'data' => $this->products->inventoryData($product),
        ]);
    }

    public function update(ProductInventoryUpdateRequest $request, Product $product): JsonResponse
    {
        $product = $this->products->updateInventory($product, $request->validated());

        return response()->json([
            'message' => 'Inventory updated successfully.',
            'data' => $this->products->inventoryData($product),
        ]);
    }
}

