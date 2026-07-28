<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasUuids;

    protected $fillable = [
        'code', 'discount_type', 'discount_value', 'max_uses',
        'used_count', 'valid_from', 'valid_until', 'plan_ids', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'valid_from' => 'datetime',
            'valid_until' => 'datetime',
            'plan_ids' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function isValidForPlan(string $planId): bool
    {
        if (!$this->is_active) return false;
        if ($this->max_uses && $this->used_count >= $this->max_uses) return false;
        $now = now();
        if ($this->valid_from && $now < $this->valid_from) return false;
        if ($this->valid_until && $now > $this->valid_until) return false;
        if (!empty($this->plan_ids) && !in_array($planId, $this->plan_ids)) return false;
        return true;
    }
}