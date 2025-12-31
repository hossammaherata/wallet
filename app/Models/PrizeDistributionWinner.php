<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * PrizeDistributionWinner Model
 * 
 * Stores individual winner details for each prize distribution.
 * 
 * @property int $id
 * @property int $prize_distribution_id
 * @property int $user_id Wallet system user ID
 * @property int $midan_user_id Midan user ID
 * @property int $position Winner position (1-5)
 * @property string $category attendance_fan, online_fan, or ugc_creator
 * @property float $prize_amount Prize amount awarded
 * @property int $points_scored Points scored by the winner
 * @property string|null $email Winner email
 * @property string|null $phone Winner phone
 * @property int|null $transaction_id Wallet transaction ID
 * @property string $status success or failed
 * @property string|null $error_message Error message if failed
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * 
 * @property-read PrizeDistribution $prizeDistribution
 * @property-read User $user
 * @property-read WalletTransaction|null $transaction
 */
class PrizeDistributionWinner extends Model
{
    use HasFactory;

    protected $fillable = [
        'prize_distribution_id',
        'user_id',
        'midan_user_id',
        'position',
        'category',
        'prize_amount',
        'points_scored',
        'email',
        'phone',
        'transaction_id',
        'status',
        'error_message',
    ];

    protected $casts = [
        'prize_amount' => 'decimal:2',
        'points_scored' => 'integer',
    ];

    /**
     * Get the prize distribution this winner belongs to.
     * 
     * @return BelongsTo
     */
    public function prizeDistribution(): BelongsTo
    {
        return $this->belongsTo(PrizeDistribution::class);
    }

    /**
     * Get the user who won the prize.
     * 
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the transaction created for this prize.
     * 
     * @return BelongsTo
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(WalletTransaction::class);
    }
}
