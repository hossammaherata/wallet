<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WalletTransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Get current user's wallet ID from request
        $currentWalletId = $request->user()?->wallet?->id ?? null;
        
        // Determine if this transaction is credit (money in) or debit (money out) for current user
        $isCredit = $currentWalletId && $this->to_wallet_id == $currentWalletId;
        $isDebit = $currentWalletId && $this->from_wallet_id == $currentWalletId;
        
        // Determine amount direction and display
        $amount = (float) $this->amount;
        $amountDirection = $isCredit ? 'in' : ($isDebit ? 'out' : 'neutral');
        $displayAmount = $isCredit ? '+' . number_format($amount, 2) : ($isDebit ? '-' . number_format($amount, 2) : number_format($amount, 2));
        
        return [
            'id' => $this->id,
            'reference_id' => $this->reference_id,
            'amount' => $amount,
            'type' => $this->type,
            'status' => $this->status,
            'is_credit' => $isCredit,
            'is_debit' => $isDebit,
            'amount_direction' => $amountDirection,
            'display_amount' => $displayAmount,
            'from_user' => $this->when(
                $this->relationLoaded('fromWallet') && 
                $this->fromWallet && 
                $this->fromWallet->relationLoaded('user') && 
                $this->fromWallet->user,
                function () {
                    return [
                        'id' => $this->fromWallet->user->id,
                        'name' => $this->fromWallet->user->name,
                        'phone' => $this->fromWallet->user->phone,
                    ];
                }
            ),
            'to_user' => $this->when(
                $this->relationLoaded('toWallet') && 
                $this->toWallet && 
                $this->toWallet->relationLoaded('user') && 
                $this->toWallet->user,
                function () {
                    return [
                        'id' => $this->toWallet->user->id,
                        'name' => $this->toWallet->user->name,
                        'phone' => $this->toWallet->user->phone,
                    ];
                }
            ),
            'meta' => $this->meta,
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}

