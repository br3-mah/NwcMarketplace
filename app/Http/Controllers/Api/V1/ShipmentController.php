<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\AuthorizesRoles;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Shipping\ProofOfDeliveryRequest;
use App\Http\Requests\Api\V1\Shipping\ShipmentCancelRequest;
use App\Http\Requests\Api\V1\Shipping\ShipmentStoreRequest;
use App\Http\Resources\Api\V1\ShipmentEventResource;
use App\Http\Resources\Api\V1\ShipmentResource;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Shipment;
use App\Models\ShipmentEvent;
use App\Models\User;
use App\Models\VendorOrder;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ShipmentController extends Controller
{
    use AuthorizesRoles;

    public function store(ShipmentStoreRequest $request): JsonResponse
    {
        $user = $request->user();
        $order = $request->input('order_id') ? Order::findOrFail($request->input('order_id')) : null;

        $this->authorizeShipmentCreation($user, $order);

        $shipment = DB::transaction(function () use ($request, $user, $order) {
            $shipment = Shipment::create([
                'order_id' => $order?->id,
                'user_id' => $user->id,
                'tracking_number' => $request->input('tracking_number'),
                'status' => 'pending',
                'service_code' => $request->input('service_code'),
                'service_name' => $request->input('service_name'),
                'cost' => (float) $request->input('cost', 0),
                'currency_sign' => $request->input('currency_sign'),
                'metadata' => $request->input('metadata'),
                'expected_delivery_at' => $request->input('expected_delivery_at'),
                'shipped_at' => now(),
            ]);

            ShipmentEvent::create([
                'shipment_id' => $shipment->id,
                'event_code' => 'created',
                'status' => 'pending',
                'description' => 'Shipment created.',
                'occurred_at' => now(),
            ]);

            $this->notifyShipment($shipment, 'Shipment created', sprintf('Tracking number %s generated.', $shipment->tracking_number));

            return $shipment;
        });

        return (new ShipmentResource($shipment->fresh()))->response()->setStatusCode(201);
    }

    public function show(Shipment $shipment, Request $request): JsonResponse
    {
        $this->authorizeShipmentAccess($shipment, $request->user());

        $shipment->load('events');

        return (new ShipmentResource($shipment))->response();
    }

    public function events(Shipment $shipment, Request $request): JsonResponse
    {
        $this->authorizeShipmentAccess($shipment, $request->user());

        $events = $shipment->events()->orderByDesc('occurred_at')->get();

        return ShipmentEventResource::collection($events)->response();
    }

    public function cancel(ShipmentCancelRequest $request, Shipment $shipment): JsonResponse
    {
        $this->authorizeShipmentAccess($shipment, $request->user());

        if ($shipment->status === 'canceled') {
            return response()->json([
                'message' => 'Shipment already canceled.',
            ], 422);
        }

        DB::transaction(function () use ($shipment, $request) {
            $shipment->status = 'canceled';
            $shipment->canceled_at = now();
            $shipment->cancellation_reason = $request->input('reason');
            $shipment->save();

            ShipmentEvent::create([
                'shipment_id' => $shipment->id,
                'event_code' => 'canceled',
                'status' => 'canceled',
                'description' => $request->input('reason') ?: 'Shipment canceled.',
                'occurred_at' => now(),
            ]);

            $this->notifyShipment($shipment, 'Shipment canceled', $shipment->cancellation_reason ?: 'Shipment was canceled.');
        });

        return (new ShipmentResource($shipment->fresh()))->response();
    }

    public function storeProofOfDelivery(ProofOfDeliveryRequest $request, Shipment $shipment): JsonResponse
    {
        $this->authorizeShipmentAccess($shipment, $request->user());

        $shipment->pod_signed_by = $request->input('signed_by');
        $shipment->pod_signed_at = $request->input('signed_at')
            ? Carbon::parse($request->input('signed_at'))
            : now();
        $shipment->pod_attachments = $request->input('attachments');
        $shipment->status = 'delivered';
        $shipment->delivered_at = $shipment->pod_signed_at;
        $shipment->save();

        ShipmentEvent::create([
            'shipment_id' => $shipment->id,
            'event_code' => 'delivered',
            'status' => 'delivered',
            'description' => sprintf('Shipment delivered and signed by %s.', $shipment->pod_signed_by),
            'occurred_at' => $shipment->pod_signed_at,
        ]);

        $this->notifyShipment($shipment, 'Shipment delivered', sprintf('Proof of delivery captured for %s.', $shipment->tracking_number));

        return (new ShipmentResource($shipment->fresh()))->response()->setStatusCode(201);
    }

    public function showProofOfDelivery(Shipment $shipment, Request $request): JsonResponse
    {
        $this->authorizeShipmentAccess($shipment, $request->user());

        if (!$shipment->pod_signed_by) {
            return response()->json([
                'message' => 'Proof of delivery not available.',
            ], 404);
        }

        return response()->json([
            'data' => [
                'signed_by' => $shipment->pod_signed_by,
                'signed_at' => $shipment->pod_signed_at?->toIso8601String(),
                'attachments' => $shipment->pod_attachments,
            ],
        ]);
    }

    private function authorizeShipmentCreation(User $user, ?Order $order): void
    {
        if (!$order) {
            $this->ensureAdmin($user);
            return;
        }

        if ($user->id === $order->user_id) {
            return;
        }

        $isVendor = VendorOrder::where('order_id', $order->id)
            ->where('user_id', $user->id)
            ->exists();

        if ($isVendor) {
            return;
        }

        $this->ensureAdmin($user);
    }

    private function authorizeShipmentAccess(Shipment $shipment, User $user): void
    {
        if ($shipment->user_id === $user->id) {
            return;
        }

        if ($shipment->order && $shipment->order->user_id === $user->id) {
            return;
        }

        if ($shipment->order && VendorOrder::where('order_id', $shipment->order_id)->where('user_id', $user->id)->exists()) {
            return;
        }

        $this->ensureAdmin($user);
    }

    private function notifyShipment(Shipment $shipment, string $title, string $message): void
    {
        $order = $shipment->order;
        $payload = [
            'title' => $title,
            'message' => $message,
            'data' => [
                'shipment_id' => $shipment->id,
                'tracking_number' => $shipment->tracking_number,
                'status' => $shipment->status,
            ],
        ];

        if ($order && $order->user_id) {
            Notification::create([
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'data' => $payload,
            ]);
        }

        if ($order) {
            $vendorIds = VendorOrder::where('order_id', $order->id)->pluck('user_id')->unique();
            foreach ($vendorIds as $vendorId) {
                Notification::create([
                    'order_id' => $order->id,
                    'vendor_id' => $vendorId,
                    'data' => $payload,
                ]);
            }
        }
    }
}
