<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * ExternalBalanceUpdate Model
 * 
 * Tracks external balance updates to prevent duplicate processing.
 * 
 * @property int $id
 * @property string $reference_id Unique reference ID from external system
 * @property array $balances Array of user_id => balance pairs
 * @property int $processed_count Number of successfully processed balances
 * @property int $failed_count Number of failed updates
 * @property string $status Status: 'pending', 'processing', 'completed', 'failed'
 * @property string|null $error_message Error message if processing failed
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class ExternalBalanceUpdate extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_id',
        'balances',
        'processed_count',
        'failed_count',
        'status',
        'error_message',
    ];

    protected $casts = [
        'balances' => 'array',
    ];
}

