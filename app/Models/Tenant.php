<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Tenant extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'domain',
        'db_name',
        'status',
    ];

    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class, 'tenant_id', 'id');
    }
}
