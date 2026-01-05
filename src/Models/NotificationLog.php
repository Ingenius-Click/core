<?php

namespace Ingenius\Core\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationLog extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'event_class',
        'channel',
        'recipient',
        'recipient_name',
        'status',
        'error_message',
        'event_data',
        'metadata',
        'sent_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'event_data' => 'array',
        'metadata' => 'array',
        'sent_at' => 'datetime',
    ];

    /**
     * Scope to filter by status
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by channel
     */
    public function scopeChannel($query, string $channel)
    {
        return $query->where('channel', $channel);
    }

    /**
     * Scope to filter by event class
     */
    public function scopeEventClass($query, string $eventClass)
    {
        return $query->where('event_class', $eventClass);
    }

    /**
     * Scope to get recent logs
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
