<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\User\UserIndexRequest;
use App\Http\Requests\Api\V1\User\UserUpdateRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use App\Services\User\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserController extends Controller
{
    public function __construct(private readonly UserService $users)
    {
    }

    public function index(UserIndexRequest $request): AnonymousResourceCollection
    {
        $users = $this->users->paginateUsers($request->validated());

        return UserResource::collection($users)
            ->additional([
                'message' => 'Users retrieved successfully.',
            ]);
    }

    public function show(User $user): JsonResponse
    {
        $user = $this->users->loadUserRelations($user);

        return (new UserResource($user))
            ->additional([
                'message' => 'User retrieved successfully.',
            ])
            ->response();
    }

    public function update(UserUpdateRequest $request, User $user): JsonResponse
    {
        $user = $this->users->updateUser($user, $request->validated());

        return (new UserResource($user))
            ->additional([
                'message' => 'User updated successfully.',
            ])
            ->response();
    }
}

