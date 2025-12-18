<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\BaseController;
use App\Models\WalletTransaction;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

/**
 * StoreDashboardController
 * 
 * Handles store dashboard operations:
 * - Display store statistics (total payments, transaction count, current balance)
 * - Display transaction history (incoming payments and external payments)
 * - Generate QR Code for store
 * 
 * @package App\Http\Controllers\Store
 */
class StoreDashboardController extends BaseController
{
    /**
     * Display store dashboard with statistics and transactions.
     *
     * @param Request $request
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        $store = auth()->user();
        
        // Ensure store has store_code
        if (!$store->store_code) {
            $store->update(['store_code' => \Illuminate\Support\Str::uuid()]);
            $store->refresh();
        }
        
        $wallet = $store->wallet;

        // Initialize statistics
        $stats = [
            'total_payments' => 0,
            'total_amount' => 0,
            'transaction_count' => 0,
            'current_balance' => 0,
        ];

        $transactions = collect();

        if ($wallet) {
            // Get all transactions related to this store's wallet
            // Same query as Admin StoreController to ensure consistency
            $allTransactionsQuery = WalletTransaction::where(function ($q) use ($wallet) {
                $q->where('from_wallet_id', $wallet->id)
                  ->orWhere('to_wallet_id', $wallet->id);
            })
            ->where('status', 'success');

            // Calculate statistics
            // Total payments received (purchase only)
            $totalPaymentsQuery = WalletTransaction::where('to_wallet_id', $wallet->id)
                ->where('status', 'success')
                ->where('type', 'purchase');
            
            $stats['total_payments'] = (float) (clone $totalPaymentsQuery)->sum('amount');
            $stats['transaction_count'] = (clone $allTransactionsQuery)->count();
            $stats['current_balance'] = (float) $wallet->balance;

            // Get paginated transactions
            $transactions = $allTransactionsQuery
                ->with(['fromWallet.user', 'toWallet.user'])
                ->orderBy('created_at', 'desc')
                ->paginate(15);

            // Transform transactions for display
            $transactions->getCollection()->transform(function ($transaction) use ($wallet) {
                $walletId = $wallet->id;
                
                // Ensure meta is an array - handle both array and JSON string
                // Laravel casts meta as 'array', but ensure it's always an array
                $meta = [];
                if ($transaction->meta !== null) {
                    if (is_array($transaction->meta)) {
                        $meta = $transaction->meta;
                    } elseif (is_string($transaction->meta)) {
                        $decoded = json_decode($transaction->meta, true);
                        $meta = is_array($decoded) ? $decoded : [];
                    } elseif (is_object($transaction->meta)) {
                        $meta = (array) $transaction->meta;
                    }
                }
                
                // Determine if this transaction is credit (money in) or debit (money out) for this store
                $isCredit = $transaction->to_wallet_id == $walletId;
                $isDebit = $transaction->from_wallet_id == $walletId;
                
                // Handle external payment (debit with payment_type = external)
                if ($transaction->type === 'debit' && isset($meta['payment_type']) && $meta['payment_type'] === 'external') {
                    $transaction->customer_name = 'External Payment';
                    $transaction->transaction_type_label = 'External Payment';
                    $transaction->is_credit = false;
                    $transaction->is_debit = true;
                    $transaction->amount_formatted = '-' . number_format((float) $transaction->amount, 2);
                    $transaction->note = $meta['note'] ?? null;
                } else {
                    // For all other transactions, determine credit/debit based on wallet direction
                    $amount = (float) $transaction->amount;
                    $amountDirection = $isCredit ? 'in' : ($isDebit ? 'out' : 'neutral');
                    $displayAmount = $isCredit ? '+' . number_format($amount, 2) : ($isDebit ? '-' . number_format($amount, 2) : number_format($amount, 2));
                    
                    $transaction->is_credit = $isCredit;
                    $transaction->is_debit = $isDebit;
                    $transaction->amount_formatted = $displayAmount;
                    
                    // Set customer name and label based on transaction type and direction
                    if ($transaction->type === 'purchase') {
                        // Incoming payment from customer
                        if ($transaction->fromWallet && $transaction->fromWallet->user) {
                            $transaction->customer_name = $transaction->fromWallet->user->name ?? 'Unknown Customer';
                        } else {
                            $transaction->customer_name = 'Unknown Customer';
                        }
                        $transaction->transaction_type_label = 'Payment from Customer';
                    } elseif ($transaction->type === 'credit') {
                        // Credit transaction
                        if ($transaction->fromWallet && $transaction->fromWallet->user) {
                            $transaction->customer_name = $transaction->fromWallet->user->name ?? 'System';
                        } else {
                            $transaction->customer_name = 'System';
                        }
                        $transaction->transaction_type_label = 'Credit';
                    } elseif ($transaction->type === 'transfer') {
                        // Transfer transaction
                        if ($isCredit) {
                            // Money coming in
                            if ($transaction->fromWallet && $transaction->fromWallet->user) {
                                $transaction->customer_name = $transaction->fromWallet->user->name ?? 'Unknown';
                            } else {
                                $transaction->customer_name = 'Unknown';
                            }
                            $transaction->transaction_type_label = 'Transfer Received';
                        } else {
                            // Money going out
                            if ($transaction->toWallet && $transaction->toWallet->user) {
                                $transaction->customer_name = $transaction->toWallet->user->name ?? 'Unknown';
                            } else {
                                $transaction->customer_name = 'Unknown';
                            }
                            $transaction->transaction_type_label = 'Transfer Sent';
                        }
                    } elseif ($transaction->type === 'refund') {
                        // Refund transaction
                        if ($transaction->fromWallet && $transaction->fromWallet->user) {
                            $transaction->customer_name = $transaction->fromWallet->user->name ?? 'System';
                        } else {
                            $transaction->customer_name = 'System';
                        }
                        $transaction->transaction_type_label = 'Refund';
                    } else {
                        // Default for other types
                        if ($isCredit && $transaction->fromWallet && $transaction->fromWallet->user) {
                            $transaction->customer_name = $transaction->fromWallet->user->name ?? 'Unknown';
                        } elseif ($isDebit && $transaction->toWallet && $transaction->toWallet->user) {
                            $transaction->customer_name = $transaction->toWallet->user->name ?? 'Unknown';
                        } else {
                            $transaction->customer_name = 'System';
                        }
                        $transaction->transaction_type_label = ucfirst($transaction->type);
                    }
                    
                    $transaction->note = $meta['note'] ?? null;
                }
                
                $transaction->date_formatted = $transaction->created_at->format('Y-m-d H:i');
                return $transaction;
            });
        }

        return Inertia::render('Store/Dashboard', [
            'store' => $store,
            'stats' => $stats,
            'transactions' => $transactions,
        ]);
    }

    /**
     * Generate QR Code for the store.
     * Returns QR Code as image response.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function qrCode(Request $request)
    {
        $store = auth()->user();

        if (!$store->store_code) {
            // Generate store_code if not exists
            $store->update(['store_code' => \Illuminate\Support\Str::uuid()]);
            $store->refresh();
        }

        // QR Code content: store-{uuid}
        $qrContent = 'store-' . $store->store_code;

        // Generate QR Code using SimpleSoftwareIO/simple-qrcode
        try {
            // Use SVG format which doesn't require imagick
            $qrCode = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
                ->size(300)
                ->generate($qrContent);

            return response($qrCode, 200)
                ->header('Content-Type', 'image/svg+xml')
                ->header('Content-Disposition', 'inline; filename="store-qr-code.svg"');
        } catch (\Exception $e) {
            abort(500, 'Failed to generate QR Code: ' . $e->getMessage());
        }
    }

    /**
     * Download QR Code as image file.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function downloadQrCode(Request $request)
    {
        $store = auth()->user();

        if (!$store->store_code) {
            $store->update(['store_code' => \Illuminate\Support\Str::uuid()]);
            $store->refresh();
        }

        $qrContent = 'store-' . $store->store_code;

        try {
            // Use SVG format which doesn't require imagick
            $qrCode = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
                ->size(500)
                ->generate($qrContent);

            $filename = 'store-qr-code-' . $store->id . '.svg';

            return response($qrCode, 200)
                ->header('Content-Type', 'image/svg+xml')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
        } catch (\Exception $e) {
            abort(500, 'Failed to generate QR Code: ' . $e->getMessage());
        }
    }

    /**
     * Display notifications for the store.
     *
     * @param Request $request
     * @return \Inertia\Response
     */
    public function notifications(Request $request)
    {
        $store = auth()->user();
        
        $notifications = Notification::where('user_id', $store->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        // Add relative time for each notification
        $notifications->getCollection()->transform(function ($notification) {
            $notification->time_ago = $notification->created_at->diffForHumans();
            return $notification;
        });
        
        return Inertia::render('Store/Notifications', [
            'store' => $store,
            'notifications' => $notifications,
        ]);
    }

    /**
     * Mark notification as read.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function markNotificationAsRead(Request $request, int $id)
    {
        $store = auth()->user();
        
        $notification = Notification::where('id', $id)
            ->where('user_id', $store->id)
            ->firstOrFail();
        
        $notification->markAsRead();
        
        return redirect()->back();
    }

    /**
     * Mark all notifications as read.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function markAllNotificationsAsRead(Request $request)
    {
        $store = auth()->user();
        
        Notification::where('user_id', $store->id)
            ->where('read', false)
            ->update([
                'read' => true,
                'read_at' => now(),
            ]);
        
        return redirect()->back();
    }

    /**
     * Display store profile page.
     *
     * @param Request $request
     * @return \Inertia\Response
     */
    public function profile(Request $request)
    {
        $store = auth()->user();
        
        return Inertia::render('Store/Profile', [
            'store' => $store,
        ]);
    }

    /**
     * Update store profile.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateProfile(Request $request)
    {
        $store = auth()->user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|string|email|max:255|unique:users,email,' . $store->id,
            'phone' => 'required|string|unique:users,phone,' . $store->id,
            'password' => 'nullable|string|min:8|confirmed',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            $updateData = $request->only(['name', 'email', 'phone']);
            
            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            // Handle profile photo upload (same as admin profile)
            if ($request->hasFile('photo')) {
                // Delete old photo if exists
                if ($store->profile_photo_path && Storage::disk('public')->exists($store->profile_photo_path)) {
                    Storage::disk('public')->delete($store->profile_photo_path);
                }
                
                $photo = $request->file('photo');
                $photoName = time() . '_' . uniqid() . '.' . $photo->getClientOriginalExtension();
                $photoPath = $photo->storeAs('profile-photos', $photoName, 'public');
                $updateData['profile_photo_path'] = $photoPath;
            }

            $store->update($updateData);

            return redirect()->route('store.profile')
                ->with('success', 'تم تحديث الملف الشخصي بنجاح');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }
}
