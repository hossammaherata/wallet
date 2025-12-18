<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'email' => 'nullable|string|email|max:255|unique:users',
            'phone' => 'required_without:email|string|unique:users',
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
            'name.required' => 'اسم المستخدم مطلوب',
            'name.string' => 'اسم المستخدم يجب أن يكون نص',
            'name.max' => 'اسم المستخدم يجب ألا يتجاوز 255 حرف',
            'email.email' => 'البريد الإلكتروني غير صحيح',
            'email.unique' => 'البريد الإلكتروني مستخدم بالفعل',
            'phone.required_without' => 'رقم الهاتف أو البريد الإلكتروني مطلوب',
            'phone.unique' => 'رقم الهاتف مستخدم بالفعل',
        ];
    }
}

