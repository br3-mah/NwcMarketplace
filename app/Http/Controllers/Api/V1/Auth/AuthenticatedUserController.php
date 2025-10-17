<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\UserResource;
use App\Services\User\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthenticatedUserController extends Controller
{
    public function __construct(private readonly UserService $users)
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $user = $this->users->loadUserRelations($request->user());

        return (new UserResource($user))
            ->additional([
                'message' => 'Authenticated user retrieved successfully.',
            ])
            ->response();
    }
}

