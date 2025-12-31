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
        'ugc_prizes',
        'nomination_prizes',
    ];

    protected $casts = [
        'transfer_fee_percentage' => 'decimal:2',
        'withdrawal_fee_percentage' => 'decimal:2',
        'ugc_prizes' => 'array',
        'nomination_prizes' => 'array',
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
            // Create default configuration with zero fees and default prizes
            $config = static::create([
                'transfer_fee_percentage' => 0,
                'withdrawal_fee_percentage' => 0,
                'ugc_prizes' => [0, 0, 0], // Default: 0 points for positions 1, 2, 3
                'nomination_prizes' => [
                    'attendance_fan' => [0, 0, 0, 0, 0], // Default: 0 points for positions 1-5
                    'online_fan' => [0, 0, 0, 0, 0], // Default: 0 points for positions 1-5
                ],
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

    /**
     * Get UGC prize amount for a specific position (1-3).
     * 
     * @param int $position Position (1, 2, or 3)
     * @return float Prize amount
     */
    public function getUgcPrize(int $position): float
    {
        $prizes = $this->ugc_prizes ?? [0, 0, 0];
        $index = $position - 1; // Convert to 0-based index
        
        if ($index < 0 || $index >= count($prizes)) {
            return 0;
        }
        
        return (float) ($prizes[$index] ?? 0);
    }

    /**
     * Get Nomination prize amount for a specific position and category.
     * 
     * @param int $position Position (1-5)
     * @param string $category 'attendance_fan' or 'online_fan'
     * @return float Prize amount
     */
    public function getNominationPrize(int $position, string $category): float
    {
        $nominationPrizes = $this->nomination_prizes ?? [
            'attendance_fan' => [0, 0, 0, 0, 0],
            'online_fan' => [0, 0, 0, 0, 0],
        ];
        
        if (!isset($nominationPrizes[$category])) {
            return 0;
        }
        
        $prizes = $nominationPrizes[$category];
        $index = $position - 1; // Convert to 0-based index
        
        if ($index < 0 || $index >= count($prizes)) {
            return 0;
        }
        
        return (float) ($prizes[$index] ?? 0);
    }
}
