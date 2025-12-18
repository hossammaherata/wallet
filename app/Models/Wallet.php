<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Wallet Model
 * 
 * Represents a user's wallet for storing balance and managing transactions.
 * Uses SoftDeletes to preserve transaction history even if wallet is deleted.
 * 
 * @property int $id
 * @property int $user_id
 * @property float $balance Current wallet balance
 * @property string $status Wallet status: 'active' or 'locked'
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * 
 * @property-read \App\Models\User $user
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\WalletTransaction[] $sentTransactions
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\WalletTransaction[] $receivedTransactions
 * 
 * @method bool isLocked()
 * @method bool lock()
 * @method bool unlock()
 */
class Wallet extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'balance',
        'status',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
    ];

    /**
     * Get the user that owns the wallet.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all transactions where this wallet is the sender.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function sentTransactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class, 'from_wallet_id');
    }

    /**
     * Get all transactions where this wallet is the receiver.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function receivedTransactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class, 'to_wallet_id');
    }

    /**
     * Check if wallet is locked.
     * 
     * @return bool
     */
    public function isLocked(): bool
    {
        return $this->status === 'locked';
    }

    /**
     * Lock the wallet to prevent transactions.
     * 
     * @return bool
     */
    public function lock(): bool
    {
        return $this->update(['status' => 'locked']);
    }

    /**
     * Unlock the wallet to allow transactions.
     * 
     * @return bool
     */
    public function unlock(): bool
    {
        return $this->update(['status' => 'active']);
    }

    /**
     * Calculate balance from transactions.
     * 
     * Balance = Sum of all successful credit transactions - Sum of all successful debit transactions
     * 
     * @return float
     */
    public function calculateBalanceFromTransactions(): float
    {
        // Sum of all successful credit transactions (received)
        $credits = $this->receivedTransactions()
            ->where('status', 'success')
            ->sum('amount');

        // Sum of all successful debit transactions (sent)
        $debits = $this->sentTransactions()
            ->where('status', 'success')
            ->sum('amount');

        return (float) ($credits - $debits);
    }

    /**
     * Get all transactions for this wallet (both sent and received).
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function allTransactions()
    {
        $sent = $this->sentTransactions()->get();
        $received = $this->receivedTransactions()->get();
        
        return $sent->merge($received)->sortByDesc('created_at');
    }
}

