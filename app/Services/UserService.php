<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

/**
 * UserService
 * 
 * Handles all user-related business logic including:
 * - User registration (creates wallet automatically)
 * - User authentication (by phone or email)
 * - Profile updates
 * - Status management (active/suspended)
 * 
 * All operations use database transactions for data integrity.
 * 
 * @package App\Services
 */
class UserService
{
    /**
     * WalletService instance for creating wallets.
     * 
     * @var WalletService
     */
    protected WalletService $walletService;

    /**
     * Create a new UserService instance.
     * 
     * @param WalletService $walletService
     */
    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Register a new user.
     * 
     * Only creates regular users (type='user').
     * This method is used by the mobile API and always creates type='user'.
     * Stores and admins must be created through the admin panel.
     *
     * @param array $data
     * @return User
     */
    public function register(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'password' => null, // No password for regular users
                'type' => 'user', // Always 'user' for API registration
                'status' => $data['status'] ?? 'active', // Allow status from admin panel, default to 'active' for API
            ]);

            // Create wallet for the user
            $this->walletService->createWallet($user);

            return $user;
        });
    }

    /**
     * Authenticate user by phone/email (no password required for regular users).
     * 
     * Only allows authentication for 'user' and 'store' types.
     * Admins cannot authenticate through this method (API only).
     * Regular users (type='user') don't need password, stores still require password.
     *
     * @param string $identifier Phone or email
     * @param string|null $password Password (required for stores, not for regular users)
     * @return User|null
     */
    public function authenticate(string $identifier, ?string $password = null): ?User
    {
        $field = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
        
        $user = User::where($field, $identifier)->first();

        if (!$user) {
            return null;
        }

        // For stores, password is still required
        if ($user->isStore()) {
            if (!$password || !Hash::check($password, $user->password)) {
                return null;
            }
        }
        // For regular users, no password check needed

        // Only allow 'user' and 'store' types to authenticate via API
        if ($user->isAdmin()) {
            return null;
        }

        if (!$user->isActive()) {
            return null;
        }

        return $user;
    }

    /**
     * Update user profile.
     *
     * @param User $user
     * @param array $data
     * @return User
     */
    public function updateProfile(User $user, array $data): User
    {
        $allowedFields = ['name', 'email', 'phone', 'status'];
        $updateData = array_intersect_key($data, array_flip($allowedFields));

        $user->update($updateData);

        // If user is suspended, revoke all tokens to force logout
        if (isset($updateData['status']) && $updateData['status'] === 'suspended') {
            $user->tokens()->delete();
        }

        return $user->fresh();
    }

    /**
     * Update user status.
     *
     * @param User $user
     * @param string $status
     * @return bool
     */
    public function updateStatus(User $user, string $status): bool
    {
        if (!in_array($status, ['active', 'suspended'])) {
            return false;
        }

        return $user->update(['status' => $status]);
    }
}


