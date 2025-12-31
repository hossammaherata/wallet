<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\CityController;
use App\Http\Controllers\Admin\StoreController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\WithdrawalRequestController;
use App\Http\Controllers\Admin\WalletConfigurationController;




Route::middleware('admin')->group(function () {
    Route::resource('/users', UserController::class);
    Route::post('/users/{id}/status', [UserController::class, 'updateStatus'])->name('users.updateStatus');
    Route::post('/users/{id}/wallet-status', [UserController::class, 'updateWalletStatus'])->name('users.updateWalletStatus');
    Route::get('/', [UserController::class, 'index'])->name('home');
    
    // Store routes
    Route::resource('/stores', StoreController::class);
    Route::post('/stores/{id}/status', [StoreController::class, 'updateStatus'])->name('stores.updateStatus');
    Route::post('/stores/{id}/wallet-status', [StoreController::class, 'updateWalletStatus'])->name('stores.updateWalletStatus');
    Route::post('/stores/{id}/external-payment', [StoreController::class, 'recordExternalPayment'])->name('stores.recordExternalPayment');
    
    // Transaction routes
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/{id}', [TransactionController::class, 'show'])->name('transactions.show');
    
    // Withdrawal Request routes
    Route::get('/withdrawal-requests', [WithdrawalRequestController::class, 'index'])->name('withdrawal-requests.index');
    Route::get('/withdrawal-requests/{id}', [WithdrawalRequestController::class, 'show'])->name('withdrawal-requests.show');
    Route::post('/withdrawal-requests/{id}/approve', [WithdrawalRequestController::class, 'approve'])->name('withdrawal-requests.approve');
    Route::post('/withdrawal-requests/{id}/reject', [WithdrawalRequestController::class, 'reject'])->name('withdrawal-requests.reject');
    
    // Wallet Configuration routes
    Route::get('/wallet-configuration', [WalletConfigurationController::class, 'index'])->name('wallet-configuration.index');
    Route::put('/wallet-configuration', [WalletConfigurationController::class, 'update'])->name('wallet-configuration.update');
});

Route::get('/cities', [CityController::class, 'index'])->name('cities.index');
Route::get('/cities/{id}/edit', [CityController::class, 'edit'])->name('cities.edit');
Route::put('/cities/{id}', [CityController::class, 'update'])->name('cities.update');



// Route::get('/get-result', [SearchController::class,'GetResult'])->name('search.get.reslut');


