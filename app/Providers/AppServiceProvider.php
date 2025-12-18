<?php

namespace App\Providers;

use App\Models\User;
use App\Observers\UserObserver;
use App\Services\TransactionService;
use App\Services\WalletService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        // Register User Observer for automatic wallet creation
        User::observe(UserObserver::class);
    }
}
