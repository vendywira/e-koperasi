<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Invoice extends Model
{
    use HasUuids;

    protected $fillable = [
        'tenant_request_id', 'tenant_id', 'user_id', 'requested_by', 'name', 'domain',
        'resort_count', 'price_per_resort', 'months', 'total_amount',
        'invoice_number', 'subtotal', 'discount_amount', 'coupon_id', 'plan_id',
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

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class, 'tenant_id', 'tenant_id');
    }

    public function toResourceData(): array
    {
        $latestTxn = $this->paymentTransactions->sortByDesc('created_at')->first();
        $planName = $this->subscription?->display_name ?? $this->subscription?->plan ?? 'Business';

        return [
            'id' => $this->id,
            'invoice_number' => $this->invoice_number,
            'plan_name' => $planName,
            'tenant_name' => $this->tenant?->name ?? $this->name,
            'name' => $this->name,
            'domain' => $this->domain,
            'resort_count' => (int) $this->resort_count,
            'price_per_resort' => (int) $this->price_per_resort,
            'months' => (int) $this->months,
            'subtotal' => (int) ($this->subtotal ?? $this->total_amount),
            'discount_amount' => (int) ($this->discount_amount ?? 0),
            'total_amount' => (int) $this->total_amount,
            'status' => $this->status,
            'payment_proof' => $this->payment_proof ? asset('storage/' . $this->payment_proof) : null,
            'due_date' => $this->due_date?->format('d M Y'),
            'paid_at' => $this->paid_at?->format('d M Y'),
            'created_at' => $this->created_at->format('d M Y'),
            'payment_method' => $latestTxn?->channel_name,
            'payment_type' => $latestTxn?->payment_type,
            // Admin-only (optional fields)
            'client_name' => $this->user?->name,
            'client_email' => $this->user?->email,
            'confirmed_by' => $this->confirmor?->name,
            'transactions' => $this->paymentTransactions->sortByDesc('created_at')->values()->map(fn($t) => [
                'id' => $t->id,
                'amount' => (int) $t->amount,
                'status' => $t->status,
                'payment_type' => $t->payment_type,
                'channel_name' => $t->channel_name,
                'paid_at' => $t->paid_at?->format('d M Y H:i'),
                'expiry' => $t->expiry?->format('d M Y H:i'),
                'notes' => $t->notes,
            ]),
            'items' => $this->invoiceItems->map(fn($i) => [
                'id' => $i->id,
                'description' => $i->description,
                'quantity' => $i->quantity,
                'unit_price' => (int) $i->unit_price,
                'discount_amount' => (int) $i->discount_amount,
                'total_amount' => (int) $i->total_amount,
            ]),
        ];
    }

    public function scopeForUser($q, $userId)
    {
        return $q->where('user_id', $userId);
    }
}
