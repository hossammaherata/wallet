<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class TransferRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'phone' => 'required|string|exists:users,phone',
            'amount' => 'required|numeric|min:0.01',
            'meta' => 'nullable|array',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'phone.required' => 'رقم الهاتف مطلوب',
            'phone.exists' => 'المستخدم غير موجود',
            'amount.required' => 'المبلغ مطلوب',
            'amount.numeric' => 'المبلغ يجب أن يكون رقماً',
            'amount.min' => 'المبلغ يجب أن يكون على الأقل 0.01',
            'meta.array' => 'البيانات الإضافية يجب أن تكون مصفوفة',
        ];
    }
}

