<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * PrizeWinner Model
 * 
 * Represents a winner added to a prize ticket.
 * 
 * @property int $id
 * @property int $prize_ticket_id
 * @property int|null $user_id
 * @property string $phone
 * @property decimal $prize_amount
 * @property int|null $transaction_id
 * @property string $status
 * @property string|null $error_message
 * @property int|null $added_by
 * 
 * @property-read PrizeTicket $prizeTicket
 * @property-read User|null $user
 * @property-read User|null $addedBy
 * @property-read WalletTransaction|null $transaction
 */
class PrizeWinner extends Model
{
    use HasFactory;

    protected $fillable = [
        'prize_ticket_id',
        'user_id',
        'phone',
        'prize_amount',
        'transaction_id',
        'status',
        'error_message',
        'added_by',
    ];

    protected $casts = [
        'prize_amount' => 'decimal:2',
    ];

    /**
     * Get the prize ticket this winner belongs to.
     * 
     * @return BelongsTo
     */
    public function prizeTicket(): BelongsTo
    {
        return $this->belongsTo(PrizeTicket::class);
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

    /**
     * Get the user (manager) who added this winner.
     * 
     * @return BelongsTo
     */
    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}
