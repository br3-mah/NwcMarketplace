<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReturnRequest extends Model
{
    protected $table = 'returns';

    protected $fillable = [
        'order_id',
        'user_id',
        'status',
        'reason',
        'notes',
        'items',
        'attachments',
        'resolved_at',
    ];

    protected $casts = [
        'items' => 'array',
        'attachments' => 'array',
        'resolved_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

