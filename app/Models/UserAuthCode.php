<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class UserAuthCode extends Model
{
    protected $fillable = [
        'channel',
        'identifier',
        'code',
        'user_id',
        'payload',
        'expires_at',
        'attempts',
        'max_attempts',
        'is_verified',
    ];

    protected $casts = [
        'payload' => 'array',
        'expires_at' => 'datetime',
        'is_verified' => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('expires_at', '>=', Carbon::now())->where('is_verified', false);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

