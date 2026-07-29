<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasUuids;

    protected $fillable = [
        'tenant_request_id', 'tenant_id', 'user_id', 'requested_by', 'name', 'domain',
        'resort_count', 'price_per_resort', 'months', 'total_amount',
        'invoice_number', 'subtotal', 'discount_amount', 'coupon_id',
        'due_date', 'payment_channel', 'payment_transaction_id',
        'status', 'paid_at', 'payment_proof', 'confirmed_by',
    ];

    protected function casts(): array
    {
        return [
            'paid_at' => 'datetime',
            'due_date' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function confirmor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function paymentTransactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    public function paymentTransaction(): BelongsTo
    {
        return $this->belongsTo(PaymentTransaction::class);
    }
}
