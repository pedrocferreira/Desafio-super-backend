<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Webhook extends Model
{
    use HasFactory;

    protected $fillable = [
        'pix_id',
        'withdraw_id',
        'subadquirente',
        'event_type',
        'payload',
        'processed',
        'processed_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'processed' => 'boolean',
        'processed_at' => 'datetime',
    ];

    public function pix(): BelongsTo
    {
        return $this->belongsTo(Pix::class);
    }

    public function withdraw(): BelongsTo
    {
        return $this->belongsTo(Withdraw::class);
    }
}
