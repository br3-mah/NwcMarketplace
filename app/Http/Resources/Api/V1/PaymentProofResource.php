<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class PaymentProofResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => (int) $this->id,
            'order_id' => (int) $this->order_id,
            'order_number' => $this->order?->order_number,
            'reference' => $this->reference,
            'status' => $this->status,
            'payload' => $this->payload,
            'attachments' => $this->attachments,
            'notes' => $this->notes,
            'verified_by' => $this->verified_by,
            'verified_at' => $this->verified_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

