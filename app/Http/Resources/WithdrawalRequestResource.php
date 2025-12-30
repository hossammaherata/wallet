<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WithdrawalRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'amount' => (float) $this->amount,
            'status' => $this->status,
            'admin_notes' => $this->admin_notes,
            'approved_at' => $this->approved_at?->toDateTimeString(),
            'rejected_at' => $this->rejected_at?->toDateTimeString(),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
            'bank_account' => $this->when(
                $this->relationLoaded('bankAccount'),
                function () {
                    return [
                        'id' => $this->bankAccount->id,
                        'bank_name' => $this->bankAccount->bank_name,
                        'account_number' => $this->bankAccount->account_number,
                        'account_holder_name' => $this->bankAccount->account_holder_name,
                        'iban' => $this->bankAccount->iban,
                        'branch_name' => $this->bankAccount->branch_name,
                    ];
                }
            ),
        ];
    }
}
