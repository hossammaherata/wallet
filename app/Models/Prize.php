<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Prize Model
 * 
 * Represents a prize created by admin with multiple dates.
 * 
 * @property int $id
 * @property string $name
 * @property array $dates
 * @property int $total_winners
 * @property decimal $total_amount
 * @property int $created_by
 * @property string $status
 * 
 * @property-read User $creator
 * @property-read \Illuminate\Database\Eloquent\Collection|PrizeTicket[] $tickets
 */
class Prize extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'dates',
        'total_winners',
        'total_amount',
        'created_by',
        'status',
    ];

    protected $casts = [
        'dates' => 'array',
        'total_amount' => 'decimal:2',
    ];

    /**
     * Get the admin user who created this prize.
     * 
     * @return BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all tickets for this prize.
     * 
     * @return HasMany
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(PrizeTicket::class);
    }
}
