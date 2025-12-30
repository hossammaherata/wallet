<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Models\WithdrawalRequest;
use App\Services\NotificationService;
use App\Services\TransactionService;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

/**
 * WithdrawalRequestController
 * 
 * Handles all admin operations for managing withdrawal requests:
 * - Listing all withdrawal requests with filters
 * - Viewing withdrawal request details
 * - Approving withdrawal requests (deducts from wallet)
 * - Rejecting withdrawal requests
 * 
 * @package App\Http\Controllers\Admin
 */
class WithdrawalRequestController extends BaseController
{
    protected WalletService $walletService;
    protected TransactionService $transactionService;
    protected NotificationService $notificationService;

    public function __construct(
        WalletService $walletService,
        TransactionService $transactionService,
        NotificationService $notificationService
    ) {
        $this->walletService = $walletService;
        $this->transactionService = $transactionService;
        $this->notificationService = $notificationService;
    }

    /**
     * Display a listing of withdrawal requests.
     * 
     * @param Request $request
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        $status = $request->get('status', 'all');
        $keyword = $request->get('keyword', '');
        
        // Normalize status - handle '0', 0, null, empty string
        if ($status === '0' || $status === 0 || $status === null || $status === '') {
            $status = 'all';
        }
        
        $query = WithdrawalRequest::with(['user', 'bankAccount', 'admin', 'walletTransaction'])
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        // Search by user name or phone
        if ($keyword) {
            $query->whereHas('user', function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhere('phone', 'like', "%{$keyword}%");
            });
        }

        $withdrawalRequests = $query->paginate(15);

        return Inertia::render('Admin/WithdrawalRequests/Index', [
            'withdrawalRequests' => $withdrawalRequests,
            'status' => $status,
            'keyword' => $keyword,
        ]);
    }

    /**
     * Show withdrawal request details.
     * 
     * @param int $id
     * @return \Inertia\Response
     */
    public function show(int $id)
    {
        $withdrawalRequest = WithdrawalRequest::with([
            'user',
            'bankAccount',
            'admin',
            'walletTransaction'
        ])->findOrFail($id);

        return Inertia::render('Admin/WithdrawalRequests/Show', [
            'withdrawalRequest' => $withdrawalRequest,
        ]);
    }

    /**
     * Approve a withdrawal request.
     * 
     * Deducts the amount from user's wallet and creates a transaction.
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function approve(Request $request, int $id)
    {
        $admin = $request->user();
        
        if (!$admin->isAdmin()) {
            return redirect()->back()->with('error', 'غير مصرح لك بتنفيذ هذه العملية');
        }

        $withdrawalRequest = WithdrawalRequest::with(['user', 'bankAccount'])
            ->findOrFail($id);

        if (!$withdrawalRequest->isPending()) {
            return redirect()->back()->with('error', 'لا يمكن الموافقة على طلب السحب إلا إذا كان في حالة انتظار');
        }

        $user = $withdrawalRequest->user;
        $amount = $withdrawalRequest->amount;

        // Check wallet balance
        $balance = $this->walletService->getBalance($user);
        
        if ($amount > $balance) {
            return redirect()->back()->with('error', "رصيد المستخدم غير كافٍ. الرصيد الحالي: {$balance} نقطة");
        }

        // Check if user has wallet
        if (!$user->hasWallet()) {
            return redirect()->back()->with('error', 'المستخدم ليس لديه محفظة');
        }

        // Check if wallet is locked
        if ($user->wallet->isLocked()) {
            return redirect()->back()->with('error', 'محفظة المستخدم موقفة حالياً');
        }

        try {
            DB::beginTransaction();

            // Lock wallet to prevent race conditions
            $wallet = $user->wallet;
            $wallet = \App\Models\Wallet::lockForUpdate()->find($wallet->id);

            // Re-check balance after locking
            $currentBalance = $this->walletService->getBalance($user);
            if ($currentBalance < $amount) {
                DB::rollBack();
                return redirect()->back()->with('error', "رصيد المستخدم غير كافٍ. الرصيد الحالي: {$currentBalance} نقطة");
            }

            // Create withdrawal transaction (debit - reduces balance)
            $transaction = $this->transactionService->create([
                'from_wallet_id' => $wallet->id,
                'to_wallet_id' => null, // Withdrawal - no destination wallet
                'amount' => $amount,
                'type' => 'withdrawal',
                'status' => 'success',
                'meta' => [
                    'withdrawal_request_id' => $withdrawalRequest->id,
                    'bank_account_id' => $withdrawalRequest->bank_account_id,
                    'bank_name' => $withdrawalRequest->bankAccount->bank_name,
                    'account_number' => $withdrawalRequest->bankAccount->account_number,
                    'approved_by' => $admin->id,
                    'approved_by_name' => $admin->name,
                ],
            ]);

            // Update wallet balance
            $newBalance = $currentBalance - $amount;
            $wallet->update(['balance' => $newBalance]);

            // Update withdrawal request
            $withdrawalRequest->update([
                'status' => 'approved',
                'admin_id' => $admin->id,
                'admin_notes' => $request->input('admin_notes'),
                'approved_at' => now(),
                'wallet_transaction_id' => $transaction->id,
            ]);

            // Send notification to user
            $this->notificationService->send(
                $user,
                'withdrawal_request_approved',
                'تم الموافقة على طلب السحب',
                "تم الموافقة على طلب السحب بقيمة {$amount} نقطة. تم خصم المبلغ من محفظتك",
                [
                    'withdrawal_request_id' => $withdrawalRequest->id,
                    'amount' => $amount,
                    'transaction_id' => $transaction->id,
                ]
            );

            DB::commit();

            return redirect()->back()->with('success', 'تم الموافقة على طلب السحب بنجاح وتم خصم المبلغ من محفظة المستخدم');
        } catch (\Exception $e) {
            dd($e);
            DB::rollBack();
            return redirect()->back()->with('error', 'حدث خطأ أثناء الموافقة على طلب السحب: ' . $e->getMessage());
        }
    }

    /**
     * Reject a withdrawal request.
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reject(Request $request, int $id)
    {
        $admin = $request->user();
        
        if (!$admin->isAdmin()) {
            return redirect()->back()->with('error', 'غير مصرح لك بتنفيذ هذه العملية');
        }

        $withdrawalRequest = WithdrawalRequest::with(['user'])
            ->findOrFail($id);

        if (!$withdrawalRequest->isPending()) {
            return redirect()->back()->with('error', 'لا يمكن رفض طلب السحب إلا إذا كان في حالة انتظار');
        }

        try {
            $withdrawalRequest->update([
                'status' => 'rejected',
                'admin_id' => $admin->id,
                'admin_notes' => $request->input('admin_notes'),
                'rejected_at' => now(),
            ]);

            // Send notification to user
            $this->notificationService->send(
                $withdrawalRequest->user,
                'withdrawal_request_rejected',
                'تم رفض طلب السحب',
                "تم رفض طلب السحب بقيمة {$withdrawalRequest->amount} نقطة" . 
                ($request->input('admin_notes') ? ". ملاحظة: {$request->input('admin_notes')}" : ''),
                [
                    'withdrawal_request_id' => $withdrawalRequest->id,
                    'amount' => $withdrawalRequest->amount,
                    'admin_notes' => $request->input('admin_notes'),
                ]
            );

            return redirect()->back()->with('success', 'تم رفض طلب السحب بنجاح');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء رفض طلب السحب: ' . $e->getMessage());
        }
    }
}
