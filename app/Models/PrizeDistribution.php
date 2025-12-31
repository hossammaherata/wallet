<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * PrizeDistribution Model
 * 
 * Tracks prize distributions to prevent duplicate processing of event_id.
 * 
 * @property int $id
 * @property int $event_id Midan event ID (unique)
 * @property string $event_type nomination or ugc
 * @property string|null $event_subtype UGC subtype
 * @property array $event_meta Event metadata
 * @property string $reference_id Internal reference ID
 * @property int $processed_count Number of successfully processed winners
 * @property int $failed_count Number of failed winners
 * @property string $status Status: pending, processing, completed, failed
 * @property string|null $error_message Error message if processing failed
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * 
 * @property-read \Illuminate\Database\Eloquent\Collection|PrizeDistributionWinner[] $winners
 */
class PrizeDistribution extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'event_type',
        'event_subtype',
        'event_meta',
        'reference_id',
        'processed_count',
        'failed_count',
        'status',
        'error_message',
    ];

    protected $casts = [
        'event_meta' => 'array',
    ];

    /**
     * Get all winners for this prize distribution.
     * 
     * @return HasMany
     */
    public function winners(): HasMany
    {
        return $this->hasMany(PrizeDistributionWinner::class);
    }
}
