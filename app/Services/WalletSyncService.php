<?php

namespace App\Services;

use App\Models\User;
use App\Models\WalletSyncLog;
use App\Services\UserService;
use App\Services\WalletService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * WalletSyncService
 * 
 * Handles synchronization of users from Wallet System.
 * Fetches users in batches to avoid server load.
 * Tracks last sync date to only fetch new/updated users.
 * 
 * @package App\Services
 */
class WalletSyncService
{
    protected UserService $userService;
    protected WalletService $walletService;
    protected string $walletApiUrl;
    protected string $walletApiKey;
    protected int $batchSize = 100; // Process 100 users at a time

    public function __construct(UserService $userService, WalletService $walletService)
    {
        $this->userService = $userService;
        $this->walletService = $walletService;
        $this->walletApiUrl = config('wallet.api_url', env('WALLET_API_URL', 'http://46.62.241.66'));
        $this->walletApiKey = config('wallet.api_key', env('WALLET_API_KEY', 'mdn-wallet-9088db076fcd7f0fad256821f8a3c3c9cba4cdcab915559e7c894d5c4e7bc564'));
    }

    /**
     * Sync users from Wallet System.
     * 
     * Fetches users based on last sync date to avoid duplicates.
     * Processes in batches to prevent server overload.
     * 
     * @param bool $forceFullSync Force full sync (ignore last sync date)
     * @return array Sync results
     */
    public function syncUsers(bool $forceFullSync = false): array
    {
        $log = WalletSyncLog::create([
            'status' => 'success',
            'users_fetched' => 0,
            'users_created' => 0,
            'users_updated' => 0,
            'users_failed' => 0,
        ]);

        try {
            // Get last sync date
            $lastSyncDate = null;
            if (!$forceFullSync) {
                $lastSync = WalletSyncLog::where('status', 'success')
                    ->whereNotNull('last_sync_date')
                    ->orderBy('last_sync_at', 'desc')
                    ->first();
                
                if ($lastSync && $lastSync->last_sync_date) {
                    // Convert to YYYY-MM-DD format
                    $lastSyncDate = $lastSync->last_sync_date->format('Y-m-d');
                }
            }

            $offset = 0;
            $totalFetched = 0;
            $totalCreated = 0;
            $totalUpdated = 0;
            $totalFailed = 0;
            $hasMore = true;

            while ($hasMore) {
                // Fetch users from Wallet System
                $response = $this->fetchUsersFromWallet($lastSyncDate, $offset, $this->batchSize);

                if (!$response['success']) {
                    throw new \Exception($response['error']);
                }

                $users = $response['users'];
                $hasMore = $response['has_more'] ?? false;

                if (empty($users)) {
                    break;
                }

                // Process batch
                $batchResults = $this->processUsersBatch($users);
                
                $totalFetched += count($users);
                $totalCreated += $batchResults['created'];
                $totalUpdated += $batchResults['updated'];
                $totalFailed += $batchResults['failed'];

                $offset += count($users);

                // Small delay to prevent server overload
                if ($hasMore) {
                    usleep(500000); // 0.5 seconds
                }
            }

            // Update log
            $log->update([
                'last_sync_at' => now(),
                'last_sync_date' => now()->toDateString(),
                'users_fetched' => $totalFetched,
                'users_created' => $totalCreated,
                'users_updated' => $totalUpdated,
                'users_failed' => $totalFailed,
                'status' => $totalFailed > 0 ? 'partial' : 'success',
                'metadata' => [
                    'offset' => $offset,
                    'last_sync_date' => $lastSyncDate,
                ],
            ]);

            return [
                'success' => true,
                'users_fetched' => $totalFetched,
                'users_created' => $totalCreated,
                'users_updated' => $totalUpdated,
                'users_failed' => $totalFailed,
            ];

        } catch (\Exception $e) {
            $log->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            Log::error('Wallet sync failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Fetch users from Wallet System API.
     * 
     * @param string|null $since Date to fetch users from (YYYY-MM-DD)
     * @param int $offset Pagination offset
     * @param int $count Number of users to fetch
     * @return array
     */
    protected function fetchUsersFromWallet(?string $since, int $offset, int $count): array
    {
        try {
            $url = rtrim($this->walletApiUrl, '/') . '/api/sync/users/';
            
            $params = [
                'offset' => $offset,
                'count' => $count,
            ];

            // If we have a last sync date, use it
            if ($since) {
                $params['since'] = $since;
            }

            $response = Http::timeout(30)
                ->withHeaders([
                    'X-API-Key' => $this->walletApiKey,
                    'Accept' => 'application/json',
                ])
                ->get($url, $params);

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'error' => 'API request failed: ' . $response->status() . ' - ' . $response->body(),
                ];
            }

            $data = $response->json();

            return [
                'success' => true,
                'users' => $data['users'] ?? [],
                'has_more' => $data['has_more'] ?? false,
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to fetch users: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Process a batch of users.
     * 
     * @param array $users Array of user data from Wallet System
     * @return array
     */
    protected function processUsersBatch(array $users): array
    {
        $created = 0;
        $updated = 0;
        $failed = 0;

        foreach ($users as $userData) {
            try {
                DB::beginTransaction();

                // Find or create user
                $user = $this->findOrCreateUser($userData);

                // Update user data if needed
                $wasUpdated = $this->updateUserData($user, $userData);

                // Ensure wallet exists
                if (!$user->hasWallet()) {
                    $this->walletService->createWallet($user);
                }

                DB::commit();

                if ($user->wasRecentlyCreated) {
                    $created++;
                } elseif ($wasUpdated) {
                    $updated++;
                }

            } catch (\Exception $e) {
                DB::rollBack();
                $failed++;

                Log::error('Failed to sync user from Wallet System', [
                    'user_data' => $userData,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return [
            'created' => $created,
            'updated' => $updated,
            'failed' => $failed,
        ];
    }

    /**
     * Find existing user or create new one.
     * 
     * @param array $userData User data from Wallet System
     * @return User
     */
    protected function findOrCreateUser(array $userData): User
    {
        // Use ONLY the 'id' from Wallet System (not external_refs from response)
        // This 'id' will be stored as string directly in external_refs (not in external_refs->wallet)
        $walletUserId = $userData['id'] ?? null;
        
        // Try to find by external_refs first (using Wallet System ID as string)
        if ($walletUserId) {
            $walletUserIdStr = (string)$walletUserId;
            $user = User::where('external_refs', $walletUserIdStr)->first();
            if ($user) {
                return $user;
            }
        }

        // Try to find by email
        if (!empty($userData['email'])) {
            $user = User::where('email', $userData['email'])->first();
            if ($user) {
                // Update external_refs with Wallet System ID (as string directly)
                $user->update(['external_refs' => (string)$walletUserId]);
                return $user;
            }
        }

        // Try to find by phone
        if (!empty($userData['phone'])) {
            $user = User::where('phone', $userData['phone'])->first();
            if ($user) {
                // Update external_refs with Wallet System ID (as string directly)
                $user->update(['external_refs' => (string)$walletUserId]);
                return $user;
            }
        }

        // Create new user
        // Store ONLY Wallet System ID (from 'id' field) as string directly in external_refs
        $externalRefs = $walletUserId ? (string)$walletUserId : null;

        return User::create([
            'name' => $userData['name'] ?? 'User',
            'email' => $userData['email'] ?? null,
            'phone' => $userData['phone'] ?? null,
            'type' => 'user',
            'status' => $userData['is_active'] ?? true ? 'active' : 'suspended',
            'external_refs' => $externalRefs,
        ]);
    }

    /**
     * Update user data if needed.
     * 
     * @param User $user
     * @param array $userData
     * @return bool True if user was updated
     */
    protected function updateUserData(User $user, array $userData): bool
    {
        $updated = false;
        $updateData = [];

        // Update name if different
        if (isset($userData['name']) && $user->name !== $userData['name']) {
            $updateData['name'] = $userData['name'];
            $updated = true;
        }

        // Update email if different and not already set
        if (isset($userData['email']) && $user->email !== $userData['email']) {
            // Only update if current email is null or different
            if ($user->email === null || $user->email !== $userData['email']) {
                $updateData['email'] = $userData['email'];
                $updated = true;
            }
        }

        // Update phone if different and not already set
        if (isset($userData['phone']) && $user->phone !== $userData['phone']) {
            // Only update if current phone is null or different
            if ($user->phone === null || $user->phone !== $userData['phone']) {
                $updateData['phone'] = $userData['phone'];
                $updated = true;
            }
        }

        // Update status
        $isActive = $userData['is_active'] ?? true;
        $newStatus = $isActive ? 'active' : 'suspended';
        if ($user->status !== $newStatus) {
            $updateData['status'] = $newStatus;
            $updated = true;
        }

        // Update external_refs with Wallet System ID (as string directly)
        $walletUserId = $userData['id'] ?? null;
        if ($walletUserId) {
            $walletUserIdStr = (string)$walletUserId;
            $currentExternalRefs = $user->external_refs;
            if ($currentExternalRefs !== $walletUserIdStr) {
                $updateData['external_refs'] = $walletUserIdStr;
                $updated = true;
            }
        }

        if ($updated) {
            $user->update($updateData);
        }

        return $updated;
    }
}

