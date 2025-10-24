<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Payment\PaymentProofStoreRequest;
use App\Http\Requests\Api\V1\Payment\PaymentProofVerifyRequest;
use App\Http\Resources\Api\V1\PaymentProofResource;
use App\Models\Order;
use App\Models\PaymentProof;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentProofController extends Controller
{
    public function store(PaymentProofStoreRequest $request): JsonResponse
    {
        $user = $request->user();
        $order = $this->findOrderOrFail($request->input('order_number'));

        $this->authorizeOrderAccess($order, $user);

        $proof = PaymentProof::create([
            'order_id' => $order->id,
            'user_id' => $user?->id,
            'reference' => $request->input('reference'),
            'status' => 'pending',
            'payload' => $request->input('details'),
            'attachments' => $request->input('attachments'),
        ]);

        return (new PaymentProofResource($proof->fresh()))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, PaymentProof $paymentProof): JsonResponse
    {
        $user = $request->user();
        $order = $paymentProof->order;

        if (!$order) {
            abort(404, 'Associated order not found.');
        }

        $this->authorizeOrderAccess($order, $user, allowAdmin: true);

        return (new PaymentProofResource($paymentProof))->response();
    }

    public function verify(PaymentProofVerifyRequest $request, PaymentProof $paymentProof): JsonResponse
    {
        $user = $request->user();
        $order = $paymentProof->order;

        if (!$order) {
            abort(404, 'Associated order not found.');
        }

        $this->ensureAdmin($user);

        DB::transaction(function () use ($paymentProof, $request, $user, $order) {
            $paymentProof->status = $request->input('status');
            $paymentProof->notes = $request->input('notes');
            $paymentProof->verified_by = $user->id;
            $paymentProof->verified_at = now();
            $paymentProof->save();

            if ($paymentProof->status === 'verified') {
                $order->payment_status = 'Completed';
                $order->save();
            }
        });

        return response()->json([
            'message' => 'Payment proof updated.',
            'data' => new PaymentProofResource($paymentProof->fresh()),
        ]);
    }

    private function findOrderOrFail(string $orderNumber): Order
    {
        return Order::where('order_number', $orderNumber)->firstOrFail();
    }

    private function authorizeOrderAccess(Order $order, $user, bool $allowAdmin = false): void
    {
        if ($allowAdmin && $this->isAdmin($user)) {
            return;
        }

        $isOwner = $order->user_id === $user?->id;

        $isVendor = $order->vendororders()
            ->where('user_id', $user?->id)
            ->exists();

        if (!$isOwner && !$isVendor) {
            abort(403, 'You do not have access to this payment proof.');
        }
    }

    private function ensureAdmin($user): void
    {
        if (!$this->isAdmin($user)) {
            abort(403, 'Administrator permissions required.');
        }
    }

    private function isAdmin($user): bool
    {
        if (!$user) {
            return false;
        }

        return $user->roles()
            ->whereRaw('LOWER(name) = ?', ['admin'])
            ->exists();
    }
}

