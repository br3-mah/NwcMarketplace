<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Product\ProductIndexRequest;
use App\Http\Requests\Api\V1\Product\ProductStoreRequest;
use App\Http\Requests\Api\V1\Product\ProductUpdateRequest;
use App\Http\Resources\Api\V1\ProductResource;
use App\Models\Product;
use App\Services\Product\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ProductController extends Controller
{
    public function __construct(private readonly ProductService $products)
    {
    }

    public function index(ProductIndexRequest $request): AnonymousResourceCollection
    {
        $products = $this->products->paginate($request->validated());

        return ProductResource::collection($products)
            ->additional([
                'message' => 'Products retrieved successfully.',
            ]);
    }

    public function store(ProductStoreRequest $request): JsonResponse
    {
        $product = $this->products->create($request->validated());

        return (new ProductResource($product))
            ->additional([
                'message' => 'Product created successfully.',
            ])
            ->response()
            ->setStatusCode(201);
    }

    public function show(Product $product): JsonResponse
    {
        $product->load($this->products->defaultRelations());

        return (new ProductResource($product))
            ->additional([
                'message' => 'Product retrieved successfully.',
            ])
            ->response();
    }

    public function update(ProductUpdateRequest $request, Product $product): JsonResponse
    {
        $product = $this->products->update($product, $request->validated());

        return (new ProductResource($product))
            ->additional([
                'message' => 'Product updated successfully.',
            ])
            ->response();
    }

    public function destroy(Product $product): Response
    {
        $this->products->delete($product);

        return response()->noContent();
    }
}

