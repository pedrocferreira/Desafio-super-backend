<?php

namespace App\Models;

use App\Enums\PixStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pix extends Model
{
    use HasFactory;

    protected $table = 'pix';

    protected $fillable = [
        'user_id',
        'subadquirente',
        'pix_id',
        'transaction_id',
        'amount',
        'status',
        'payer_name',
        'payer_document',
        'description',
        'payment_date',
        'qr_code',
        'qr_code_base64',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'status' => PixStatus::class,
        'payment_date' => 'datetime',
        'metadata' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function webhooks(): HasMany
    {
        return $this->hasMany(Webhook::class);
    }
}
