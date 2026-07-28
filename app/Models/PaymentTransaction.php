<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentTransaction extends Model
{
    use HasUuids;

    protected $fillable = [
        'invoice_id', 'duitku_ref', 'amount', 'channel_code',
        'channel_name', 'status', 'paid_at', 'expiry', 'raw_response',
    ];

    protected function casts(): array
    {
        return [
            'paid_at' => 'datetime',
            'expiry' => 'datetime',
            'raw_response' => 'array',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}