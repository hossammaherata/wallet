<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Notification Model
 * 
 * Represents a push notification sent to a user.
 * Notifications are stored for history and to track read/unread status.
 * 
 * @property int $id
 * @property int $user_id
 * @property string $type Notification type (transaction_received, transaction_sent, etc.)
 * @property string $title Notification title in Arabic
 * @property string $body Notification body in Arabic
 * @property array|null $data Additional data (transaction_id, amount, etc.)
 * @property bool $read Read status
 * @property \Illuminate\Support\Carbon|null $read_at When notification was read
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * 
 * @property-read \App\Models\User $user
 * 
 * @package App\Models
 */
class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'body',
        'data',
        'read',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'read' => 'boolean',
        'read_at' => 'datetime',
    ];

    /**
     * Get the user that owns the notification.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mark notification as read.
     * 
     * @return bool
     */
    public function markAsRead(): bool
    {
        return $this->update([
            'read' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * Scope to filter unread notifications.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUnread($query)
    {
        return $query->where('read', false);
    }

    /**
     * Scope to filter read notifications.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRead($query)
    {
        return $query->where('read', true);
    }
}

