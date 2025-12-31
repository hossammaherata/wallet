<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * WalletConfiguration Model
 * 
 * Represents wallet system configuration including fees for transfers and withdrawals.
 * This is a singleton model - there should only be one active configuration record.
 * 
 * @property int $id
 * @property float $transfer_fee_percentage Percentage fee for friend transfers (0-100)
 * @property float $withdrawal_fee_percentage Percentage fee for bank withdrawals (0-100)
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * 
 * @method static WalletConfiguration|null getCurrent() Get the current active configuration
 */
class WalletConfiguration extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'transfer_fee_percentage',
        'withdrawal_fee_percentage',
    ];

    protected $casts = [
        'transfer_fee_percentage' => 'decimal:2',
        'withdrawal_fee_percentage' => 'decimal:2',
    ];

    /**
     * Get the current active configuration.
     * Creates a default configuration if none exists.
     * 
     * @return WalletConfiguration
     */
    public static function getCurrent(): WalletConfiguration
    {
        $config = static::latest()->first();
        
        if (!$config) {
            // Create default configuration with zero fees
            $config = static::create([
                'transfer_fee_percentage' => 0,
                'withdrawal_fee_percentage' => 0,
            ]);
        }
        
        return $config;
    }

    /**
     * Calculate transfer fee for a given amount.
     * 
     * @param float $amount
     * @return float
     */
    public function calculateTransferFee(float $amount): float
    {
        if ($this->transfer_fee_percentage <= 0) {
            return 0;
        }
        
        return ($amount * $this->transfer_fee_percentage) / 100;
    }

    /**
     * Calculate withdrawal fee for a given amount.
     * 
     * @param float $amount
     * @return float
     */
    public function calculateWithdrawalFee(float $amount): float
    {
        if ($this->withdrawal_fee_percentage <= 0) {
            return 0;
        }
        
        return ($amount * $this->withdrawal_fee_percentage) / 100;
    }

    /**
     * Get net amount after transfer fee.
     * 
     * @param float $amount
     * @return float
     */
    public function getNetAmountAfterTransferFee(float $amount): float
    {
        $fee = $this->calculateTransferFee($amount);
        return $amount - $fee;
    }

    /**
     * Get net amount after withdrawal fee.
     * 
     * @param float $amount
     * @return float
     */
    public function getNetAmountAfterWithdrawalFee(float $amount): float
    {
        $fee = $this->calculateWithdrawalFee($amount);
        return $amount - $fee;
    }
}
