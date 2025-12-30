<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ExternalBalanceController;
use App\Http\Controllers\Api\BankAccountController;
use App\Http\Controllers\Api\WithdrawalRequestController;

/*
||--------------------------------------------------------------------------
|| API Routes
||--------------------------------------------------------------------------
||
|| Here is where you can register API routes for your application. These
|| routes are loaded by the RouteServiceProvider and all of them will
|| be assigned to the "api" middleware group. Make something great!
||
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/*
||--------------------------------------------------------------------------
|| Wallet & Transaction API Routes (Versioned)
||--------------------------------------------------------------------------
||
|| Versioned API routes for mobile applications
||
*/

Route::prefix('v1')->group(function () {
    // Public routes
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    // Protected routes (require authentication, active status, and user/store type only)
    Route::middleware(['auth:sanctum', 'check.user.status', 'check.user.type'])->group(function () {
        // Profile routes
        Route::get('/profile', [WalletController::class, 'profile']);
        Route::put('/profile', [WalletController::class, 'updateProfile']);
        Route::patch('/profile', [WalletController::class, 'updateProfile']);
        
        // Wallet routes
        Route::get('/wallet', [WalletController::class, 'balance']);
        Route::get('/wallet/transactions', [WalletController::class, 'transactions']);
        Route::post('/wallet/pay', [WalletController::class, 'pay'])->middleware('ability:wallet:pay');
        Route::post('/wallet/transfer', [WalletController::class, 'transfer'])->middleware('ability:wallet:transfer');
        
        // Stores list (available for all authenticated users)
        Route::get('/stores', [WalletController::class, 'stores']);
        
        // Notification routes
        Route::prefix('notifications')->group(function () {
            Route::get('/', [NotificationController::class, 'index']);
            Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
            Route::post('/{id}/read', [NotificationController::class, 'markAsRead']);
            Route::post('/read-all', [NotificationController::class, 'markAllAsRead']);
        });
        
        // Bank Account routes
        Route::prefix('bank-accounts')->group(function () {
            Route::get('/', [BankAccountController::class, 'index']);
            Route::post('/', [BankAccountController::class, 'store']);
            Route::put('/{id}', [BankAccountController::class, 'update']);
            Route::delete('/{id}', [BankAccountController::class, 'destroy']);
        });
        
        // Withdrawal Request routes
        Route::prefix('withdrawal-requests')->group(function () {
            Route::get('/', [WithdrawalRequestController::class, 'index']);
            Route::post('/', [WithdrawalRequestController::class, 'store']);
            Route::post('/{id}/cancel', [WithdrawalRequestController::class, 'cancel']);
        });
    });
});

/*
||--------------------------------------------------------------------------
|| External API Routes
||--------------------------------------------------------------------------
||
|| These routes are for external systems integration and require API authentication
|| via X-API-Token and X-API-Secret headers
||
*/

Route::prefix('external')->middleware(['api.auth', 'throttle:60,1'])->group(function () {
    Route::post('/balances/update', [ExternalBalanceController::class, 'updateBalances']);
});

/*
||--------------------------------------------------------------------------
|| CRM API Routes
||--------------------------------------------------------------------------
||
|| These routes are for external CRM integration and require API authentication
||
*/

// Route::prefix('crm')->middleware(['api.auth', 'throttle:api'])->group(function () {
//     Route::post('/cities', [App\Http\Controllers\Admin\Crm\CityController::class, 'store']);
// });
