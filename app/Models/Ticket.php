<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'ticket_number',
        'user_id',
        'subject',
        'description',
        'status',
        'priority',
        'assigned_to',
    ];

    protected static function booted(): void
    {
        static::creating(function (Ticket $ticket) {
            if (!$ticket->ticket_number) {
                $ticket->ticket_number = static::generateTicketNumber();
            }
        });
    }

    public static function generateTicketNumber(): string
    {
        $prefix = 'TKT-';
        $count = static::withTrashed()->count();
        $num = $count + 1;

        return $prefix . str_pad((string) $num, 4, '0', STR_PAD_LEFT);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(TicketReply::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }
}
