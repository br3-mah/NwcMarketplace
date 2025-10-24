<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LmsSyncJob extends Model
{
    protected $fillable = [
        'shipment_id',
        'status',
        'payload',
        'response',
        'attempted_at',
        'completed_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'response' => 'array',
        'attempted_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }
}

