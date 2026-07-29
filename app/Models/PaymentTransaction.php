<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentTransaction extends Model
{
    use HasUuids;

    protected $fillable = [
        'invoice_id', 'duitku_ref', 'amount', 'base_amount', 'fee_amount',
        'channel_code', 'channel_name', 'status', 'paid_at', 'expiry', 'raw_response',
        'payment_type', 'payment_method_name', 'notes', 'payment_proof', 'confirmed_by',
        'receipt_number',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_SUCCESS = 'success';
    public const STATUS_FAILED = 'failed';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_CANCELLED = 'cancelled';

    protected function casts(): array
    {
        return [
            'paid_at' => 'datetime',
            'expiry' => 'datetime',
            'raw_response' => 'array',
            'amount' => 'integer',
            'base_amount' => 'integer',
            'fee_amount' => 'integer',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function confirmor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function isGateway(): bool
    {
        return $this->payment_type === 'gateway';
    }

    public function isManual(): bool
    {
        return $this->payment_type === 'manual';
    }
}
