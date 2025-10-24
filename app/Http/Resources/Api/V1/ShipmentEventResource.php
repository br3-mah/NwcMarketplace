<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class ShipmentEventResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => (int) $this->id,
            'shipment_id' => $this->shipment_id,
            'event_code' => $this->event_code,
            'status' => $this->status,
            'description' => $this->description,
            'location' => $this->location,
            'occurred_at' => $this->occurred_at?->toIso8601String(),
            'metadata' => $this->metadata,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}

