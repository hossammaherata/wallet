<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * BankAccount Model
 * 
 * Represents a bank account linked to a user for withdrawal requests.
 * 
 * @property int $id
 * @property int $user_id
 * @property string $bank_name
 * @property string $account_number
 * @property string $account_holder_name
 * @property string|null $iban
 * @property string|null $branch_name
 * @property string $status 'active' or 'inactive'
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * 
 * @property-read \App\Models\User $user
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\WithdrawalRequest[] $withdrawalRequests
 */
class BankAccount extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'bank_name',
        'account_number',
        'account_holder_name',
        'iban',
        'branch_name',
        'status',
    ];

    /**
     * Get the user that owns the bank account.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all withdrawal requests for this bank account.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function withdrawalRequests(): HasMany
    {
        return $this->hasMany(WithdrawalRequest::class);
    }

    /**
     * Check if bank account is active.
     * 
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
