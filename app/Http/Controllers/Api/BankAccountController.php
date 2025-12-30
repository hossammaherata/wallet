<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BankAccountResource;
use App\Models\BankAccount;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * BankAccountController
 * 
 * Handles all bank account management API endpoints for users:
 * - List user's bank accounts
 * - Create new bank account
 * - Update bank account
 * - Delete bank account
 * 
 * All endpoints require authentication.
 * Users can only manage their own bank accounts.
 * 
 * @package App\Http\Controllers\Api
 */
class BankAccountController extends Controller
{
    use ApiResponse;

    /**
     * Get all bank accounts for the authenticated user.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $bankAccounts = BankAccount::where('user_id', $user->id)
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->successResponse(
            BankAccountResource::collection($bankAccounts),
            'تم جلب الحسابات البنكية بنجاح'
        );
    }

    /**
     * Create a new bank account for the authenticated user.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'bank_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:255',
            'account_holder_name' => 'required|string|max:255',
            'iban' => 'nullable|string|max:255',
            'branch_name' => 'nullable|string|max:255',
        ], [
            'bank_name.required' => 'اسم البنك مطلوب',
            'account_number.required' => 'رقم الحساب مطلوب',
            'account_holder_name.required' => 'اسم صاحب الحساب مطلوب',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors(), 'البيانات المدخلة غير صحيحة');
        }

        $user = $request->user();

        $bankAccount = BankAccount::create([
            'user_id' => $user->id,
            'bank_name' => $request->bank_name,
            'account_number' => $request->account_number,
            'account_holder_name' => $request->account_holder_name,
            'iban' => $request->iban,
            'branch_name' => $request->branch_name,
            'status' => 'active',
        ]);

        return $this->successResponse(
            new BankAccountResource($bankAccount),
            'تم إضافة الحساب البنكي بنجاح',
            201
        );
    }

    /**
     * Update a bank account.
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $bankAccount = BankAccount::where('user_id', $user->id)
            ->where('id', $id)
            ->first();

        if (!$bankAccount) {
            return $this->errorResponse('الحساب البنكي غير موجود', null, 404);
        }

        $validator = Validator::make($request->all(), [
            'bank_name' => 'sometimes|required|string|max:255',
            'account_number' => 'sometimes|required|string|max:255',
            'account_holder_name' => 'sometimes|required|string|max:255',
            'iban' => 'nullable|string|max:255',
            'branch_name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors(), 'البيانات المدخلة غير صحيحة');
        }

        $bankAccount->update($request->only([
            'bank_name',
            'account_number',
            'account_holder_name',
            'iban',
            'branch_name',
        ]));

        return $this->successResponse(
            new BankAccountResource($bankAccount),
            'تم تحديث الحساب البنكي بنجاح'
        );
    }

    /**
     * Delete a bank account (soft delete).
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $bankAccount = BankAccount::where('user_id', $user->id)
            ->where('id', $id)
            ->first();

        if (!$bankAccount) {
            return $this->errorResponse('الحساب البنكي غير موجود', null, 404);
        }

        // Check if bank account has pending withdrawal requests
        $pendingRequests = $bankAccount->withdrawalRequests()
            ->where('status', 'pending')
            ->count();

        if ($pendingRequests > 0) {
            return $this->errorResponse('لا يمكن حذف الحساب البنكي لأنه يحتوي على طلبات سحب قيد الانتظار', null, 400);
        }

        $bankAccount->delete();

        return $this->successResponse(null, 'تم حذف الحساب البنكي بنجاح');
    }
}
