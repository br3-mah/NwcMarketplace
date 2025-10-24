<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => (int) $this->id,
            'order_id' => $this->order_id,
            'user_id' => $this->user_id,
            'vendor_id' => $this->vendor_id,
            'product_id' => $this->product_id,
            'conversation_id' => $this->conversation_id,
            'is_read' => (bool) $this->is_read,
            'data' => $this->data,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

