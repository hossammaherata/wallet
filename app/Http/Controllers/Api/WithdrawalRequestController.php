<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\WithdrawalRequestResource;
use App\Models\BankAccount;
use App\Models\WithdrawalRequest;
use App\Models\WalletConfiguration;
use App\Services\NotificationService;
use App\Services\WalletService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * WithdrawalRequestController
 * 
 * Handles all withdrawal request API endpoints for users:
 * - List user's withdrawal requests
 * - Create new withdrawal request
 * - Cancel pending withdrawal request
 * 
 * All endpoints require authentication.
 * Users can only manage their own withdrawal requests.
 * 
 * @package App\Http\Controllers\Api
 */
class WithdrawalRequestController extends Controller
{
    use ApiResponse;

    protected WalletService $walletService;
    protected NotificationService $notificationService;

    public function __construct(WalletService $walletService, NotificationService $notificationService)
    {
        $this->walletService = $walletService;
        $this->notificationService = $notificationService;
    }

    /**
     * Get all withdrawal requests for the authenticated user.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = $request->get('per_page', 15);
        
        $withdrawalRequests = WithdrawalRequest::where('user_id', $user->id)
            ->with(['bankAccount'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return $this->successResponse([
            'withdrawal_requests' => WithdrawalRequestResource::collection($withdrawalRequests->items()),
            'pagination' => [
                'current_page' => $withdrawalRequests->currentPage(),
                'last_page' => $withdrawalRequests->lastPage(),
                'per_page' => $withdrawalRequests->perPage(),
                'total' => $withdrawalRequests->total(),
            ],
        ], 'تم جلب طلبات السحب بنجاح');
    }

    /**
     * Create a new withdrawal request.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'amount' => 'required|numeric|min:0.01',
        ], [
            'bank_account_id.required' => 'يرجى اختيار الحساب البنكي',
            'bank_account_id.exists' => 'الحساب البنكي المحدد غير موجود',
            'amount.required' => 'المبلغ مطلوب',
            'amount.numeric' => 'المبلغ يجب أن يكون رقماً',
            'amount.min' => 'المبلغ يجب أن يكون أكبر من صفر',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors(), 'البيانات المدخلة غير صحيحة');
        }

        $user = $request->user();
        $amount = (float) $request->amount;

        // Check if bank account belongs to user
        $bankAccount = BankAccount::where('id', $request->bank_account_id)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if (!$bankAccount) {
            return $this->errorResponse('الحساب البنكي المحدد غير موجود أو غير نشط', null, 404);
        }

        // Check wallet balance
        $balance = $this->walletService->getBalance($user);
        
        if ($amount > $balance) {
            return $this->errorResponse("رصيدك غير كافٍ. الرصيد الحالي: {$balance} نقطة", null, 400);
        }

        // Check if user has wallet
        if (!$user->hasWallet()) {
            return $this->errorResponse('ليس لديك محفظة', null, 400);
        }

        // Check if wallet is locked
        if ($user->wallet->isLocked()) {
            return $this->errorResponse('محفظتك موقفة حالياً. يرجى التواصل مع الدعم', null, 400);
        }

        // Get wallet configuration to calculate fee at request creation time
        $configuration = WalletConfiguration::getCurrent();
        $fee = $configuration->calculateWithdrawalFee($amount);
        $feePercentage = $configuration->withdrawal_fee_percentage;
        
        // Net amount after fee (what user will actually receive)
        $netAmount = $amount - $fee;

        try {
            DB::beginTransaction();

            $withdrawalRequest = WithdrawalRequest::create([
                'user_id' => $user->id,
                'bank_account_id' => $bankAccount->id,
                'amount' => $amount,
                'fee' => $fee, // Store fee at request creation time
                'status' => 'pending',
            ]);

            // Send notification to user
            $this->notificationService->send(
                $user,
                'withdrawal_request_created',
                'تم إنشاء طلب سحب',
                "تم إنشاء طلب سحب بقيمة {$amount} نقطة. في انتظار الموافقة من الإدارة",
                [
                    'withdrawal_request_id' => $withdrawalRequest->id,
                    'amount' => $amount,
                    'bank_account' => $bankAccount->bank_name,
                ]
            );

            DB::commit();

            return $this->successResponse(
                new WithdrawalRequestResource($withdrawalRequest->load('bankAccount')),
                'تم إنشاء طلب السحب بنجاح. في انتظار الموافقة',
                201
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('حدث خطأ أثناء إنشاء طلب السحب', null, 500);
        }
    }

    /**
     * Cancel a pending withdrawal request.
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function cancel(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $withdrawalRequest = WithdrawalRequest::where('user_id', $user->id)
            ->where('id', $id)
            ->first();

        if (!$withdrawalRequest) {
            return $this->errorResponse('طلب السحب غير موجود', null, 404);
        }

        if (!$withdrawalRequest->isPending()) {
            return $this->errorResponse('لا يمكن إلغاء طلب السحب إلا إذا كان في حالة انتظار', null, 400);
        }

        try {
            $withdrawalRequest->update([
                'status' => 'cancelled',
            ]);

            // Send notification to user
            $this->notificationService->send(
                $user,
                'withdrawal_request_cancelled',
                'تم إلغاء طلب السحب',
                "تم إلغاء طلب السحب بقيمة {$withdrawalRequest->amount} نقطة",
                [
                    'withdrawal_request_id' => $withdrawalRequest->id,
                    'amount' => $withdrawalRequest->amount,
                ]
            );

            return $this->successResponse(
                new WithdrawalRequestResource($withdrawalRequest->load('bankAccount')),
                'تم إلغاء طلب السحب بنجاح'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('حدث خطأ أثناء إلغاء طلب السحب', null, 500);
        }
    }
}
