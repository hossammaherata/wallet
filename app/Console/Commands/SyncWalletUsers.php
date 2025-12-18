<?php

namespace App\Console\Commands;

use App\Services\WalletSyncService;
use Illuminate\Console\Command;

/**
 * SyncWalletUsers Command
 * 
 * Syncs users from Wallet System.
 * Should be run via cron job (e.g., every hour).
 * 
 * @package App\Console\Commands
 */
class SyncWalletUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wallet:sync-users {--force : Force full sync (ignore last sync date)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync users from Wallet System';

    protected WalletSyncService $syncService;

    public function __construct(WalletSyncService $syncService)
    {
        parent::__construct();
        $this->syncService = $syncService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting wallet users sync...');

        $forceFullSync = $this->option('force');
        
        if ($forceFullSync) {
            $this->warn('Force full sync enabled - will fetch all users');
        }

        $result = $this->syncService->syncUsers($forceFullSync);

        if ($result['success']) {
            $this->info('Sync completed successfully!');
            $this->table(
                ['Metric', 'Count'],
                [
                    ['Users Fetched', $result['users_fetched']],
                    ['Users Created', $result['users_created']],
                    ['Users Updated', $result['users_updated']],
                    ['Users Failed', $result['users_failed']],
                ]
            );
        } else {
            $this->error('Sync failed: ' . ($result['error'] ?? 'Unknown error'));
            return 1;
        }

        return 0;
    }
}
