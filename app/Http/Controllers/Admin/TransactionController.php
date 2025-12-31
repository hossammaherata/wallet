<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * TransactionController
 * 
 * Handles all admin operations for viewing transactions including:
 * - Listing all transactions with advanced filtering:
 *   - Keyword search (reference ID, user names)
 *   - Status filter (success, pending, failed)
 *   - Type filter (purchase, transfer, credit, debit, refund)
 *   - Date range filter (from date to date)
 * - Viewing transaction details
 * 
 * Transactions are read-only in admin panel (no delete/edit operations)
 * to maintain complete audit trail.
 * 
 * @package App\Http\Controllers\Admin
 */
class TransactionController extends BaseController
{
    /**
     * Display a listing of transactions.
     * 
     * Supports multiple filters: keyword, status, type, and date range.
     * Results are paginated and include related user information.
     * 
     * @param Request $request
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        $keyword = $request->get('keyword', null);
        $status = $request->get('status');
        $type = $request->get('type');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        $query = WalletTransaction::with(['fromWallet.user', 'toWallet.user']);

        // Search by reference ID or user name
        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('reference_id', 'like', "%{$keyword}%")
                  ->orWhereHas('fromWallet.user', function ($userQuery) use ($keyword) {
                      $userQuery->where('name', 'like', "%{$keyword}%")
                                ->orWhere('phone', 'like', "%{$keyword}%");
                  })
                  ->orWhereHas('toWallet.user', function ($userQuery) use ($keyword) {
                      $userQuery->where('name', 'like', "%{$keyword}%")
                                ->orWhere('phone', 'like', "%{$keyword}%");
                  });
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($type) {
            $query->where('type', $type);
        }

        // Date range filter
        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $transactions = $query->orderBy('created_at', 'desc')
            ->paginate(15);

        // Add display fields for each transaction
        $transactions->getCollection()->transform(function ($transaction) {
            $amount = (float) $transaction->amount;
            $meta = $transaction->meta ?? [];
            
            // Extract fee information - prioritize fee field, fallback to meta
            $fee = (float) ($transaction->fee ?? (isset($meta['fee']) ? (float) $meta['fee'] : 0));
            $feePercentage = isset($meta['fee_percentage']) ? (float) $meta['fee_percentage'] : 0;
            $originalAmount = isset($meta['original_amount']) ? (float) $meta['original_amount'] : ($fee > 0 ? $amount + $fee : $amount);
            $netAmount = isset($meta['net_amount']) ? (float) $meta['net_amount'] : $amount;
            $isFirstTransfer = isset($meta['is_first_transfer']) ? (bool) $meta['is_first_transfer'] : false;
            
            // Determine if this transaction has credit (money in) or debit (money out)
            // In Dashboard, we show both perspectives:
            // - If to_wallet_id exists: this is credit (money in) for the receiver
            // - If from_wallet_id exists: this is debit (money out) for the sender
            
            // For transactions with both from and to (purchase, transfer):
            // We'll show it as debit (red) for the sender and credit (green) for the receiver
            // But in the list, we'll prioritize showing it based on the transaction type
            
            $hasCredit = $transaction->to_wallet_id !== null;
            $hasDebit = $transaction->from_wallet_id !== null;
            
            // For display purposes:
            // - If it's a pure credit (only to_wallet_id): green
            // - If it's a pure debit (only from_wallet_id): red
            // - If it has both (transfer/purchase): show as debit (red) for sender perspective
            $isCredit = $hasCredit && !$hasDebit;
            $isDebit = $hasDebit;
            
            // If both exist, prioritize debit (sender perspective) for display
            if ($hasCredit && $hasDebit) {
                $isDebit = true;
                $isCredit = false;
            }
            
            $amountDirection = $isCredit ? 'in' : ($isDebit ? 'out' : 'neutral');
            $displayAmount = $isCredit ? '+' . number_format($amount, 2) : ($isDebit ? '-' . number_format($amount, 2) : number_format($amount, 2));
            
            $transaction->is_credit = $isCredit;
            $transaction->is_debit = $isDebit;
            $transaction->amount_direction = $amountDirection;
            $transaction->display_amount = $displayAmount;
            $transaction->has_credit = $hasCredit;
            $transaction->has_debit = $hasDebit;
            
            // Add fee information
            $transaction->fee = $fee;
            $transaction->fee_percentage = $feePercentage;
            $transaction->original_amount = $originalAmount;
            $transaction->net_amount = $netAmount;
            $transaction->is_first_transfer = $isFirstTransfer;
            $transaction->has_fee = $fee > 0;
            
            return $transaction;
        });

        return Inertia::render('Admin/transactions/Index', [
            'transactions' => $transactions,
            'filters' => [
                'keyword' => $keyword,
                'status' => $status,
                'type' => $type,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
        ]);
    }

    /**
     * Display the specified transaction.
     */
    public function show(string $id)
    {
        $transaction = WalletTransaction::with([
            'fromWallet.user',
            'toWallet.user'
        ])->findOrFail($id);
        
        // Add fee information for display
        $meta = $transaction->meta ?? [];
        $transaction->fee_display = (float) ($transaction->fee ?? (isset($meta['fee']) ? (float) $meta['fee'] : 0));
        $transaction->fee_percentage_display = isset($meta['fee_percentage']) ? (float) $meta['fee_percentage'] : 0;
        $transaction->original_amount_display = isset($meta['original_amount']) ? (float) $meta['original_amount'] : ($transaction->fee_display > 0 ? $transaction->amount + $transaction->fee_display : $transaction->amount);
        $transaction->net_amount_display = isset($meta['net_amount']) ? (float) $meta['net_amount'] : $transaction->amount;
        $transaction->is_first_transfer_display = isset($meta['is_first_transfer']) ? (bool) $meta['is_first_transfer'] : false;
        $transaction->has_fee_display = $transaction->fee_display > 0;
        
        return Inertia::render('Admin/transactions/Show', [
            'transaction' => $transaction
        ]);
    }
}

