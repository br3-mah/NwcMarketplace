<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ConversationResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => (int) $this->id,
            'subject' => $this->subject,
            'sent_user' => (int) $this->sent_user,
            'recieved_user' => (int) $this->recieved_user,
            'message' => $this->message,
            'latest_message' => $this->when(
                $this->relationLoaded('messages') && $this->messages->isNotEmpty(),
                fn () => new ConversationMessageResource($this->messages->first())
            ),
            'messages' => ConversationMessageResource::collection($this->whenLoaded('messages')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
