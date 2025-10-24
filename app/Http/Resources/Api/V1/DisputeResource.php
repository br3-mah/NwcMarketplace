<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class DisputeResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => (int) $this->id,
            'order_id' => $this->order_id,
            'buyer_id' => $this->buyer_id,
            'seller_id' => $this->seller_id,
            'status' => $this->status,
            'subject' => $this->subject,
            'reason' => $this->reason,
            'description' => $this->description,
            'resolution_notes' => $this->resolution_notes,
            'attachments' => $this->attachments,
            'closed_at' => $this->closed_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

