<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Models\User;
use App\Services\StoreService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

/**
 * StoreController
 * 
 * Handles all admin operations for managing stores including:
 * - Listing stores with search and pagination
 * - Creating new stores
 * - Viewing store details (with wallet and transactions)
 * - Updating store information
 * - Deleting stores (soft delete)
 * - Updating store status (active/suspended)
 * 
 * Stores are users with type='store'. They can receive payments but cannot transfer.
 * All delete operations use soft deletes to preserve data integrity.
 * 
 * @package App\Http\Controllers\Admin
 */
class StoreController extends BaseController
{
    /**
     * StoreService instance for business logic.
     * 
     * @var StoreService
     */
    protected StoreService $storeService;

    /**
     * Create a new StoreController instance.
     * 
     * @param StoreService $storeService
     */
    public function __construct(StoreService $storeService)
    {
        $this->storeService = $storeService;
    }

    /**
     * Display a listing of stores.
     */
    public function index(Request $request)
    {
        $keyword = $request->get('keyword', null);
        $stores = User::where('type', 'store')
            ->ofKeyword($keyword)
            ->with('wallet')
            ->orderBy('id', 'desc')
            ->paginate(10);
        
        return Inertia::render('Admin/stores/Index', [
            'stores' => $stores,
            'keyword' => $keyword,
        ]);
    }

    /**
     * Show the form for creating a new store.
     */
    public function create()
    {
        return Inertia::render('Admin/stores/Create');
    }

