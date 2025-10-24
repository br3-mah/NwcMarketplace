<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LmsWebhookLog extends Model
{
    protected $fillable = [
        'event_type',
        'payload',
        'headers',
        'processed_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'headers' => 'array',
        'processed_at' => 'datetime',
    ];
}

