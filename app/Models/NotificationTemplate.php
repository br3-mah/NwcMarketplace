<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationTemplate extends Model
{
    protected $fillable = [
        'name',
        'code',
        'channel',
        'subject',
        'body',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];
}

