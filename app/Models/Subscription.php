<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    protected $fillable = [
        'user_id',
        'plan',
        'status',
        'started_at',
        'trial_ends_at',
        'ends_at',
        'renewed_at',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'trial_ends_at' => 'datetime',
            'ends_at' => 'datetime',
            'renewed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && ($this->ends_at === null || $this->ends_at->isFuture());
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
}
