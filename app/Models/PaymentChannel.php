<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PaymentChannel extends Model
{
    use HasUuids;

    protected $fillable = [
        'code', 'name', 'icon_url', 'type', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'fee_fixed' => 'integer',
            'fee_percent' => 'integer',
        ];
    }

    public function calculateFee(int $baseAmount): int
    {
        return $this->fee_fixed + (int) round($baseAmount * $this->fee_percent / 100);
    }

    public function totalAmount(int $baseAmount): int
    {
        return $baseAmount + $this->calculateFee($baseAmount);
    }

    public function scopeActive($q)
    {
        $q->where('is_active', true)->orderBy('sort_order');
    }
}