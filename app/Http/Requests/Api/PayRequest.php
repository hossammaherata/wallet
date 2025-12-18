<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class PayRequest extends FormRequest
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
            'qr_code' => 'required_without:store_id|string',
            'store_id' => 'required_without:qr_code|integer|exists:users,id',
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
            'qr_code.required_without' => 'رمز QR Code مطلوب إذا لم يتم إرسال معرف المتجر',
            'qr_code.string' => 'رمز QR Code غير صحيح',
            'store_id.required_without' => 'معرف المتجر مطلوب إذا لم يتم إرسال رمز QR Code',
            'store_id.integer' => 'معرف المتجر يجب أن يكون رقماً',
            'store_id.exists' => 'المتجر المحدد غير موجود',
            'amount.required' => 'المبلغ مطلوب',
            'amount.numeric' => 'المبلغ يجب أن يكون رقماً',
            'amount.min' => 'المبلغ يجب أن يكون على الأقل 0.01',
            'meta.array' => 'البيانات الإضافية يجب أن تكون مصفوفة',
        ];
    }
}

