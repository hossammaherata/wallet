<?php

namespace App\Services;

use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * WalletService
 * 
 * Handles all wallet-related business logic including:
 * - Creating wallets for users/stores
 * - Managing wallet balances
 * - Processing payments from users to stores
 * - Processing transfers between users
 * - Locking/unlocking wallets
 * - Balance verification
 * 
 * All financial operations use database transactions to ensure data integrity.
 * Wallets are locked during transactions to prevent race conditions.
 * 
 * @package App\Services
 */
class WalletService
{
    /**
     * TransactionService instance for creating and managing transactions.
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
     * Create a new WalletService instance.
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
     * Get wallet balance for a user.
     * 
     * Calculates balance from transactions (sum of credits - sum of debits).
     *
     * @param User $user
     * @return float
     */
    public function getBalance(User $user): float
    {
        $wallet = $user->wallet;
        
        if (!$wallet) {
            return 0;
        }

        // Calculate balance from transactions
        return $wallet->calculateBalanceFromTransactions();
    }

    /**
     * Create a wallet for a user.
     *
     * @param User $user
     * @return Wallet
     */
    public function createWallet(User $user): Wallet
    {
        if ($user->wallet) {
            return $user->wallet;
        }

        return Wallet::create([
            'user_id' => $user->id,
            'balance' => 0,
            'status' => 'active',
        ]);
    }

    /**
     * Pay from user wallet to store wallet.
     * 
     * Can accept either:
     * - User $store object
     * - string $storeIdentifier (qr_code or store_id)
     *
     * @param User $user
     * @param User|string|int $store Store object, qr_code (store-{uuid}), or store_id
     * @param float $amount
     * @param array|null $meta
     * @return WalletTransaction
     * @throws \Exception
     */
    public function pay(User $user, User|string|int $store, float $amount, ?array $meta = null): WalletTransaction
    {
        // If $store is not a User object, find the store
        if (!($store instanceof User)) {
            $store = $this->findStoreByIdentifier($store);
        }
        
        return $this->processPayment($user, $store, $amount, $meta);
    }

    /**
     * Find store by identifier (qr_code or store_id).
     *
     * @param string|int $identifier qr_code (store-{uuid}) or store_id
     * @return User
     * @throws \Exception
     */
    protected function findStoreByIdentifier(string|int $identifier): User
    {
        // If it's a numeric string or integer, treat as store_id
        if (is_numeric($identifier)) {
            $store = User::where('type', 'store')->find($identifier);
            if (!$store) {
                throw new \Exception('المتجر المحدد غير موجود');
            }
            return $store;
        }

        // If it starts with "store-", extract UUID
        if (str_starts_with($identifier, 'store-')) {
            $storeCode = str_replace('store-', '', $identifier);
            $store = User::where('type', 'store')
                ->where('store_code', $storeCode)
                ->first();
            
            if (!$store) {
                throw new \Exception('المتجر المحدد غير موجود');
            }
            return $store;
        }

        // Try to find by store_code directly
        $store = User::where('type', 'store')
            ->where('store_code', $identifier)
            ->first();
        
        if ($store) {
            return $store;
        }

        throw new \Exception('المتجر المحدد غير موجود');
    }

