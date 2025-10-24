<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderDetailResource extends JsonResource
{
    public function toArray($request): array
    {
        $cart = [];

        if ($this->cart) {
            $decoded = is_array($this->cart) ? $this->cart : json_decode($this->cart, true);
            if (is_array($decoded)) {
                $cart = $decoded;
            }
        }

        return [
            'id' => (int) $this->id,
            'order_number' => $this->order_number,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'customer' => [
                'name' => $this->customer_name,
                'email' => $this->customer_email,
                'phone' => $this->customer_phone,
                'address' => $this->customer_address,
                'city' => $this->customer_city,
                'state' => $this->customer_state,
                'zip' => $this->customer_zip,
                'country' => $this->customer_country,
            ],
            'shipping' => [
                'name' => $this->shipping_name,
                'email' => $this->shipping_email,
                'phone' => $this->shipping_phone,
                'address' => $this->shipping_address,
                'city' => $this->shipping_city,
                'state' => $this->shipping_state,
                'zip' => $this->shipping_zip,
                'country' => $this->shipping_country,
                'method' => $this->shipping,
                'cost' => (float) ($this->shipping_cost ?? 0),
            ],
            'payment' => [
                'method' => $this->method,
                'amount' => (float) $this->pay_amount,
                'wallet_amount' => (float) ($this->wallet_price ?? 0),
                'transaction_id' => $this->txnid,
            ],
            'totals' => [
                'subtotal' => (float) (($cart['totalPrice'] ?? 0)),
                'total_quantity' => (int) ($cart['totalQty'] ?? 0),
                'currency_sign' => $this->currency_sign,
                'currency_value' => (float) $this->currency_value,
                'tax' => (float) ($this->tax ?? 0),
                'tax_location' => $this->tax_location,
                'coupon_code' => $this->coupon_code,
                'coupon_discount' => (float) ($this->coupon_discount ?? 0),
            ],
            'items' => isset($cart['items']) ? array_values($cart['items']) : [],
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
