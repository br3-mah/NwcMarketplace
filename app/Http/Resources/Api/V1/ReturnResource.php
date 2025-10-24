<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class ReturnResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => (int) $this->id,
            'order_id' => $this->order_id,
            'user_id' => $this->user_id,
            'status' => $this->status,
            'reason' => $this->reason,
            'notes' => $this->notes,
            'items' => $this->items,
            'attachments' => $this->attachments,
            'resolved_at' => $this->resolved_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

