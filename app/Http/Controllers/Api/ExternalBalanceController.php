<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateBalanceRequest;
use App\Models\ExternalBalanceUpdate;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\NotificationService;
use App\Services\TransactionService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * ExternalBalanceController
 * 
 * Handles external API requests to update user balances.
 * Requires API authentication via X-API-Token and X-API-Secret headers.
 * Uses reference_id to prevent duplicate processing.
 * 
 * @package App\Http\Controllers\Api
 */
class ExternalBalanceController extends Controller
{
    use ApiResponse;

    /**
     * TransactionService instance for creating transactions.
     * 
     * @var TransactionService
     */
    protected TransactionService $transactionService;

    /**
     * NotificationService instance for sending notifications.
     * 
     * @var NotificationService
     */
    protected NotificationService $notificationService;

    /**
     * Create a new ExternalBalanceController instance.
     * 
     * @param TransactionService $transactionService
     * @param NotificationService $notificationService
     */
    public function __construct(TransactionService $transactionService, NotificationService $notificationService)
    {
        $this->transactionService = $transactionService;
        $this->notificationService = $notificationService;
    }

    /**
     * Update user balances from external system (accumulative).
     * 
     * This API adds the specified amounts to the current balance (accumulative).
     * Example: If user has 100 and you send 100, the new balance will be 200.
     * 
     * Expected request format:
     * {
     *   "reference_id": "unique-reference-id",
     *   "balances": {
     *     "146": 100.00,  // Add 100 to user with Wallet System ID 146
     *     "147": -50.00   // Subtract 50 from user with Wallet System ID 147 (negative for debit)
     *   }
     * }
     * 
     * Note: Keys in balances are Wallet System IDs (stored in external_refs->wallet), not Midan user IDs.
     *
     * @param UpdateBalanceRequest $request
     * @return JsonResponse
     */
    public function updateBalances(UpdateBalanceRequest $request): JsonResponse
    {
        // Get reference_id from header first, fallback to body
        $referenceId = $request->header('X-Reference-ID') ?? $request->input('reference_id');
        
        if (!$referenceId) {
            return $this->errorResponse('X-Reference-ID header or reference_id in body is required.', null, 400);
        }
        
        $balances = $request->input('balances');

        // Check if this reference_id has been processed before
        $existingUpdate = ExternalBalanceUpdate::where('reference_id', $referenceId)->first();

        if ($existingUpdate) {
            if ($existingUpdate->status === 'completed') {
                return $this->successResponse([
                    'reference_id' => $referenceId,
                    'message' => 'This reference ID has already been processed',
                    'processed_at' => $existingUpdate->updated_at,
                  
                ], 'تم معالجة هذا المرجع مسبقاً', 200);
            } elseif ($existingUpdate->status === 'processing') {
                return $this->errorResponse('This reference ID is currently being processed', null, 400);
            }
        }

        // Create or update the external balance update record
        $externalUpdate = ExternalBalanceUpdate::updateOrCreate(
            ['reference_id' => $referenceId],
            [
                'balances' => $balances,
                'status' => 'processing',
                'processed_count' => 0,
                'failed_count' => 0,
                'error_message' => null,
            ]
        );

        try {
            DB::beginTransaction();

            $processedCount = 0;
            $failedCount = 0;
            $errors = [];

            // Process each balance update (accumulative)
            // Keys in balances are Wallet System IDs (stored directly in external_refs as string)
            foreach ($balances as $walletUserId => $amountToAdd) {
                try {
                    // Convert to string for consistent comparison (Wallet System ID is stored as string)
                    $walletUserIdStr = (string)$walletUserId;
                    
                    // Find user by external_refs (Wallet System ID stored as string directly)
                    $user = User::where('external_refs', $walletUserIdStr)->first();

                    if (!$user) {
                        $failedCount++;
                        $errors[] = "User with Wallet System ID {$walletUserIdStr} not found";
                        continue;
                    }

                    // Ensure only 'user' or 'store' types can have their balance updated externally
                    if ($user->isAdmin() || $user->isStore()) {
                        // $failedCount++;
                        // $errors[] = "Cannot update balance for admin user with Wallet System ID: {$walletUserIdStr}";
                        continue;
                    }

                    // Get or create wallet for the user
                    $wallet = $user->wallet;
                    if (!$wallet) {
                        $wallet = Wallet::create([
                            'user_id' => $user->id,
                            'balance' => 0,
                            'status' => 'active',
                        ]);
                    }

                    // Calculate current balance from transactions
                    $currentBalance = $wallet->calculateBalanceFromTransactions();
                    $amountToAdd = (float) $amountToAdd;

                    // Only create transaction if amount is not zero
                    if (abs($amountToAdd) > 0.01) { // Use 0.01 as threshold for floating point comparison
                        $transactionType = $amountToAdd > 0 ? 'credit' : 'debit';
                        $transactionAmount = abs($amountToAdd);
                        $newBalance = $currentBalance + $amountToAdd;

                        // Check if balance would go negative (for debit transactions)
                        if ($newBalance < 0) {
                            $failedCount++;
                            $errors[] = "Insufficient balance for user with Wallet System ID {$walletUserIdStr} (Midan User ID: {$user->id}). Current: {$currentBalance}, Attempted to subtract: {$transactionAmount}";
                            continue;
                        }

                        // Create transaction
                        $transaction = $this->transactionService->create([
                            'from_wallet_id' => $transactionType === 'debit' ? $wallet->id : null,
                            'to_wallet_id' => $transactionType === 'credit' ? $wallet->id : null,
                            'amount' => $transactionAmount,
                            'type' => $transactionType,
                            'status' => 'success',
                            'reference_id' => $referenceId . '-wallet-' . $walletUserIdStr,
                            'meta' => [
                                'external_update' => true,
                                'external_reference_id' => $referenceId,
                                'previous_balance' => $currentBalance,
                                'amount_added' => $amountToAdd,
                                'new_balance' => $newBalance,
                            ],
                        ]);

                        // Update wallet balance field (for quick access, but balance is calculated from transactions)
                        $wallet->update(['balance' => $newBalance]);

                        // Send notification to user
                        try {
                            if ($transactionType === 'credit') {
                                // Notification for credit (money added)
                                $this->notificationService->send(
                                    $user,
                                    'balance_credit',
                                    'تم إضافة رصيد إلى محفظتك',
                                    "تم إضافة {$transactionAmount} نقطة إلى رصيدك. رصيدك الجديد: {$newBalance} نقطة",
                                    [
                                        'amount' => $transactionAmount,
                                        'previous_balance' => $currentBalance,
                                        'new_balance' => $newBalance,
                                        'transaction_id' => $transaction->id,
                                        'reference_id' => $referenceId,
                                    ]
                                );
                            } else {
                                // Notification for debit (money deducted)
                                $this->notificationService->send(
                                    $user,
                                    'balance_debit',
                                    'تم خصم رصيد من محفظتك',
                                    "تم خصم {$transactionAmount} نقطة من رصيدك. رصيدك الجديد: {$newBalance} نقطة",
                                    [
                                        'amount' => $transactionAmount,
                                        'previous_balance' => $currentBalance,
                                        'new_balance' => $newBalance,
                                        'transaction_id' => $transaction->id,
                                        'reference_id' => $referenceId,
                                    ]
                                );
                            }
                        } catch (\Exception $notifError) {
                            // Log notification error but don't fail the transaction
                            Log::warning("Failed to send notification for balance update", [
                                'user_id' => $user->id,
                                'transaction_id' => $transaction->id,
                                'error' => $notifError->getMessage(),
                            ]);
                        }
                    }

                    $processedCount++;

                } catch (\Exception $e) {
                    $failedCount++;
                    $errors[] = "Failed to update balance for user with Wallet System ID {$walletUserIdStr}: " . $e->getMessage();
                    Log::error("External balance update failed for user with Wallet System ID {$walletUserIdStr}", [
                        'wallet_user_id' => $walletUserIdStr,
                        'midan_user_id' => $user->id ?? null,
                        'reference_id' => $referenceId,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Update the external balance update record
            $externalUpdate->update([
                'status' => $failedCount > 0 && $processedCount === 0 ? 'failed' : 'completed',
                'processed_count' => $processedCount,
                'failed_count' => $failedCount,
                'error_message' => !empty($errors) ? implode('; ', $errors) : null,
            ]);

            DB::commit();

            $response = [
                'reference_id' => $referenceId,
                'processed_count' => $processedCount,
                // 'failed_count' => $failedCount,
                // 'total_count' => count($balances),
            ];

            if (!empty($errors)) {
                $response['errors'] = $errors;
            }

            if ($failedCount > 0 && $processedCount > 0) {
                return $this->successResponse($response, 'تم تحديث بعض الأرصدة بنجاح، ولكن حدثت أخطاء في بعضها', 207);
            } elseif ($failedCount > 0) {
                return $this->errorResponse('فشل تحديث جميع الأرصدة', $response, 400);
            } else {
                return $this->successResponse($response, 'تم تحديث جميع الأرصدة بنجاح', 200);
            }

        } catch (\Exception $e) {
            DB::rollBack();

            $externalUpdate->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            Log::error("External balance update failed", [
                'reference_id' => $referenceId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->errorResponse('حدث خطأ أثناء معالجة الطلب: ' . $e->getMessage(), null, 500);
        }
    }
}

