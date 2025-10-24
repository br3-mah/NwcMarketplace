<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\AuthorizesRoles;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Return\ReturnStoreRequest;
use App\Http\Requests\Api\V1\Return\ReturnUpdateRequest;
use App\Http\Resources\Api\V1\ReturnResource;
use App\Models\Notification;
use App\Models\Order;
use App\Models\ReturnRequest;
use App\Models\VendorOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReturnController extends Controller
{
    use AuthorizesRoles;

    public function store(ReturnStoreRequest $request): JsonResponse
    {
        $user = $request->user();
        $order = Order::findOrFail($request->input('order_id'));

        if ($order->user_id !== $user->id) {
            abort(403, 'Only order owners can create returns.');
        }

        $return = ReturnRequest::create([
            'order_id' => $order->id,
            'user_id' => $user->id,
            'status' => 'pending',
            'reason' => $request->input('reason'),
            'notes' => $request->input('notes'),
            'items' => $request->input('items'),
            'attachments' => $request->input('attachments'),
        ]);

        $this->notifyReturn($return, 'Return requested', $return->reason);

        return (new ReturnResource($return))->response()->setStatusCode(201);
    }

    public function show(ReturnRequest $return, Request $request): JsonResponse
    {
        $this->authorizeReturnAccess($return, $request->user());

        return (new ReturnResource($return))->response();
    }

    public function update(ReturnUpdateRequest $request, ReturnRequest $return): JsonResponse
    {
        $user = $request->user();
        $this->authorizeReturnAccess($return, $user, allowCustomer: false);

        $return->fill($request->validated());

        if ($request->filled('resolved_at')) {
            $return->resolved_at = $request->input('resolved_at');
        }

        $return->save();

        $this->notifyReturn($return, 'Return updated', $return->notes ?? 'Return status updated.');

        return (new ReturnResource($return->refresh()))->response();
    }

    private function authorizeReturnAccess(ReturnRequest $return, $user, bool $allowCustomer = true): void
    {
        if ($allowCustomer && $return->user_id === $user->id) {
            return;
        }

        $isVendor = VendorOrder::where('order_id', $return->order_id)
            ->where('user_id', $user->id)
            ->exists();

        if ($isVendor) {
            return;
        }

        $this->ensureAdmin($user);
    }

    private function notifyReturn(ReturnRequest $return, string $title, string $message): void
    {
        $payload = [
            'title' => $title,
            'message' => $message,
            'data' => [
                'return_id' => $return->id,
                'order_id' => $return->order_id,
                'status' => $return->status,
            ],
        ];

        if ($return->user_id) {
            Notification::create([
                'order_id' => $return->order_id,
                'user_id' => $return->user_id,
                'data' => $payload,
            ]);
        }

        $vendorIds = VendorOrder::where('order_id', $return->order_id)->pluck('user_id')->unique();
        foreach ($vendorIds as $vendorId) {
            Notification::create([
                'order_id' => $return->order_id,
                'vendor_id' => $vendorId,
                'data' => $payload,
            ]);
        }
    }
}

