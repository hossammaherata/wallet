<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * StoreService
 * 
 * Handles all store-related business logic including:
 * - Store creation (creates wallet automatically)
 * - Store information updates
 * - Store status management (active/suspended)
 * 
 * Stores are users with type='store'. They can receive payments but cannot transfer.
 * All operations use database transactions for data integrity.
 * 
 * @package App\Services
 */
class StoreService
{
    /**
     * WalletService instance for creating wallets.
     * 
     * @var WalletService
     */
    protected WalletService $walletService;

    /**
     * Create a new StoreService instance.
     * 
     * @param WalletService $walletService
     */
    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Create a new store.
     *
     * @param array $data
     * @return User
     */
    public function create(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $store = User::create([
                'name' => $data['name'],
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'password' => Hash::make($data['password']),
                'type' => 'store',
                'status' => $data['status'] ?? 'active',
                'store_code' => Str::uuid(), // Generate unique UUID for QR code
                'profile_photo_path' => $data['profile_photo_path'] ?? null,
            ]);

            // Create wallet for the store
            $this->walletService->createWallet($store);

            return $store;
        });
    }

    /**
     * Update store information.
     *
     * @param User $store
     * @param array $data
     * @return User
     */
    public function update(User $store, array $data): User
    {
        if (!$store->isStore()) {
            throw new \Exception('User is not a store');
        }

        $allowedFields = ['name', 'email', 'phone', 'status', 'profile_photo_path', 'password'];
        $updateData = array_intersect_key($data, array_flip($allowedFields));

        // Hash password if provided and not empty
        if (isset($updateData['password']) && !empty($updateData['password'])) {
            $updateData['password'] = Hash::make($updateData['password']);
        } elseif (isset($updateData['password']) && empty($updateData['password'])) {
            // Remove password from update if it's empty (don't update password)
            unset($updateData['password']);
        }
        // dd( $updateData);

        $store->update($updateData);

        return $store->fresh();
    }

    /**
     * Update store status.
     *
     * @param User $store
     * @param string $status
     * @return bool
     */
    public function updateStatus(User $store, string $status): bool
    {
        if (!$store->isStore()) {
            return false;
        }

        if (!in_array($status, ['active', 'suspended'])) {
            return false;
        }

        return $store->update(['status' => $status]);
    }
}

