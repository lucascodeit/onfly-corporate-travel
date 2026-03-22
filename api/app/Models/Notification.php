<?php

namespace App\Models;

use Database\Factories\NotificationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    /** @use HasFactory<NotificationFactory> */
    use HasFactory;

    protected $fillable = [
        'user_to_id',
        'user_from_id',
        'request_id',
        'notification_type',
        'message',
        'is_read',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'read_at' => 'datetime',
        ];
    }

    public function userTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_to_id');
    }

    public function userFrom(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_from_id');
    }

    public function travelRequest(): BelongsTo
    {
        return $this->belongsTo(TravelRequest::class, 'request_id');
    }
}
