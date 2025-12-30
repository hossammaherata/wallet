<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * WalletTransaction Model
 * 
 * Represents a transaction between two wallets (payment or transfer).
 * Uses SoftDeletes to maintain audit trail even if transaction is deleted.
 * Automatically generates UUID reference_id on creation.
 * 
 * @property int $id
 * @property int|null $from_wallet_id Sender wallet ID
 * @property int|null $to_wallet_id Receiver wallet ID
 * @property float $amount Transaction amount
 * @property string $type Transaction type: 'purchase', 'transfer', 'credit', 'debit', 'refund', 'withdrawal'
 * @property string $status Transaction status: 'pending', 'success', 'failed'
 * @property string $reference_id Unique UUID reference for tracking
 * @property array|null $meta Additional metadata (JSON)
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * 
 * @property-read \App\Models\Wallet|null $fromWallet
 * @property-read \App\Models\Wallet|null $toWallet
 * 
 * @method static \Illuminate\Database\Eloquent\Builder successful()
 * @method static \Illuminate\Database\Eloquent\Builder pending()
 * @method static \Illuminate\Database\Eloquent\Builder failed()
 */
class WalletTransaction extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'from_wallet_id',
        'to_wallet_id',
        'amount',
        'type',
        'status',
        'reference_id',
        'meta',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'meta' => 'array',
    ];

    /**
     * Boot the model.
     * Automatically generates UUID reference_id when creating a new transaction.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            if (empty($transaction->reference_id)) {
                $transaction->reference_id = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the sender wallet.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function fromWallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'from_wallet_id');
    }

    /**
     * Get the receiver wallet.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function toWallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'to_wallet_id');
    }

    /**
     * Scope to filter successful transactions.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * Scope to filter pending transactions.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to filter failed transactions.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }
}