    /**
     * Store a newly created store.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|string|email|max:255|unique:users',
            'phone' => 'required|string|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'status' => 'required|in:active,suspended',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            $data = $request->all();
            
            // Handle profile photo upload (same as admin profile)
            if ($request->hasFile('photo')) {
                $photo = $request->file('photo');
                $photoName = time() . '_' . uniqid() . '.' . $photo->getClientOriginalExtension();
                $photoPath = $photo->storeAs('profile-photos', $photoName, 'public');
                $data['profile_photo_path'] = $photoPath;
            }
               
            $store = $this->storeService->create($data);

            return redirect()->route('admin.stores.index')
                ->with('success', 'Store created successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified store.
     */
    public function show(string $id)
    {
        $store = User::where('type', 'store')
            ->with('wallet')
            ->findOrFail($id);
        
        // Get transactions for this store's wallet
        $transactions = collect();
        if ($store->wallet) {
            $transactions = \App\Models\WalletTransaction::where(function ($q) use ($store) {
                $q->where('from_wallet_id', $store->wallet->id)
                  ->orWhere('to_wallet_id', $store->wallet->id);
            })
            ->with(['fromWallet.user', 'toWallet.user'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);
            
            // Add display fields for each transaction (from this store's perspective)
            $transactions->getCollection()->transform(function ($transaction) use ($store) {
                $walletId = $store->wallet->id;
                $amount = (float) $transaction->amount;
                
                // Handle external payment (debit with payment_type = external)
                if ($transaction->type === 'debit' && $transaction->meta && isset($transaction->meta['payment_type']) && $transaction->meta['payment_type'] === 'external') {
                    $transaction->is_credit = false;
                    $transaction->is_debit = true;
                    $transaction->amount_direction = 'out';
                    $transaction->display_amount = '-' . number_format($amount, 2);
                    $transaction->note = $transaction->meta['note'] ?? null;
                } else {
                    // Determine if this transaction is credit (money in) or debit (money out) for this store
                    $isCredit = $transaction->to_wallet_id == $walletId;
                    $isDebit = $transaction->from_wallet_id == $walletId;
                    
                    $amountDirection = $isCredit ? 'in' : ($isDebit ? 'out' : 'neutral');
                    $displayAmount = $isCredit ? '+' . number_format($amount, 2) : ($isDebit ? '-' . number_format($amount, 2) : number_format($amount, 2));
                    
                    $transaction->is_credit = $isCredit;
                    $transaction->is_debit = $isDebit;
                    $transaction->amount_direction = $amountDirection;
                    $transaction->display_amount = $displayAmount;
                }
                
                return $transaction;
            });
        }
        
        return Inertia::render('Admin/stores/Show', [
            'store' => $store,
            'transactions' => $transactions,
        ]);
    }

    /**
     * Show the form for editing the specified store.
     */
    public function edit(string $id)
    {
        $store = User::where('type', 'store')
            ->with('wallet')
            ->findOrFail($id);
        
        return Inertia::render('Admin/stores/Edit', [
            'store' => $store
        ]);
    }

    /**
     * Update the specified store.
     */
    public function update(Request $request, string $id)
    {
        // dd($request->all());
        $store = User::where('type', 'store')->findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|string|email|max:255|unique:users,email,' . $id,
            'phone' => 'required|string|unique:users,phone,' . $id,
            'password' => 'nullable|string|min:8|confirmed',
            'status' => 'required|in:active,suspended',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            $updateData = $request->only(['name', 'email', 'phone', 'status']);
            
            if ($request->filled('password')) {
                $updateData['password'] = $request->password; // StoreService will hash it
            }

            // Handle profile photo upload (same as admin profile)
            if ($request->hasFile('photo')) {
                // dd('here');
                // Delete old photo if exists
                if ($store->profile_photo_path && Storage::disk('public')->exists($store->profile_photo_path)) {
                    Storage::disk('public')->delete($store->profile_photo_path);
                }
                
                $photo = $request->file('photo');
                $photoName = time() . '_' . uniqid() . '.' . $photo->getClientOriginalExtension();
                $photoPath = $photo->storeAs('profile-photos', $photoName, 'public');
                $updateData['profile_photo_path'] = $photoPath;
                // dd($updateData);
            }

            $this->storeService->update($store, $updateData);

            // If store is suspended, revoke all tokens to force logout
            if ($request->status === 'suspended') {
                $store->tokens()->delete();
            }

            return redirect()->route('admin.stores.index')
                ->with('success', 'Store updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified store from storage.
     * 
     * Uses soft delete to preserve data integrity and allow recovery.
     * The store record is marked as deleted but remains in the database.
     * Transaction history is preserved even after store deletion.
     * 
     * @param string $id Store ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(string $id)
    {
        $store = User::where('type', 'store')->findOrFail($id);
        $store->delete(); // Soft delete - sets deleted_at timestamp

        return redirect()->route('admin.stores.index')
            ->with('success', 'Store deleted successfully');
    }

    /**
     * Update store status.
     */
    public function updateStatus(Request $request, string $id)
    {
        $request->validate([
            'status' => 'required|in:active,suspended',
        ]);

        $store = User::where('type', 'store')->findOrFail($id);
        
        $this->storeService->updateStatus($store, $request->status);

        return redirect()->back()
            ->with('success', 'Store status updated successfully');
    }

    /**
     * Update wallet status (lock/unlock).
     */
    public function updateWalletStatus(Request $request, string $id)
    {
        $request->validate([
            'status' => 'required|in:active,locked',
        ]);

        $store = User::where('type', 'store')->with('wallet')->findOrFail($id);
        
        if (!$store->wallet) {
            return redirect()->back()
                ->with('error', 'المتجر لا يملك محفظة');
        }

        $store->wallet->update(['status' => $request->status]);

        $statusText = $request->status === 'locked' ? 'موقفة' : 'نشطة';
        
        return redirect()->back()
            ->with('success', "تم تحديث حالة المحفظة إلى: {$statusText}");
    }

    /**
     * Record external payment to store.
     * 
     * Allows admin to record an external payment made to a store.
     * This payment reduces the store's balance (debt to admin).
     *
     * @param Request $request
     * @param string $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function recordExternalPayment(Request $request, string $id)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'note' => 'nullable|string|max:500',
        ]);

        $store = User::where('type', 'store')->findOrFail($id);

        try {
            $walletService = app(\App\Services\WalletService::class);
            $transaction = $walletService->recordExternalPaymentToStore(
                $store,
                (float) $request->amount,
                $request->note
            );

            return redirect()->back()
                ->with('success', "تم تسجيل دفعة خارجية بقيمة {$request->amount} نقطة بنجاح");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }
}

