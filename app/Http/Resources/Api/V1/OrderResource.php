<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => (int) $this->id,
            'order_number' => $this->order_number,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'total_quantity' => (int) $this->totalQty,
            'pay_amount' => (float) $this->pay_amount,
            'currency_sign' => $this->currency_sign,
            'currency_value' => (float) $this->currency_value,
            'wallet_amount' => (float) ($this->wallet_price ?? 0),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

