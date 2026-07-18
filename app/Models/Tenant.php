<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Tenant extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'domain',
        'db_name',
        'status',
        'notes',
        'requested_by',
    ];

    public function requestor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class, 'tenant_id', 'id');
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class, 'tenant_id', 'id');
    }
}
