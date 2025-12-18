<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

/**
 * UserController
 * 
 * Handles all admin operations for managing users including:
 * - Listing users with search and pagination
 * - Creating new users
 * - Viewing user details (with wallet and transactions)
 * - Updating user information
 * - Deleting users (soft delete)
 * - Updating user status (active/suspended)
 * 
 * All delete operations use soft deletes to preserve data integrity.
 * 
 * @package App\Http\Controllers\Admin
 */
class UserController extends BaseController
{
    /**
     * UserService instance for business logic.
     * 
     * @var UserService
     */
    protected UserService $userService;

    /**
     * Create a new UserController instance.
     * 
     * @param UserService $userService
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Display a listing of users.
     * 
     * Supports keyword search and pagination.
     * Only shows regular users (type='user'), not stores or admins.
     * 
     * @param Request $request
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {

        $authUser = $request->user();
        if($authUser->isStore()){
           return redirect()->route('store.dashboard');
        }
        $keyword = $request->get('keyword', null);
        $users = User::where('type', 'user')
            ->ofKeyword($keyword)
            ->with('wallet')
            ->orderBy('id', 'desc')
            ->paginate(10);
        
        return Inertia::render('Admin/users/Index', [
            'users' => $users,
            'keyword' => $keyword,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Admin/users/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|string|email|max:255|unique:users',
            'phone' => 'required_without:email|string|unique:users',
            'status' => 'required|in:active,suspended',
        ]);

        try {
            $userData = [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'status' => $request->status,
            ];

            $this->userService->register($userData);

            return redirect()->route('admin.users.index')
                ->with('success', 'User created successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::with('wallet')->findOrFail($id);
        
        // Get transactions for this user's wallet
        $transactions = collect();
        if ($user->wallet) {
            $transactions = \App\Models\WalletTransaction::where(function ($q) use ($user) {
                $q->where('from_wallet_id', $user->wallet->id)
                  ->orWhere('to_wallet_id', $user->wallet->id);
            })
            ->with(['fromWallet.user', 'toWallet.user'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);
            
            // Add display fields for each transaction (from this user's perspective)
            $transactions->getCollection()->transform(function ($transaction) use ($user) {
                $walletId = $user->wallet->id;
                $amount = (float) $transaction->amount;
                
                // Determine if this transaction is credit (money in) or debit (money out) for this user
                $isCredit = $transaction->to_wallet_id == $walletId;
                $isDebit = $transaction->from_wallet_id == $walletId;
                
                $amountDirection = $isCredit ? 'in' : ($isDebit ? 'out' : 'neutral');
                $displayAmount = $isCredit ? '+' . number_format($amount, 2) : ($isDebit ? '-' . number_format($amount, 2) : number_format($amount, 2));
                
                $transaction->is_credit = $isCredit;
                $transaction->is_debit = $isDebit;
                $transaction->amount_direction = $amountDirection;
                $transaction->display_amount = $displayAmount;
                
                return $transaction;
            });
        }
        
        return Inertia::render('Admin/users/Show', [
            'user' => $user,
            'transactions' => $transactions,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = User::with('wallet')->findOrFail($id);
        
        return Inertia::render('Admin/users/Edit', [
            'user' => $user
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);
        
        // Only validate password for stores, not for regular users
        $validationRules = [
            'name' => 'required|string|max:255',
            'email' => 'nullable|string|email|max:255|unique:users,email,' . $id,
            'phone' => 'nullable|string|unique:users,phone,' . $id,
            'status' => 'required|in:active,suspended',
        ];
        
        // Only add password validation for stores
        if ($user->isStore()) {
            $validationRules['password'] = 'nullable|string|min:8|confirmed';
        }
        
        $request->validate($validationRules);

        try {
            $updateData = [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'status' => $request->status,
            ];

            // Only update password for stores
            if ($user->isStore() && $request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            $user->update($updateData);

            // If user is suspended, revoke all tokens to force logout
            if ($request->status === 'suspended') {
                $user->tokens()->delete();
            }

            return redirect()->route('admin.users.index')
                ->with('success', 'User updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Update user status.
     */
    public function updateStatus(Request $request, string $id)
    {
        $request->validate([
            'status' => 'required|in:active,suspended',
        ]);

        $user = User::findOrFail($id);
        
        $this->userService->updateStatus($user, $request->status);

        return redirect()->back()
            ->with('success', 'User status updated successfully');
    }

    /**
     * Update wallet status (lock/unlock).
     */
    public function updateWalletStatus(Request $request, string $id)
    {
        $request->validate([
            'status' => 'required|in:active,locked',
        ]);

        $user = User::with('wallet')->findOrFail($id);
        
        if (!$user->wallet) {
            return redirect()->back()
                ->with('error', 'المستخدم لا يملك محفظة');
        }

        $user->wallet->update(['status' => $request->status]);

        $statusText = $request->status === 'locked' ? 'موقفة' : 'نشطة';
        
        return redirect()->back()
            ->with('success', "تم تحديث حالة المحفظة إلى: {$statusText}");
    }

    /**
     * Remove the specified user from storage.
     * 
     * Uses soft delete to preserve data integrity and allow recovery.
     * The user record is marked as deleted but remains in the database.
     * 
     * @param string $id User ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        $user->delete(); // Soft delete - sets deleted_at timestamp

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully');
    }
}
