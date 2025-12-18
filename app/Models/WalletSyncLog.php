<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * WalletSyncLog Model
 * 
 * Tracks wallet user synchronization logs.
 * 
 * @property int $id
 * @property \Carbon\Carbon|null $last_sync_at
 * @property string|null $last_sync_date
 * @property int $users_fetched
 * @property int $users_created
 * @property int $users_updated
 * @property int $users_failed
 * @property string $status
 * @property string|null $error_message
 * @property array|null $metadata
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class WalletSyncLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'last_sync_at',
        'last_sync_date',
        'users_fetched',
        'users_created',
        'users_updated',
        'users_failed',
        'status',
        'error_message',
        'metadata',
    ];

    protected $casts = [
        'last_sync_at' => 'datetime',
        'last_sync_date' => 'date',
        'users_fetched' => 'integer',
        'users_created' => 'integer',
        'users_updated' => 'integer',
        'users_failed' => 'integer',
        'metadata' => 'array',
    ];
}
