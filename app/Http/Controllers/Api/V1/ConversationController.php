<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Conversation\ConversationIndexRequest;
use App\Http\Requests\Api\V1\Conversation\ConversationStoreRequest;
use App\Http\Resources\ConversationResource;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ConversationController extends Controller
{
    public function index(ConversationIndexRequest $request): JsonResponse
    {
        $user = $request->user();

        $query = Conversation::query()
            ->with(['messages' => function ($relation) {
                $relation->latest()->limit(1);
            }])
            ->where(function ($builder) use ($user) {
                $builder->where('sent_user', $user->id)
                    ->orWhere('recieved_user', $user->id);
            });

        if ($request->filled('user_id')) {
            $otherUserId = (int) $request->input('user_id');

            $query->where(function ($builder) use ($user, $otherUserId) {
                $builder->where(function ($inner) use ($user, $otherUserId) {
                    $inner->where('sent_user', $user->id)
                        ->where('recieved_user', $otherUserId);
                })->orWhere(function ($inner) use ($user, $otherUserId) {
                    $inner->where('sent_user', $otherUserId)
                        ->where('recieved_user', $user->id);
                });
            });
        }

        if ($request->filled('order_id')) {
            $orderId = $request->input('order_id');
            $query->where('subject', 'like', '%' . $orderId . '%');
        }

        $perPage = (int) ($request->input('per_page') ?? 15);
        $perPage = $perPage > 0 ? min($perPage, 100) : 15;

        $conversations = $query
            ->orderByDesc('updated_at')
            ->paginate($perPage);

        return ConversationResource::collection($conversations)->response();
    }

    public function store(ConversationStoreRequest $request): JsonResponse
    {
        $user = $request->user();
        $recipient = User::findOrFail($request->input('recipient_id'));

        if ($recipient->id === $user->id) {
            return response()->json([
                'message' => 'You cannot start a conversation with yourself.',
            ], 422);
        }

        $subject = trim((string) $request->input('subject'));
        $subject = $subject !== '' ? $subject : 'Conversation';

        $messageBody = trim((string) $request->input('message'));

        $conversation = DB::transaction(function () use ($user, $recipient, $subject, $messageBody) {
            $existing = Conversation::where('subject', $subject)
                ->where(function ($builder) use ($user, $recipient) {
                    $builder->where(function ($inner) use ($user, $recipient) {
                        $inner->where('sent_user', $user->id)
                            ->where('recieved_user', $recipient->id);
                    })->orWhere(function ($inner) use ($user, $recipient) {
                        $inner->where('sent_user', $recipient->id)
                            ->where('recieved_user', $user->id);
                    });
                })
                ->first();

            if ($existing) {
                $conversation = $existing;
            } else {
                $conversation = new Conversation();
                $conversation->subject = $subject;
                $conversation->sent_user = $user->id;
                $conversation->recieved_user = $recipient->id;
            }

            $conversation->message = $messageBody;
            $conversation->save();

            $message = new Message();
            $message->conversation_id = $conversation->id;
            $message->message = $messageBody;
            $message->sent_user = $user->id;
            $message->recieved_user = $recipient->id;
            $message->save();

            return $conversation;
        });

        $conversation->load(['messages' => function ($relation) {
            $relation->latest()->limit(1);
        }]);

        return (new ConversationResource($conversation))
            ->response()
            ->setStatusCode(201);
    }
}

