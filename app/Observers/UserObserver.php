<?php

namespace App\Observers;

use App\Models\User;
use App\Services\WalletService;

class UserObserver
{
    protected WalletService $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        // Create wallet for users and stores (not for admin)
        if ($user->isRegularUser() || $user->isStore()) {
            // $this->walletService->createWallet($user);
        }
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        // If user type changed to user or store, create wallet if it doesn't exist
        if (($user->isRegularUser() || $user->isStore()) && !$user->hasWallet()) {
            // $this->walletService->createWallet($user);
        }
    }
}

