<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBalanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization is handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // reference_id can be in header (X-Reference-ID) or body
        // If not in header, it's required in body
        $referenceIdRule = $this->header('X-Reference-ID') 
            ? 'nullable|string|max:255' 
            : 'required|string|max:255';

        return [
            'reference_id' => $referenceIdRule,
            'balances' => 'required|array|min:1',
            'balances.*' => 'required|numeric|max:999999999.99', // Each amount can be positive (credit) or negative (debit)
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'reference_id.required' => 'Reference ID is required',
            'reference_id.string' => 'Reference ID must be a string',
            'balances.required' => 'Balances array is required',
            'balances.array' => 'Balances must be an array',
            'balances.min' => 'At least one balance must be provided',
            'balances.*.required' => 'Each amount value is required',
            'balances.*.numeric' => 'Each amount must be a number',
            'balances.*.max' => 'Amount exceeds maximum allowed value',
        ];
    }
}

