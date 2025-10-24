<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentProof extends Model
{
    protected $fillable = [
        'order_id',
        'user_id',
        'reference',
        'status',
        'payload',
        'attachments',
        'notes',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'attachments' => 'array',
        'verified_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}

