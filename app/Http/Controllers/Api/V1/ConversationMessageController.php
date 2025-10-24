<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Conversation\ConversationMessageStoreRequest;
use App\Http\Resources\ConversationMessageResource;
use App\Models\Conversation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConversationMessageController extends Controller
{
    public function index(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();

        if (!$this->userIsParticipant($conversation, $user->id)) {
            return response()->json([
                'message' => 'Conversation not found.',
            ], 404);
        }

        $perPage = (int) ($request->query('perPage', $request->query('per_page', 50)));
        $perPage = $perPage > 0 ? min($perPage, 100) : 50;

        $messages = $conversation->messages()
            ->orderBy('created_at')
            ->paginate($perPage);

        return ConversationMessageResource::collection($messages)->response();
    }

    public function store(ConversationMessageStoreRequest $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();

        if (!$this->userIsParticipant($conversation, $user->id)) {
            return response()->json([
                'message' => 'Conversation not found.',
            ], 404);
        }

        $recipientId = $conversation->sent_user === $user->id
            ? (int) $conversation->recieved_user
            : (int) $conversation->sent_user;

        $message = DB::transaction(function () use ($conversation, $request, $user, $recipientId) {
            $created = $conversation->messages()->create([
                'message' => trim((string) $request->input('message')),
                'sent_user' => $user->id,
                'recieved_user' => $recipientId,
            ]);

            $conversation->message = $created->message;
            $conversation->save();

            return $created->fresh();
        });

        return (new ConversationMessageResource($message))
            ->response()
            ->setStatusCode(201);
    }

    private function userIsParticipant(Conversation $conversation, int $userId): bool
    {
        return in_array($userId, [(int) $conversation->sent_user, (int) $conversation->recieved_user], true);
    }
}

