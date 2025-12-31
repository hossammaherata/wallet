<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\PrizeDistributionRequest;
use App\Models\PrizeDistribution;
use App\Models\PrizeDistributionWinner;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletConfiguration;
use App\Services\NotificationService;
use App\Services\TransactionService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * PrizeDistributionController
 * 
 * Handles prize distribution from Midan system when events end.
 * Calculates prize amounts dynamically based on configuration.
 * 
 * @package App\Http\Controllers\Api
 */
class PrizeDistributionController extends Controller
{
    use ApiResponse;

    protected TransactionService $transactionService;
    protected NotificationService $notificationService;

    public function __construct(TransactionService $transactionService, NotificationService $notificationService)
    {
        $this->transactionService = $transactionService;
        $this->notificationService = $notificationService;
    }

    /**
     * Distribute prizes to winners based on event results.
     * 
     * @param PrizeDistributionRequest $request
     * @return JsonResponse
     */
    public function distributePrizes(PrizeDistributionRequest $request): JsonResponse
    {
        $eventId = $request->input('event_id');
        $eventType = $request->input('event_type');
        $eventSubtype = $request->input('event_subtype');
        $eventMeta = $request->input('event_meta');
        $winners = $request->input('winners', []);

        // Check if this event_id has been processed before
        $existingDistribution = PrizeDistribution::where('event_id', $eventId)->first();

        if ($existingDistribution) {
            if ($existingDistribution->status === 'completed') {
                // Return the previous result
                $previousWinners = $existingDistribution->winners()
                    ->with('user', 'transaction')
                    ->get()
                    ->map(function ($winner) {
                        return [
                            'user_id' => $winner->user_id,
                            'midan_user_id' => $winner->midan_user_id,
                            'position' => $winner->position,
                            'category' => $winner->category,
                            'prize_amount' => $winner->prize_amount,
                            'status' => $winner->status,
                        ];
                    });

                return $this->successResponse([
                    'event_id' => $eventId,
                    'message' => 'This event has already been processed',
                    'processed_at' => $existingDistribution->updated_at,
                    'processed_count' => $existingDistribution->processed_count,
                    'failed_count' => $existingDistribution->failed_count,
                    'winners' => $previousWinners,
                ], 'تم معالجة هذا الحدث مسبقاً', 200);
            } elseif ($existingDistribution->status === 'processing') {
                return $this->errorResponse('This event is currently being processed', null, 400);
            }
        }

        // Get wallet configuration for prize amounts
        $config = WalletConfiguration::getCurrent();

        // Generate reference ID for this prize distribution
        // Using shorter format to avoid issues: prize-{type}-{eventId}-{timestamp}
        $referenceId = "prize-{$eventType}-{$eventId}-" . time();

        try {
            DB::beginTransaction();

            // Create prize distribution record
            $prizeDistribution = PrizeDistribution::create([
                'event_id' => $eventId,
                'event_type' => $eventType,
                'event_subtype' => $eventSubtype,
                'event_meta' => $eventMeta,
                'reference_id' => $referenceId,
                'status' => 'processing',
                'processed_count' => 0,
                'failed_count' => 0,
            ]);

            $processedCount = 0;
            $failedCount = 0;
            $errors = [];
            $processedWinners = [];

            foreach ($winners as $index => $winner) {
                try {
                    $position = (int) $winner['position'];
                    $category = $winner['category'];
                    $midanUserId = (int) $winner['user_id'];
                    $email = $winner['email'] ?? null;
                    $phone = $winner['phone'] ?? null;
                    $points = $winner['points'] ?? 0;

                    // Find user by email or phone
                    $user = null;
                    if ($email) {
                        $user = User::where('email', $email)->first();
                    }
                    if (!$user && $phone) {
                        $user = User::where('phone', $phone)->first();
                    }

                    if (!$user) {
                        $failedCount++;
                        $errors[] = "Winner #{$index} (Midan User ID: {$midanUserId}): User not found by email or phone";
                        
                        // Save failed winner record
                        PrizeDistributionWinner::create([
                            'prize_distribution_id' => $prizeDistribution->id,
                            'user_id' => 0, // No user found
                            'midan_user_id' => $midanUserId,
                            'position' => $position,
                            'category' => $category,
                            'prize_amount' => 0,
                            'points_scored' => $points,
                            'email' => $email,
                            'phone' => $phone,
                            'status' => 'failed',
                            'error_message' => "User not found by email or phone",
                        ]);
                        
                        Log::warning("Prize distribution: User not found", [
                            'midan_user_id' => $midanUserId,
                            'email' => $email,
                            'phone' => $phone,
                            'event_id' => $eventId,
                        ]);
                        continue;
                    }

                    // Ensure user is not admin or store
                    if ($user->isAdmin() || $user->isStore()) {
                        $failedCount++;
                        $errors[] = "Winner #{$index} (User ID: {$user->id}): Cannot distribute prize to admin or store user";
                        
                        // Save failed winner record
                        PrizeDistributionWinner::create([
                            'prize_distribution_id' => $prizeDistribution->id,
                            'user_id' => $user->id,
                            'midan_user_id' => $midanUserId,
                            'position' => $position,
                            'category' => $category,
                            'prize_amount' => 0,
                            'points_scored' => $points,
                            'email' => $email,
                            'phone' => $phone,
                            'status' => 'failed',
                            'error_message' => "Cannot distribute prize to admin or store user",
                        ]);
                        
                        continue;
                    }

                    // Calculate prize amount based on event type, position, and category
                    $prizeAmount = 0;
                    if ($eventType === 'ugc') {
                        $prizeAmount = $config->getUgcPrize($position);
                    } elseif ($eventType === 'nomination') {
                        $prizeAmount = $config->getNominationPrize($position, $category);
                    }

                    // Skip if prize amount is zero
                    if ($prizeAmount <= 0) {
                        $failedCount++;
                        $errors[] = "Winner #{$index} (User ID: {$user->id}): Prize amount is zero for position {$position}";
                        
                        // Save failed winner record
                        PrizeDistributionWinner::create([
                            'prize_distribution_id' => $prizeDistribution->id,
                            'user_id' => $user->id,
                            'midan_user_id' => $midanUserId,
                            'position' => $position,
                            'category' => $category,
                            'prize_amount' => 0,
                            'points_scored' => $points,
                            'email' => $email,
                            'phone' => $phone,
                            'status' => 'failed',
                            'error_message' => "Prize amount is zero for position {$position}",
                        ]);
                        
                        Log::warning("Prize distribution: Zero prize amount", [
                            'user_id' => $user->id,
                            'event_type' => $eventType,
                            'position' => $position,
                            'category' => $category,
                            'event_id' => $eventId,
                        ]);
                        continue;
                    }

                    // Get or create wallet
                    $wallet = $user->wallet;
                    if (!$wallet) {
                        $wallet = Wallet::create([
                            'user_id' => $user->id,
                            'balance' => 0,
                            'status' => 'active',
                        ]);
                    }

                    // Calculate current balance
                    $currentBalance = $wallet->calculateBalanceFromTransactions();
                    $newBalance = $currentBalance + $prizeAmount;

                    // Create transaction
                    $transaction = $this->transactionService->create([
                        'from_wallet_id' => null,
                        'to_wallet_id' => $wallet->id,
                        'amount' => $prizeAmount,
                        'type' => 'credit',
                        'status' => 'success',
                        'reference_id' => "{$referenceId}-winner-{$index}",
                        'meta' => [
                            'prize_distribution' => true,
                            'event_id' => $eventId,
                            'event_type' => $eventType,
                            'event_subtype' => $eventSubtype,
                            'event_title' => $eventMeta['title'] ?? null,
                            'event_date' => $eventMeta['date'] ?? null,
                            'position' => $position,
                            'category' => $category,
                            'midan_user_id' => $midanUserId,
                            'points_scored' => $points,
                            'previous_balance' => $currentBalance,
                            'new_balance' => $newBalance,
                        ],
                    ]);

                    // Update wallet balance
                    $wallet->update(['balance' => $newBalance]);

                    // Save successful winner record
                    $winnerRecord = PrizeDistributionWinner::create([
                        'prize_distribution_id' => $prizeDistribution->id,
                        'user_id' => $user->id,
                        'midan_user_id' => $midanUserId,
                        'position' => $position,
                        'category' => $category,
                        'prize_amount' => $prizeAmount,
                        'points_scored' => $points,
                        'email' => $email,
                        'phone' => $phone,
                        'transaction_id' => $transaction->id,
                        'status' => 'success',
                    ]);

                    // Send notification
                    try {
                        $eventTitle = $eventMeta['title'] ?? 'الفعالية';
                        $this->notificationService->send(
                            $user,
                            'prize_received',
                            'تهانينا! لقد فزت بجائزة',
                            "تهانينا! لقد فزت بالمركز {$position} في {$eventTitle} وحصلت على {$prizeAmount} نقطة. رصيدك الجديد: {$newBalance} نقطة",
                            [
                                'event_id' => $eventId,
                                'event_type' => $eventType,
                                'event_title' => $eventTitle,
                                'position' => $position,
                                'category' => $category,
                                'prize_amount' => $prizeAmount,
                                'previous_balance' => $currentBalance,
                                'new_balance' => $newBalance,
                                'transaction_id' => $transaction->id,
                            ]
                        );
                    } catch (\Exception $notifError) {
                        // Log notification error but don't fail the transaction
                        Log::warning("Failed to send prize notification", [
                            'user_id' => $user->id,
                            'transaction_id' => $transaction->id,
                            'error' => $notifError->getMessage(),
                        ]);
                    }

                    $processedCount++;
                    $processedWinners[] = [
                        'user_id' => $user->id,
                        'midan_user_id' => $midanUserId,
                        'position' => $position,
                        'category' => $category,
                        'prize_amount' => $prizeAmount,
                    ];

                } catch (\Exception $e) {
                    $failedCount++;
                    $errors[] = "Winner #{$index}: " . $e->getMessage();
                    Log::error("Prize distribution failed for winner", [
                        'index' => $index,
                        'winner' => $winner,
                        'event_id' => $eventId,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            }

            // Update prize distribution status
            $prizeDistribution->update([
                'status' => $failedCount > 0 && $processedCount === 0 ? 'failed' : 'completed',
                'processed_count' => $processedCount,
                'failed_count' => $failedCount,
                'error_message' => !empty($errors) ? implode('; ', array_slice($errors, 0, 5)) : null, // Limit error message length
            ]);

            DB::commit();

            $response = [
                'reference_id' => $referenceId,
                'event_id' => $eventId,
                'event_type' => $eventType,
                'processed_count' => $processedCount,
                'failed_count' => $failedCount,
                'total_count' => count($winners),
                'processed_winners' => $processedWinners,
            ];

            if (!empty($errors)) {
                $response['errors'] = $errors;
            }

            if ($failedCount > 0 && $processedCount > 0) {
                return $this->successResponse($response, 'تم توزيع الجوائز جزئياً', 207);
            } elseif ($failedCount > 0) {
                return $this->errorResponse('فشل توزيع جميع الجوائز', $response, 400);
            } else {
                return $this->successResponse($response, 'تم توزيع جميع الجوائز بنجاح', 200);
            }

        } catch (\Exception $e) {
            DB::rollBack();

            // Update prize distribution status to failed
            if (isset($prizeDistribution)) {
                $prizeDistribution->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                ]);
            }

            Log::error("Prize distribution failed", [
                'event_id' => $eventId,
                'event_type' => $eventType,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->errorResponse('حدث خطأ أثناء توزيع الجوائز: ' . $e->getMessage(), null, 500);
        }
    }
}

