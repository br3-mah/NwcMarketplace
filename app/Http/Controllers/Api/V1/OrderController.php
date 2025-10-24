<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\AuthorizesRoles;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Order\OrderCancelRequest;
use App\Http\Requests\Api\V1\Order\OrderConfirmDeliveryRequest;
use App\Http\Requests\Api\V1\Order\OrderIndexRequest;
use App\Http\Requests\Api\V1\Order\OrderStatusUpdateRequest;
use App\Http\Resources\Api\V1\OrderDetailResource;
use App\Http\Resources\Api\V1\OrderResource;
use App\Http\Resources\Api\V1\OrderTimelineResource;
use App\Models\Order;
use App\Models\User;
use App\Models\VendorOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    use AuthorizesRoles;
    public function index(OrderIndexRequest $request): JsonResponse
    {
        $user = $request->user();
        $role = $request->input('role', 'buyer') ?: 'buyer';

        $query = Order::query();

        if ($role === 'admin') {
            $this->ensureAdmin($user);
        } elseif ($role === 'seller') {
            $vendorOrderIds = VendorOrder::where('user_id', $user->id)
                ->pluck('order_id')
                ->unique()
                ->all();

            $query->whereIn('id', $vendorOrderIds ?: [0]);
        } else {
            $query->where('user_id', $user->id);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->input('q')) {
            $query->where(function ($builder) use ($search) {
                $builder->where('order_number', 'like', '%' . $search . '%')
                    ->orWhere('customer_email', 'like', '%' . $search . '%');
            });
        }

        $perPage = (int) ($request->input('per_page') ?? 15);
        $perPage = max(1, min(100, $perPage));

        $orders = $query
            ->orderByDesc('id')
            ->paginate($perPage);

        return OrderResource::collection($orders)->response();
    }

    public function show(Request $request, string $orderNumber): JsonResponse
    {
        $user = $request->user();
        $order = $this->findOrderOrFail($orderNumber);
        $role = strtolower((string) $request->input('role', 'buyer'));

        $this->authorizeAccess($order, $user, $role);

        return (new OrderDetailResource($order))->response();
    }

    public function cancel(OrderCancelRequest $request, string $orderNumber): JsonResponse
    {
        $user = $request->user();
        $order = $this->findOrderOrFail($orderNumber);
        $this->authorizeAccess($order, $user, 'user');

        if (!in_array($order->status, ['pending', 'processing'], true)) {
            return response()->json([
                'message' => 'Order can no longer be cancelled.',
            ], 422);
        }

        $reason = $request->input('reason');

        DB::transaction(function () use ($order, $reason) {
            $order->status = 'declined';
            $order->payment_status = $order->payment_status ?? 'Pending';
            $order->save();

            $order->tracks()->create([
                'title' => 'Cancelled',
                'text' => $reason ?: 'Order cancelled by customer.',
            ]);
        });

        return response()->json([
            'message' => 'Order cancelled successfully.',
            'data' => new OrderDetailResource($order->fresh()),
        ]);
    }

    public function updateStatus(OrderStatusUpdateRequest $request, string $orderNumber): JsonResponse
    {
        $user = $request->user();
        $order = $this->findOrderOrFail($orderNumber);

        $role = strtolower((string) $request->input('role', 'buyer'));

        if (!in_array($role, ['seller', 'admin'], true)) {
            return response()->json([
                'message' => 'Only sellers or admins may update status.',
            ], 403);
        }

        $this->authorizeAccess($order, $user, $role);

        $status = $request->input('status');
        $note = $request->input('note');

        DB::transaction(function () use ($order, $status, $note) {
            $order->status = $this->mapOrderStatus($status);
            if ($status === 'delivered') {
                $order->payment_status = 'Completed';
            }
            if ($status === 'failed') {
                $order->payment_status = 'Pending';
            }
            $order->save();

            $order->tracks()->create([
                'title' => ucfirst($status),
                'text' => $note ?: sprintf('Order status updated to %s.', $status),
            ]);
        });

        return response()->json([
            'message' => 'Order status updated.',
            'data' => new OrderDetailResource($order->fresh()),
        ]);
    }

    public function confirmDelivery(OrderConfirmDeliveryRequest $request, string $orderNumber): JsonResponse
    {
        $user = $request->user();
        $order = $this->findOrderOrFail($orderNumber);

        $this->authorizeAccess($order, $user, 'user');

        if (!$request->input('confirmation')) {
            return response()->json([
                'message' => 'Confirmation flag must be true.',
            ], 422);
        }

        DB::transaction(function () use ($order, $request) {
            $order->status = 'completed';
            $order->payment_status = 'Completed';
            $order->save();

            $order->tracks()->create([
                'title' => 'Delivered',
                'text' => $request->input('note') ?: 'Buyer confirmed delivery.',
            ]);
        });

        return response()->json([
            'message' => 'Delivery confirmed.',
            'data' => new OrderDetailResource($order->fresh()),
        ]);
    }

    public function timeline(Request $request, string $orderNumber): JsonResponse
    {
        $user = $request->user();
        $order = $this->findOrderOrFail($orderNumber);
        $role = strtolower((string) $request->input('role', 'buyer'));

        $this->authorizeAccess($order, $user, $role);

        $tracks = $order->tracks()->orderBy('created_at')->get();

        return OrderTimelineResource::collection($tracks)->response();
    }

    private function findOrderOrFail(string $orderNumber): Order
    {
        return Order::where('order_number', $orderNumber)->firstOrFail();
    }

    private function authorizeAccess(Order $order, User $user, string $role): void
    {
        if ($role === 'admin') {
            $this->ensureAdmin($user);
            return;
        }

        if ($role === 'seller') {
            $hasOrder = VendorOrder::where('order_id', $order->id)
                ->where('user_id', $user->id)
                ->exists();

            if (!$hasOrder) {
                abort(403, 'You do not have access to this order.');
            }

            return;
        }

        if ($order->user_id !== $user->id) {
            abort(403, 'You do not have access to this order.');
        }
    }


    private function mapOrderStatus(string $status): string
    {
        return match ($status) {
            'packed', 'shipped', 'processing' => 'processing',
            'delivered', 'completed' => 'completed',
            'failed', 'declined' => 'declined',
            default => 'pending',
        };
    }
}


