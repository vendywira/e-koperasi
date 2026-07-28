<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class BillingCycle extends Model
{
    use HasUuids;

    protected $fillable = ['name', 'slug', 'months', 'discount_percent', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function scopeActive($q)
    {
        $q->where('is_active', true)->orderBy('sort_order');
    }
}