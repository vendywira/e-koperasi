<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class DemoRequest extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'role',
        'cooperative_name',
        'member_count',
        'whatsapp',
        'message',
    ];

    public function getIncrementing(): bool
    {
        return false;
    }

    public function getKeyType(): string
    {
        return 'string';
    }
}
