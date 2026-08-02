<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use HasUuids;

    protected $fillable = [
        'name', 'description', 'type', 'pricing_config', 'max_resorts', 'price_per_month',
        'trial_days', 'sort_order', 'is_active', 'is_default', 'is_popular',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'is_popular' => 'boolean',
            'pricing_config' => 'array',
        ];
    }

    public function features(): HasMany
    {
        return $this->hasMany(PlanFeature::class);
    }

    public function scopeActive($q)
    {
        $q->where('is_active', true)->orderBy('sort_order');
    }
}