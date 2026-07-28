<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class SubscriptionLineItem extends Model
{
    use HasUuids;

    protected $fillable = [
        'subscription_id', 'type', 'previous_plan_id', 'new_plan_id',
        'previous_price', 'new_price', 'prorated_amount', 'discount_amount',
        'total_amount', 'start_date', 'end_date',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }
}