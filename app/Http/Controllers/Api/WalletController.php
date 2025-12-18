<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\PayRequest;
use App\Http\Requests\Api\TransferRequest;
use App\Http\Requests\Api\UpdateProfileRequest;
use App\Http\Resources\ProfileResource;
use App\Http\Resources\StoreResource;
use App\Http\Resources\WalletResource;
use App\Http\Resources\WalletTransactionResource;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Services\WalletService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * WalletController
 * 
 * Handles all wallet-related API endpoints for mobile applications:
 * - Get user profile with wallet information
 * - Get current wallet balance
 * - Get list of active stores
 * - Get transaction history with filters
 * - Pay to store using QR code (requires wallet:pay ability)
 * - Transfer to user (requires wallet:transfer ability)
 * 
 * All endpoints require authentication (Bearer token).
 * Payment and transfer endpoints require specific abilities.
 * All responses use ApiResponse trait for consistent format.
 * All error messages are in Arabic.
 * 
 * @package App\Http\Controllers\Api
 */
class WalletController extends Controller
{
    use ApiResponse;

    /**
     * WalletService instance for business logic.
     * 
     * @var WalletService
     */
    protected WalletService $walletService;

    /**
     * Create a new WalletController instance.
     * 
     * @param WalletService $walletService
     */
    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Get user profile with wallet.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user()->load('wallet');
        
        return $this->successResponse(
            new ProfileResource($user),
            'تم جلب البيانات بنجاح'
        );
    }

    /**
     * Update user profile.
     *
     * @param UpdateProfileRequest $request
     * @return JsonResponse
     */
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        
        $user->update($request->only(['name', 'email', 'phone']));
        
        // Reload wallet relationship
        $user->load('wallet');
        
        return $this->successResponse(
            new ProfileResource($user),
            'تم تحديث البيانات بنجاح'
        );
    }

    /**
     * Get current wallet balance.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function balance(Request $request): JsonResponse
    {
        $user = $request->user();
        $wallet = $user->wallet;
        
        if (!$wallet) {
            return $this->successResponse([
                'balance' => 0,
                'currency' => 'points',
            ], 'تم جلب الرصيد بنجاح');
        }

        return $this->successResponse([
            'balance' => (float) $wallet->balance,
            'currency' => 'points',
        ], 'تم جلب الرصيد بنجاح');
    }

    /**
     * Get list of active stores with search support.
     * 
     * Search parameter searches across: name, email, phone
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function stores(Request $request): JsonResponse
    {
        $search = $request->get('search');
        
        $query = User::where('type', 'store')
            ->where('status', 'active');
        
        // Apply search using SearchTrait's ofKeyword scope
        // Searches across: name, email, phone (defined in User::$searchable)
        if ($search) {
            $query->ofKeyword($search);
        }
        
        $stores = $query->orderBy('name')->get();

        return $this->successResponse(
            StoreResource::collection($stores),
            'تم جلب المتاجر بنجاح'
        );
    }

    /**
     * Get transaction history.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function transactions(Request $request): JsonResponse
    {
        $user = $request->user();
        $wallet = $user->wallet;

        if (!$wallet) {
            return $this->successResponse([
                'transactions' => [],
                'pagination' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => 15,
                    'total' => 0,
                ],
            ], 'تم جلب المعاملات بنجاح');
        }

        $perPage = $request->get('per_page', 15);
        $status = $request->get('status');
        $type = $request->get('type');

        $query = WalletTransaction::where(function ($q) use ($wallet) {
            $q->where('from_wallet_id', $wallet->id)
              ->orWhere('to_wallet_id', $wallet->id);
        });

        if ($status) {
            $query->where('status', $status);
        }

        if ($type) {
            $query->where('type', $type);
        }

        $transactions = $query->with(['fromWallet.user', 'toWallet.user'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return $this->successResponse([
            'transactions' => WalletTransactionResource::collection($transactions->items()),
            'pagination' => [
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'per_page' => $transactions->perPage(),
                'total' => $transactions->total(),
            ],
        ], 'تم جلب المعاملات بنجاح');
    }

    /**
     * Pay from user wallet to store wallet.
     * 
     * Can accept either:
     * - qr_code: store-{uuid} (from QR code scan)
     * - store_id: numeric ID of the store
     *
     * @param PayRequest $request
     * @return JsonResponse
     */
    public function pay(PayRequest $request): JsonResponse
    {
        $user = $request->user();
        
        // Only regular users can make payments (stores cannot pay)
        if (!$user->isRegularUser()) {
            return $this->errorResponse('يمكن للمستخدمين العاديين فقط القيام بالدفع', null, 403);
        }
        
        // Determine store identifier (qr_code or store_id)
        $storeIdentifier = $request->qr_code ?? $request->store_id;
        
        try {
            $transaction = $this->walletService->pay(
                $user,
                $storeIdentifier, // Can be qr_code (store-{uuid}) or store_id
                (float) $request->amount,
                $request->meta
            );

            return $this->successResponse([
                'transaction' => new WalletTransactionResource($transaction->load(['fromWallet.user', 'toWallet.user'])),
                'new_balance' => $this->walletService->getBalance($user),
            ], 'تم الدفع بنجاح', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 400);
        }
    }

    /**
     * Transfer points from one user to another using phone number.
     *
     * @param TransferRequest $request
     * @return JsonResponse
     */
    public function transfer(TransferRequest $request): JsonResponse
    {
        $user = $request->user();
        
        // Only regular users can transfer (stores cannot transfer)
        if (!$user->isRegularUser()) {
            return $this->errorResponse('يمكن للمستخدمين العاديين فقط القيام بالتحويل', null, 403);
        }
        
        $recipient = User::where('phone', $request->phone)->firstOrFail();

        if (!$recipient->isRegularUser()) {
            return $this->errorResponse('يمكن التحويل فقط للمستخدمين العاديين', null, 400);
        }

        try {
            $transaction = $this->walletService->transfer(
                $user,
                $recipient,
                (float) $request->amount,
                $request->meta
            );

            return $this->successResponse([
                'transaction' => new WalletTransactionResource($transaction->load(['fromWallet.user', 'toWallet.user'])),
                'new_balance' => $this->walletService->getBalance($user),
            ], 'تم التحويل بنجاح', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 400);
        }
    }
}

