<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\AuthorizesRoles;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Notification\NotificationTemplateStoreRequest;
use App\Http\Requests\Api\V1\Notification\NotificationTemplateUpdateRequest;
use App\Http\Resources\Api\V1\NotificationTemplateResource;
use App\Models\NotificationTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationTemplateController extends Controller
{
    use AuthorizesRoles;

    public function index(Request $request): JsonResponse
    {
        $this->ensureAdmin($request->user());

        $perPage = (int) $request->query('per_page', 20);
        $perPage = max(1, min(100, $perPage));

        $templates = NotificationTemplate::query()
            ->orderBy('name')
            ->paginate($perPage);

        return NotificationTemplateResource::collection($templates)->response();
    }

    public function store(NotificationTemplateStoreRequest $request): JsonResponse
    {
        $this->ensureAdmin($request->user());

        $template = NotificationTemplate::create($request->validated());

        return (new NotificationTemplateResource($template))
            ->response()
            ->setStatusCode(201);
    }

    public function update(NotificationTemplateUpdateRequest $request, NotificationTemplate $template): JsonResponse
    {
        $this->ensureAdmin($request->user());

        $template->update($request->validated());

        return (new NotificationTemplateResource($template->refresh()))->response();
    }
}

