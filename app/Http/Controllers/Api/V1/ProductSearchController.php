<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Product\ProductSearchRequest;
use App\Http\Resources\Api\V1\ProductResource;
use App\Services\Product\ProductService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductSearchController extends Controller
{
    public function __construct(private readonly ProductService $products)
    {
    }

    public function __invoke(ProductSearchRequest $request): AnonymousResourceCollection
    {
        $products = $this->products->search($request->validated());

        return ProductResource::collection($products)
            ->additional([
                'message' => 'Products retrieved successfully.',
            ]);
    }
}

