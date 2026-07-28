<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    use HasUuids;

    public $timestamps = true;
    const UPDATED_AT = null;

    protected $fillable = [
        'invoice_id', 'description', 'quantity', 'unit_price',
        'discount_amount', 'total_amount', 'type',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}