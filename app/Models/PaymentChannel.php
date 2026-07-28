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
        ];
    }

    public function scopeActive($q)
    {
        $q->where('is_active', true)->orderBy('sort_order');
    }
}