    /**
     * Process payment from user wallet to store wallet.
     *
     * @param User $user
     * @param User $store
     * @param float $amount
     * @param array|null $meta
     * @return WalletTransaction
     * @throws \Exception
     */
    protected function processPayment(User $user, User $store, float $amount, ?array $meta = null): WalletTransaction
    {
        if (!$user->isRegularUser()) {
            throw new \Exception('يمكن للمستخدمين العاديين فقط القيام بالدفع');
        }

        if (!$user->isActive()) {
            throw new \Exception('حسابك معطل. يرجى التواصل مع الدعم');
        }

        if (!$store->isStore()) {
            throw new \Exception('المستخدم المحدد ليس متجراً');
        }

        if (!$store->isActive()) {
            throw new \Exception('المتجر المحدد معطل حالياً');
        }

        $userWallet = $user->wallet;
        $storeWallet = $store->wallet;

        if (!$userWallet) {
            $userWallet = $this->createWallet($user);
        }

        if (!$storeWallet) {
            $storeWallet = $this->createWallet($store);
        }

        // Check if user wallet is locked
        if ($userWallet->isLocked()) {
            throw new \Exception('محفظتك موقفة حالياً. يرجى التواصل مع الدعم');
        }

        // Check if store wallet is locked
        if ($storeWallet->isLocked()) {
            throw new \Exception('محفظة المتجر موقفة حالياً. لا يمكن استقبال المدفوعات');
        }

        if ($amount <= 0) {
            throw new \Exception('المبلغ يجب أن يكون أكبر من صفر');
        }

        return DB::transaction(function () use ($userWallet, $storeWallet, $amount, $meta) {
            // Lock wallets to prevent race conditions
            $userWallet = Wallet::lockForUpdate()->find($userWallet->id);
            $storeWallet = Wallet::lockForUpdate()->find($storeWallet->id);

            // Double check if wallets are locked (after locking for update)
            if ($userWallet->isLocked() || $storeWallet->isLocked()) {
                throw new \Exception('واحدة أو أكثر من المحافظ موقفة حالياً');
            }

            // Check if user can perform transaction (rate limiting - 1 minute between transactions)
            $canPerform = $this->canPerformTransaction($userWallet);
            if (!$canPerform['allowed']) {
                throw new \Exception($canPerform['message']);
            }

            // Check balance
            if ($userWallet->balance < $amount) {
                throw new \Exception('رصيد غير كافٍ');
            }

            // Create transaction record
            $transaction = $this->transactionService->create([
                'from_wallet_id' => $userWallet->id,
                'to_wallet_id' => $storeWallet->id,
                'amount' => $amount,
                'type' => 'purchase',
                'status' => 'pending',
                'meta' => $meta,
            ]);

            try {
                // Deduct from user wallet
                $userWallet->decrement('balance', $amount);

                // Credit to store wallet
                $storeWallet->increment('balance', $amount);

                // Mark transaction as successful
                $this->transactionService->markAsSuccess($transaction);

                // Reload relationships for notifications
                $transaction->load(['fromWallet.user', 'toWallet.user']);

                // Send notifications
                $this->notificationService->sendPaymentSent(
                    $transaction->fromWallet->user,
                    $amount,
                    $transaction->toWallet->user->name,
                    $transaction->reference_id
                );

                $this->notificationService->sendPaymentReceived(
                    $transaction->toWallet->user,
                    $amount,
                    $transaction->fromWallet->user->name,
                    $transaction->reference_id
                );

                Log::info('Payment successful', [
                    'transaction_id' => $transaction->id,
                    'reference_id' => $transaction->reference_id,
                    'from_user' => $userWallet->user_id,
                    'to_store' => $storeWallet->user_id,
                    'amount' => $amount,
                ]);

                return $transaction;
            } catch (\Exception $e) {
                $this->transactionService->markAsFailed($transaction, $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Transfer points from one user to another.
     *
     * @param User $fromUser
     * @param User $toUser
     * @param float $amount
     * @param array|null $meta
     * @return WalletTransaction
     * @throws \Exception
     */
    public function transfer(User $fromUser, User $toUser, float $amount, ?array $meta = null): WalletTransaction
    {
        if (!$fromUser->isRegularUser()) {
            throw new \Exception('يمكن للمستخدمين العاديين فقط القيام بالتحويل');
        }

        if (!$fromUser->isActive()) {
            throw new \Exception('حسابك معطل. يرجى التواصل مع الدعم');
        }

        if (!$toUser->isRegularUser()) {
            throw new \Exception('يمكن التحويل فقط للمستخدمين العاديين');
        }

        if (!$toUser->isActive()) {
            throw new \Exception('حساب المستخدم المستقبل معطل');
        }

        if ($fromUser->id === $toUser->id) {
            throw new \Exception('لا يمكن التحويل لنفسك');
        }

        $fromWallet = $fromUser->wallet;
        $toWallet = $toUser->wallet;

        if (!$fromWallet) {
            $fromWallet = $this->createWallet($fromUser);
        }

        if (!$toWallet) {
            $toWallet = $this->createWallet($toUser);
        }

        // Check if sender wallet is locked
        if ($fromWallet->isLocked()) {
            throw new \Exception('محفظتك موقفة حالياً. يرجى التواصل مع الدعم');
        }

        // Check if recipient wallet is locked
        if ($toWallet->isLocked()) {
            throw new \Exception('محفظة المستخدم المستقبل موقفة حالياً. لا يمكن استقبال التحويلات');
        }

        if ($amount <= 0) {
            throw new \Exception('المبلغ يجب أن يكون أكبر من صفر');
        }

        return DB::transaction(function () use ($fromWallet, $toWallet, $amount, $meta) {
            // Lock wallets to prevent race conditions
            $fromWallet = Wallet::lockForUpdate()->find($fromWallet->id);
            $toWallet = Wallet::lockForUpdate()->find($toWallet->id);

            // Double check if wallets are locked (after locking for update)
            if ($fromWallet->isLocked() || $toWallet->isLocked()) {
                throw new \Exception('واحدة أو أكثر من المحافظ موقفة حالياً');
            }

            // Check if user can perform transaction (rate limiting - 1 minute between transactions)
            $canPerform = $this->canPerformTransaction($fromWallet);
            if (!$canPerform['allowed']) {
                throw new \Exception($canPerform['message']);
            }

            // Check balance
            if ($fromWallet->balance < $amount) {
                throw new \Exception('رصيد غير كافٍ');
            }

            // Create transaction record
            $transaction = $this->transactionService->create([
                'from_wallet_id' => $fromWallet->id,
                'to_wallet_id' => $toWallet->id,
                'amount' => $amount,
                'type' => 'transfer',
                'status' => 'pending',
                'meta' => $meta,
            ]);

            try {
                // Deduct from sender wallet
                $fromWallet->decrement('balance', $amount);

                // Credit to receiver wallet
                $toWallet->increment('balance', $amount);

                // Mark transaction as successful
                $this->transactionService->markAsSuccess($transaction);

                // Reload relationships for notifications
                $transaction->load(['fromWallet.user', 'toWallet.user']);

                // Send notifications
                $this->notificationService->sendTransactionSent(
                    $transaction->fromWallet->user,
                    $amount,
                    $transaction->toWallet->user->name,
                    $transaction->reference_id
                );

                $this->notificationService->sendTransactionReceived(
                    $transaction->toWallet->user,
                    $amount,
                    $transaction->fromWallet->user->name,
                    $transaction->reference_id
                );

                Log::info('Transfer successful', [
                    'transaction_id' => $transaction->id,
                    'reference_id' => $transaction->reference_id,
                    'from_user' => $fromWallet->user_id,
                    'to_user' => $toWallet->user_id,
                    'amount' => $amount,
                ]);

                return $transaction;
            } catch (\Exception $e) {
                $this->transactionService->markAsFailed($transaction, $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Lock a wallet.
     *
     * @param Wallet $wallet
     * @return bool
     */
    public function lockWallet(Wallet $wallet): bool
    {
        return $wallet->lock();
    }

    /**
     * Unlock a wallet.
     *
     * @param Wallet $wallet
     * @return bool
     */
    public function unlockWallet(Wallet $wallet): bool
    {
        return $wallet->unlock();
    }

    /**
     * Verify wallet balance is sufficient.
     *
     * @param User $user
     * @param float $amount
     * @return bool
     */
    public function verifyBalance(User $user, float $amount): bool
    {
        $balance = $this->getBalance($user);
        return $balance >= $amount;
    }

    /**
     * Check if user can perform a transaction (transfer or pay).
     * Prevents multiple transactions within the same minute for security.
     *
     * @param Wallet $wallet
     * @return array ['allowed' => bool, 'message' => string|null]
     */
    protected function canPerformTransaction(Wallet $wallet): array
    {
        // Get the last successful transaction (transfer or purchase) from this wallet
        $lastTransaction = WalletTransaction::where('from_wallet_id', $wallet->id)
            ->whereIn('type', ['transfer', 'purchase'])
            ->where('status', 'success')
            ->orderBy('created_at', 'desc')
            ->first();

        // If no previous transaction, allow
        if (!$lastTransaction) {
            return ['allowed' => true, 'message' => null];
        }

        // Check if last transaction was in the same minute
        $lastTransactionMinute = $lastTransaction->created_at->format('Y-m-d H:i');
        $currentMinute = now()->format('Y-m-d H:i');

        if ($lastTransactionMinute === $currentMinute) {
            return [
                'allowed' => true,
                'message' => 'لا يمكنك إجراء أكثر من عملية واحدة في نفس الدقيقة. يرجى المحاولة بعد لحظة.'
            ];
        }

        return ['allowed' => true, 'message' => null];
    }

    /**
     * Record external payment to store (admin only).
     * 
     * This method allows admins to record an external payment made to a store.
     * This payment reduces the store's balance (debt to admin).
     * Creates a debit transaction with type='debit' and to_wallet_id=null.
     *
     * @param User $store
     * @param float $amount
     * @param string|null $note Optional note/description for the payment
     * @return WalletTransaction
     * @throws \Exception
     */
    public function recordExternalPaymentToStore(User $store, float $amount, ?string $note = null): WalletTransaction
    {
        if (!$store->isStore()) {
            throw new \Exception('المستخدم المحدد ليس متجراً');
        }

        if (!$store->isActive()) {
            throw new \Exception('المتجر المحدد معطل حالياً');
        }

        if ($amount <= 0) {
            throw new \Exception('المبلغ يجب أن يكون أكبر من صفر');
        }

        $storeWallet = $store->wallet;

        if (!$storeWallet) {
            throw new \Exception('المتجر لا يملك محفظة');
        }

        // Check if store wallet is locked
        if ($storeWallet->isLocked()) {
            throw new \Exception('محفظة المتجر موقفة حالياً. لا يمكن تسجيل دفعة');
        }

        // Check if store has enough balance
        $currentBalance = $this->getBalance($store);
        if ($currentBalance < $amount) {
            throw new \Exception("رصيد المتجر غير كافٍ. الرصيد الحالي: {$currentBalance} نقطة");
        }

        return DB::transaction(function () use ($storeWallet, $amount, $note) {
            // Lock wallet to prevent race conditions
            $storeWallet = Wallet::lockForUpdate()->find($storeWallet->id);

            // Double check if wallet is locked
            if ($storeWallet->isLocked()) {
                throw new \Exception('محفظة المتجر موقفة حالياً');
            }

            // Re-check balance after locking
            $currentBalance = $this->getBalance($storeWallet->user);
            if ($currentBalance < $amount) {
                throw new \Exception("رصيد المتجر غير كافٍ. الرصيد الحالي: {$currentBalance} نقطة");
            }

            // Create external payment transaction (debit - reduces balance)
            $transaction = $this->transactionService->create([
                'from_wallet_id' => $storeWallet->id, // From store wallet
                'to_wallet_id' => null, // External payment - no destination wallet
                'amount' => $amount,
                'type' => 'debit',
                'status' => 'pending',
                'meta' => $note ? ['note' => $note, 'paid_by' => 'admin', 'payment_type' => 'external'] : ['paid_by' => 'admin', 'payment_type' => 'external'],
            ]);

            try {
                // Debit from store wallet (reduce balance)
                $storeWallet->decrement('balance', $amount);

                // Mark transaction as successful
                $this->transactionService->markAsSuccess($transaction);

                // Reload relationships
                $transaction->load(['fromWallet.user']);

                // Send notification to store
                $this->notificationService->sendExternalPaymentReceived(
                    $storeWallet->user,
                    $amount,
                    $transaction->reference_id,
                    $note
                );

                Log::info('External payment recorded for store', [
                    'transaction_id' => $transaction->id,
                    'reference_id' => $transaction->reference_id,
                    'store_id' => $storeWallet->user_id,
                    'amount' => $amount,
                    'note' => $note,
                ]);

                return $transaction;
            } catch (\Exception $e) {
                $this->transactionService->markAsFailed($transaction, $e->getMessage());
                throw $e;
            }
        });
    }
}

