<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * PrizeTicket Model
 * 
 * Represents a single day ticket for a prize.
 * 
 * @property int $id
 * @property int $prize_id
 * @property string $date
 * @property int $total_winners
 * @property decimal $total_amount
 * @property decimal $remaining_amount
 * @property int $current_winners_count
 * @property string $status
 * 
 * @property-read Prize $prize
 * @property-read \Illuminate\Database\Eloquent\Collection|PrizeWinner[] $winners
 */
class PrizeTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'prize_id',
        'date',
        'total_winners',
        'total_amount',
        'remaining_amount',
        'current_winners_count',
        'status',
    ];

    protected $casts = [
        'date' => 'date',
        'total_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
    ];

    /**
     * Get the prize this ticket belongs to.
     * 
     * @return BelongsTo
     */
    public function prize(): BelongsTo
    {
        return $this->belongsTo(Prize::class);
    }

    /**
     * Get all winners for this ticket.
     * 
     * @return HasMany
     */
    public function winners(): HasMany
    {
        return $this->hasMany(PrizeWinner::class);
    }

    /**
     * Get successful winners count.
     * 
     * @return int
     */
    public function getSuccessfulWinnersCount(): int
    {
        return $this->winners()->where('status', 'success')->count();
    }

    /**
     * Check if ticket can accept more winners.
     * 
     * @return bool
     */
    public function canAcceptMoreWinners(): bool
    {
        return $this->current_winners_count < $this->total_winners 
            && $this->remaining_amount > 0 
            && $this->status === 'active';
    }
}
