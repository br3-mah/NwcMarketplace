<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class ShipmentResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => (int) $this->id,
            'order_id' => $this->order_id,
            'user_id' => $this->user_id,
            'tracking_number' => $this->tracking_number,
            'status' => $this->status,
            'service_code' => $this->service_code,
            'service_name' => $this->service_name,
            'cost' => (float) $this->cost,
            'currency_sign' => $this->currency_sign,
            'metadata' => $this->metadata,
            'shipped_at' => $this->shipped_at?->toIso8601String(),
            'expected_delivery_at' => $this->expected_delivery_at?->toIso8601String(),
            'delivered_at' => $this->delivered_at?->toIso8601String(),
            'canceled_at' => $this->canceled_at?->toIso8601String(),
            'cancellation_reason' => $this->cancellation_reason,
            'pod' => [
                'signed_by' => $this->pod_signed_by,
                'signed_at' => $this->pod_signed_at?->toIso8601String(),
                'attachments' => $this->pod_attachments,
            ],
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

