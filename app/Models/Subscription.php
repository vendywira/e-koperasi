<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Subscription extends Model
{
    protected $fillable = [
        'user_id',
        'tenant_id',
        'type',
        'plan',
        'max_resorts',
        'price_per_resort',
        'status',
        'started_at',
        'trial_ends_at',
        'ends_at',
        'renewed_at',
        'plan_id',
        'billing_cycle',
        'grace_period_days',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'trial_ends_at' => 'datetime',
            'ends_at' => 'datetime',
            'renewed_at' => 'datetime',
            'price_per_resort' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function paymentTransactions(): HasManyThrough
    {
        return $this->hasManyThrough(
            PaymentTransaction::class,
            Invoice::class,
            'user_id',
            'invoice_id',
            'user_id',
            'id'
        );
    }

    // Alias for backward compatibility (e.g., TenantController stats query)
    public function payments(): HasManyThrough
    {
        return $this->paymentTransactions();
    }

    public const STATUS_ACTIVE = 'active';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_TRIALING = 'trialing';

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE && ($this->ends_at === null || $this->ends_at->isFuture());
    }

    public function isGrace(): bool
    {
        return $this->status === self::STATUS_EXPIRED
            && $this->ends_at !== null
            && $this->graceEndsAt()->isFuture();
    }

    public function graceDaysRemaining(): int
    {
        if (!$this->ends_at) return 0;
        return (int) max(0, now()->diffInDays($this->graceEndsAt(), false));
    }

    public function graceEndsAt(): \Carbon\CarbonInterface
    {
        $graceDays = (int) ($this->grace_period_days ?? 7);
        return $this->ends_at->copy()->addDays($graceDays);
    }

    public function daysRemaining(): int
    {
        if (!$this->ends_at) return 365;
        return max(0, now()->diffInDays($this->ends_at, false));
    }

    public function usagePercent(): int
    {
        if (!$this->ends_at || !$this->started_at) return 0;
        $total = $this->started_at->diffInDays($this->ends_at);
        if ($total <= 0) return 100;
        $elapsed = $this->started_at->diffInDays(now());
        return min(100, max(0, (int) round(($elapsed / $total) * 100)));
    }

    public function getDisplayNameAttribute(): string
    {
        $cycleName = \App\Models\BillingCycle::where('slug', $this->billing_cycle)->value('name') ?? 'Bulanan';
        return ucfirst($this->plan ?? 'Business') . ' - ' . $cycleName;
    }
}