<?php

namespace App\Models;

use App\Interfaces\Searchable;
use App\Models\Notification;
use App\Traits\SearchTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

/**
 * User Model
 * 
 * Represents a user in the system. Can be of type: user, store, or admin.
 * Uses SoftDeletes to allow recovery of deleted records.
 * 
 * @property int $id
 * @property string $name
 * @property string|null $email
 * @property string|null $phone
 * @property string $password
 * @property string $type User type: 'user', 'store', or 'admin'
 * @property string $status Account status: 'active' or 'suspended'
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * 
 * @property-read \App\Models\Wallet|null $wallet
 * 
 * @method bool isAdmin()
 * @method bool isStore()
 * @method bool isRegularUser()
 * @method bool isActive()
 * @method bool hasWallet()
 */
class User extends Authenticatable implements Searchable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use SearchTrait;
    use SoftDeletes;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'type',
        'status',
        'store_code',
        'external_refs',
        'profile_photo_path',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        // external_refs is stored as string (Wallet System ID), not array
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    // Searchable fields for SearchTrait
    protected $searchable = ['name',  'email', 'phone'];
    protected $searchableRelations = [];

    public function getSearchableFields(): array
    {
        return $this->searchable ?? [];
    }

    /**
     * Get the wallet associated with the user.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    /**
     * Check if user is an admin.
     * 
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->type === 'admin';
    }

    /**
     * Check if user is a store.
     * 
     * @return bool
     */
    public function isStore(): bool
    {
        return $this->type === 'store';
    }

    /**
     * Check if user is a regular user.
     * 
     * @return bool
     */
    public function isRegularUser(): bool
    {
        return $this->type === 'user';
    }

    /**
     * Check if user account is active.
     * 
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if user has a wallet.
     * 
     * @return bool
     */
    public function hasWallet(): bool
    {
        return $this->wallet !== null;
    }

    /**
     * Get all notifications for the user.
     * 
     * @return HasMany
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get unread notifications count.
     * 
     * @return int
     */
    public function unreadNotificationsCount(): int
    {
        return $this->notifications()->unread()->count();
    }

    /**
     * Get all bank accounts for the user.
     * 
     * @return HasMany
     */
    public function bankAccounts(): HasMany
    {
        return $this->hasMany(\App\Models\BankAccount::class);
    }

    /**
     * Get all withdrawal requests for the user.
     * 
     * @return HasMany
     */
    public function withdrawalRequests(): HasMany
    {
        return $this->hasMany(\App\Models\WithdrawalRequest::class);
    }
}
