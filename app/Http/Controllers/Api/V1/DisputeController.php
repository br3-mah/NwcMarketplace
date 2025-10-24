<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\AuthorizesRoles;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Dispute\DisputeIndexRequest;
use App\Http\Requests\Api\V1\Dispute\DisputeMessageStoreRequest;
use App\Http\Requests\Api\V1\Dispute\DisputeStoreRequest;
use App\Http\Requests\Api\V1\Dispute\DisputeUpdateRequest;
use App\Http\Resources\Api\V1\DisputeMessageResource;
use App\Http\Resources\Api\V1\DisputeResource;
use App\Models\Dispute;
use App\Models\DisputeMessage;
use App\Models\Notification;
use App\Models\Order;
use App\Models\VendorOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DisputeController extends Controller
{
    use AuthorizesRoles;

    public function index(DisputeIndexRequest $request): JsonResponse
    {
        $user = $request->user();
        $role = strtolower((string) $request->query('role', 'buyer'));

        $query = Dispute::query()->latest();

        if ($role === 'admin') {
            $this->ensureAdmin($user);
        } elseif ($role === 'seller') {
            $query->where('seller_id', $user->id);
        } else {
            $query->where('buyer_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('order_id')) {
            $query->where('order_id', $request->input('order_id'));
        }

        if ($request->filled('seller_id')) {
            $query->where('seller_id', $request->input('seller_id'));
        }

        $perPage = max(1, min(100, (int) $request->input('per_page', 20)));

        $disputes = $query->paginate($perPage);

        return DisputeResource::collection($disputes)->response();
    }

    public function store(DisputeStoreRequest $request): JsonResponse
    {
        $user = $request->user();
        $order = Order::findOrFail($request->input('order_id'));

        if ($order->user_id !== $user->id) {
            abort(403, 'Only order owners can open disputes.');
        }

        $sellerId = $request->input('seller_id');

        if (!$sellerId) {
            $sellerId = VendorOrder::where('order_id', $order->id)->value('user_id');
        }

        $dispute = DB::transaction(function () use ($request, $user, $order, $sellerId) {
            $dispute = Dispute::create([
                'order_id' => $order->id,
                'buyer_id' => $user->id,
                'seller_id' => $sellerId,
                'subject' => $request->input('subject'),
                'reason' => $request->input('reason'),
                'description' => $request->input('description'),
                'attachments' => $request->input('attachments'),
                'status' => 'open',
            ]);

            DisputeMessage::create([
                'dispute_id' => $dispute->id,
                'user_id' => $user->id,
                'message' => $request->input('description', 'Dispute opened.'),
                'attachments' => $request->input('attachments'),
            ]);

            $this->notifyDispute($dispute, 'Dispute opened', $dispute->subject);

            return $dispute;
        });

        return (new DisputeResource($dispute))->response()->setStatusCode(201);
    }

    public function show(Dispute $dispute, \Illuminate\Http\Request $request): JsonResponse
    {
        $this->authorizeDisputeAccess($dispute, $request->user());

        $dispute->load('messages');

        return (new DisputeResource($dispute))->response();
    }

    public function update(DisputeUpdateRequest $request, Dispute $dispute): JsonResponse
    {
        $user = $request->user();
        $this->authorizeDisputeAccess($dispute, $user, allowBuyer: false);

        $dispute->fill($request->validated());

        if ($request->filled('status') && $request->input('status') === 'closed') {
            $dispute->closed_at = now();
        }

        $dispute->save();

        $this->notifyDispute($dispute, 'Dispute updated', $request->input('resolution_notes', 'Status updated.'));

        return (new DisputeResource($dispute->refresh()))->response();
    }

    public function storeMessage(DisputeMessageStoreRequest $request, Dispute $dispute): JsonResponse
    {
        $user = $request->user();
        $this->authorizeDisputeAccess($dispute, $user);

        $message = DisputeMessage::create([
            'dispute_id' => $dispute->id,
            'user_id' => $user->id,
            'message' => $request->input('message'),
            'attachments' => $request->input('attachments'),
        ]);

        $this->notifyDispute($dispute, 'New dispute message', $request->input('message'));

        return (new DisputeMessageResource($message))->response()->setStatusCode(201);
    }

    private function authorizeDisputeAccess(Dispute $dispute, $user, bool $allowBuyer = true): void
    {
        if ($allowBuyer && $dispute->buyer_id === $user->id) {
            return;
        }

        if ($dispute->seller_id && $dispute->seller_id === $user->id) {
            return;
        }

        $this->ensureAdmin($user);
    }

    private function notifyDispute(Dispute $dispute, string $title, string $message): void
    {
        $payload = [
            'title' => $title,
            'message' => $message,
            'data' => [
                'dispute_id' => $dispute->id,
                'order_id' => $dispute->order_id,
                'status' => $dispute->status,
            ],
        ];

        if ($dispute->buyer_id) {
            Notification::create([
                'order_id' => $dispute->order_id,
                'user_id' => $dispute->buyer_id,
                'data' => $payload,
            ]);
        }

        if ($dispute->seller_id) {
            Notification::create([
                'order_id' => $dispute->order_id,
                'vendor_id' => $dispute->seller_id,
                'data' => $payload,
            ]);
        }
    }
}
