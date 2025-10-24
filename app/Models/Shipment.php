<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shipment extends Model
{
    protected $fillable = [
        'order_id',
        'user_id',
        'tracking_number',
        'status',
        'service_code',
        'service_name',
        'cost',
        'currency_sign',
        'metadata',
        'shipped_at',
        'expected_delivery_at',
        'delivered_at',
        'canceled_at',
        'cancellation_reason',
        'pod_signed_by',
        'pod_signed_at',
        'pod_attachments',
    ];

    protected $casts = [
        'metadata' => 'array',
        'pod_attachments' => 'array',
        'shipped_at' => 'datetime',
        'expected_delivery_at' => 'datetime',
        'delivered_at' => 'datetime',
        'canceled_at' => 'datetime',
        'pod_signed_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(ShipmentEvent::class);
    }
}

