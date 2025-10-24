<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\AuthorizesRoles;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Notification\NotificationSendRequest;
use App\Http\Resources\Api\V1\NotificationResource;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class NotificationController extends Controller
{
    use AuthorizesRoles;

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $scope = strtolower((string) $request->query('scope', 'personal'));
        $onlyUnread = filter_var($request->query('unread', false), FILTER_VALIDATE_BOOLEAN);
        $markRead = filter_var($request->query('mark_read', false), FILTER_VALIDATE_BOOLEAN);

        $query = Notification::query()->latest();

        if ($scope === 'all') {
            $this->ensureAdmin($user);
        } else {
            $query->where(function ($builder) use ($user) {
                $builder->where('user_id', $user->id)
                    ->orWhere('vendor_id', $user->id);
            });
        }

        if ($onlyUnread) {
            $query->where('is_read', 0);
        }

        $perPage = (int) $request->query('per_page', 20);
        $perPage = max(1, min(100, $perPage));

        $notifications = $query->paginate($perPage);

        if ($markRead) {
            $ids = $notifications->pluck('id')->all();
            if ($ids) {
                Notification::whereIn('id', $ids)->update(['is_read' => 1]);
            }
        }

        return NotificationResource::collection($notifications)->response();
    }

    public function send(NotificationSendRequest $request): JsonResponse
    {
        $this->ensureAdmin($request->user());

        $userIds = collect($request->input('user_ids', []))->unique()->values();
        $vendorIds = collect($request->input('vendor_ids', []))->unique()->values();

        if ($userIds->isEmpty() && $vendorIds->isEmpty()) {
            return response()->json([
                'message' => 'At least one user or vendor id is required.',
            ], 422);
        }

        $payload = [
            'title' => $request->input('title'),
            'message' => $request->input('message'),
            'data' => Arr::wrap($request->input('data')),
        ];

        $created = [];

        foreach ($userIds as $id) {
            $created[] = Notification::create([
                'user_id' => $id,
                'order_id' => $request->input('order_id'),
                'product_id' => $request->input('product_id'),
                'data' => $payload,
            ]);
        }

        foreach ($vendorIds as $id) {
            $created[] = Notification::create([
                'vendor_id' => $id,
                'order_id' => $request->input('order_id'),
                'product_id' => $request->input('product_id'),
                'data' => $payload,
            ]);
        }

        return response()->json([
            'message' => 'Notification dispatch accepted.',
            'created' => collect($created)->pluck('id'),
        ], 202);
    }
}

