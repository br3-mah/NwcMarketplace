<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dispute extends Model
{
    protected $fillable = [
        'order_id',
        'buyer_id',
        'seller_id',
        'status',
        'subject',
        'reason',
        'description',
        'resolution_notes',
        'attachments',
        'closed_at',
    ];

    protected $casts = [
        'attachments' => 'array',
        'closed_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(DisputeMessage::class);
    }
}

