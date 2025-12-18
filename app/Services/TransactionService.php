<?php

namespace App\Services;

use App\Models\WalletTransaction;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

/**
 * TransactionService
 * 
 * Handles all transaction-related operations including:
 * - Creating new transactions with unique reference IDs
 * - Marking transactions as successful or failed
 * - Generating UUID reference IDs for tracking
 * - Finding transactions by reference ID
 * 
 * Transactions are never deleted (use SoftDeletes) to maintain audit trail.
 * 
 * @package App\Services
 */
class TransactionService
{
    /**
     * Create a new transaction.
     *
     * @param array $data
     * @return WalletTransaction
     */
    public function create(array $data): WalletTransaction
    {
        return WalletTransaction::create([
            'from_wallet_id' => $data['from_wallet_id'] ?? null,
            'to_wallet_id' => $data['to_wallet_id'] ?? null,
            'amount' => $data['amount'],
            'type' => $data['type'],
            'status' => $data['status'] ?? 'pending',
            'reference_id' => $data['reference_id'] ?? (string) Str::uuid(),
            'meta' => $data['meta'] ?? null,
        ]);
    }

    /**
     * Mark transaction as successful.
     *
     * @param WalletTransaction $transaction
     * @return bool
     */
    public function markAsSuccess(WalletTransaction $transaction): bool
    {
        return $transaction->update(['status' => 'success']);
    }

    /**
     * Mark transaction as failed.
     *
     * @param WalletTransaction $transaction
     * @param string|null $reason
     * @return bool
     */
    public function markAsFailed(WalletTransaction $transaction, ?string $reason = null): bool
    {
        $meta = $transaction->meta ?? [];
        if ($reason) {
            $meta['failure_reason'] = $reason;
        }

        return $transaction->update([
            'status' => 'failed',
            'meta' => $meta,
        ]);
    }

    /**
     * Generate a unique reference ID.
     *
     * @return string
     */
    public function generateReferenceId(): string
    {
        return (string) Str::uuid();
    }

    /**
     * Get transaction by reference ID.
     *
     * @param string $referenceId
     * @return WalletTransaction|null
     */
    public function findByReferenceId(string $referenceId): ?WalletTransaction
    {
        return WalletTransaction::where('reference_id', $referenceId)->first();
    }
}

