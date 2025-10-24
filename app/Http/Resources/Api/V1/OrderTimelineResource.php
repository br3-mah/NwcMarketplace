<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderTimelineResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => (int) $this->id,
            'order_id' => (int) $this->order_id,
            'title' => $this->title,
            'description' => $this->text,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}

