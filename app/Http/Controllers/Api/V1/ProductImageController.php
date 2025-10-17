<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Product\ProductImageStoreRequest;
use App\Http\Resources\Api\V1\ProductImageResource;
use App\Models\Product;
use App\Services\Product\ProductService;
use Illuminate\Http\JsonResponse;

class ProductImageController extends Controller
{
    public function __construct(private readonly ProductService $products)
    {
    }

    public function store(ProductImageStoreRequest $request, Product $product): JsonResponse
    {
        $image = $this->products->addImage($product, $request->validated());

        return (new ProductImageResource($image))
            ->additional([
                'message' => 'Product image added successfully.',
            ])
            ->response()
            ->setStatusCode(201);
    }
}

