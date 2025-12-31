<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WalletConfiguration;
use App\Models\WalletTransaction;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * WalletConfigurationController
 * 
 * Handles wallet configuration API endpoints:
 * - Get current configuration
 * - Check if transfer is free for user
 * 
 * @package App\Http\Controllers\Api
 */
class WalletConfigurationController extends Controller
{
    use ApiResponse;

    /**
     * Get current wallet configuration.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getConfiguration(Request $request): JsonResponse
    {
        $configuration = WalletConfiguration::getCurrent();
        
        return $this->successResponse([
            'transfer_fee_percentage' => (float) $configuration->transfer_fee_percentage,
            'withdrawal_fee_percentage' => (float) $configuration->withdrawal_fee_percentage,
        ], 'تم جلب الإعدادات بنجاح');
    }

    /**
     * Check if transfer is free for the authenticated user.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function checkTransferFree(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->hasWallet()) {
            return $this->successResponse([
                'is_free' => true,
                'reason' => 'first_transfer',
                'message' => 'هذه أول عملية تحويل لك، ستكون مجانية',
            ], 'التحويل مجاني');
        }

        // Check if user has made any successful transfers before
        $hasPreviousTransfers = WalletTransaction::where('from_wallet_id', $user->wallet->id)
            ->where('type', 'transfer')
            ->where('status', 'success')
            ->exists();

        // Get configuration
        $configuration = WalletConfiguration::getCurrent();
        $feePercentage = (float) $configuration->transfer_fee_percentage;

        if ($hasPreviousTransfers) {
            // Not first transfer
            if ($feePercentage > 0) {
                return $this->successResponse([
                    'is_free' => false,
                    'reason' => 'has_fee',
                    'fee_percentage' => $feePercentage,
                    'message' => "التحويل غير مجاني. نسبة الرسوم: {$feePercentage}%",
                ], 'التحويل غير مجاني');
            } else {
                return $this->successResponse([
                    'is_free' => true,
                    'reason' => 'no_fee',
                    'fee_percentage' => 0,
                    'message' => 'التحويل مجاني (لا توجد رسوم)',
                ], 'التحويل مجاني');
            }
        } else {
            // First transfer - always free
            return $this->successResponse([
                'is_free' => true,
                'reason' => 'first_transfer',
                'fee_percentage' => $feePercentage,
                'message' => 'هذه أول عملية تحويل لك، ستكون مجانية',
            ], 'التحويل مجاني');
        }
    }
}
