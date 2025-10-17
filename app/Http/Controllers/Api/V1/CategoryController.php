<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Category\CategoryStoreRequest;
use App\Http\Resources\Api\V1\CategoryResource;
use App\Services\Category\CategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CategoryController extends Controller
{
    public function __construct(private readonly CategoryService $categories)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $categories = $this->categories->listCategories([
            'status' => $request->query('status'),
            'q' => $request->query('q'),
        ]);

        return CategoryResource::collection($categories)
            ->additional([
                'message' => 'Categories retrieved successfully.',
            ]);
    }

    public function store(CategoryStoreRequest $request): JsonResponse
    {
        $category = $this->categories->createCategory($request->validated());

        return (new CategoryResource($category))
            ->additional([
                'message' => 'Category created successfully.',
            ])
            ->response()
            ->setStatusCode(201);
    }
}

