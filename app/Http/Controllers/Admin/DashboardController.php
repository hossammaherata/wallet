<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Models\WithdrawalRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

/**
 * DashboardController
 * 
 * Handles admin dashboard with statistics and charts.
 * 
 * @package App\Http\Controllers\Admin
 */
class DashboardController extends BaseController
{
    /**
     * Display the dashboard.
     * 
     * @param Request $request
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        $authUser = $request->user();
        if ($authUser->isStore()) {
            return redirect()->route('store.dashboard');
        }

        // Get date range (default: last 30 days)
        $days = (int) $request->get('days', 30);
        $startDate = now()->subDays($days)->startOfDay();
        $endDate = now()->endOfDay();

        // Basic Statistics
        $stats = [
            'total_users' => User::where('type', 'user')->count(),
            'active_users' => User::where('type', 'user')->where('status', 'active')->count(),
            'suspended_users' => User::where('type', 'user')->where('status', 'suspended')->count(),
            'total_stores' => User::where('type', 'store')->count(),
            'active_stores' => User::where('type', 'store')->where('status', 'active')->count(),
            'total_transactions' => WalletTransaction::where('status', 'success')->count(),
            'total_transactions_amount' => WalletTransaction::where('status', 'success')->sum('amount'),
            'total_fees_collected' => WalletTransaction::where('status', 'success')->sum('fee'),
            'pending_withdrawals' => WithdrawalRequest::where('status', 'pending')->count(),
            'total_withdrawals' => WithdrawalRequest::where('status', 'approved')->count(),
            'total_withdrawals_amount' => WithdrawalRequest::where('status', 'approved')->sum('amount'),
        ];

        // Transactions by type
        $transactionsByType = WalletTransaction::where('status', 'success')
            ->select('type', DB::raw('count(*) as count'), DB::raw('sum(amount) as total_amount'), DB::raw('sum(fee) as total_fee'))
            ->groupBy('type')
            ->get()
            ->map(function ($item) {
                return [
                    'type' => $item->type,
                    'count' => $item->count,
                    'total_amount' => (float) $item->total_amount,
                    'total_fee' => (float) $item->total_fee,
                ];
            });

        // Transactions by status
        $transactionsByStatus = WalletTransaction::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->map(function ($item) {
                return [
                    'status' => $item->status,
                    'count' => $item->count,
                ];
            });

        // Withdrawal requests by status
        $withdrawalsByStatus = WithdrawalRequest::select('status', DB::raw('count(*) as count'), DB::raw('sum(amount) as total_amount'), DB::raw('sum(fee) as total_fee'))
            ->groupBy('status')
            ->get()
            ->map(function ($item) {
                return [
                    'status' => $item->status,
                    'count' => $item->count,
                    'total_amount' => (float) $item->total_amount,
                    'total_fee' => (float) $item->total_fee,
                ];
            });

        // Transactions over time (last 30 days by default)
        $transactionsOverTime = WalletTransaction::where('status', 'success')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('count(*) as count'),
                DB::raw('sum(amount) as total_amount'),
                DB::raw('sum(fee) as total_fee')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date,
                    'count' => $item->count,
                    'total_amount' => (float) $item->total_amount,
                    'total_fee' => (float) $item->total_fee,
                ];
            });

        // New users over time
        $usersOverTime = User::where('type', 'user')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('count(*) as count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date,
                    'count' => $item->count,
                ];
            });

        // Recent transactions
        $recentTransactions = WalletTransaction::with(['fromWallet.user', 'toWallet.user'])
            ->where('status', 'success')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'reference_id' => $transaction->reference_id,
                    'type' => $transaction->type,
                    'amount' => (float) $transaction->amount,
                    'fee' => (float) ($transaction->fee ?? 0),
                    'from_user' => $transaction->fromWallet?->user?->name,
                    'to_user' => $transaction->toWallet?->user?->name,
                    'created_at' => $transaction->created_at,
                ];
            });

        // Recent withdrawal requests
        $recentWithdrawals = WithdrawalRequest::with(['user', 'bankAccount'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($withdrawal) {
                return [
                    'id' => $withdrawal->id,
                    'user_name' => $withdrawal->user?->name,
                    'amount' => (float) $withdrawal->amount,
                    'fee' => (float) ($withdrawal->fee ?? 0),
                    'status' => $withdrawal->status,
                    'bank_name' => $withdrawal->bankAccount?->bank_name,
                    'created_at' => $withdrawal->created_at,
                ];
            });

        return Inertia::render('Admin/Dashboard/Index', [
            'stats' => $stats,
            'transactionsByType' => $transactionsByType,
            'transactionsByStatus' => $transactionsByStatus,
            'withdrawalsByStatus' => $withdrawalsByStatus,
            'transactionsOverTime' => $transactionsOverTime,
            'usersOverTime' => $usersOverTime,
            'recentTransactions' => $recentTransactions,
            'recentWithdrawals' => $recentWithdrawals,
            'days' => $days,
        ]);
    }
}
