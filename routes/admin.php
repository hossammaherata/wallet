<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\CityController;
use App\Http\Controllers\Admin\StoreController;
use App\Http\Controllers\Admin\TransactionController;




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
});

Route::get('/cities', [CityController::class, 'index'])->name('cities.index');
Route::get('/cities/{id}/edit', [CityController::class, 'edit'])->name('cities.edit');
Route::put('/cities/{id}', [CityController::class, 'update'])->name('cities.update');



// Route::get('/get-result', [SearchController::class,'GetResult'])->name('search.get.reslut');


