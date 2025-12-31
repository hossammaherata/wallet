<?php

namespace Database\Seeders;

use App\Models\WalletConfiguration;
use Illuminate\Database\Seeder;

class WalletConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if configuration already exists
        $configuration = WalletConfiguration::latest()->first();

        if (!$configuration) {
            WalletConfiguration::create([
                'transfer_fee_percentage' => 0,
                'withdrawal_fee_percentage' => 0,
            ]);

            $this->command->info('Wallet configuration created successfully!');
        } else {
            $this->command->warn('Wallet configuration already exists!');
        }
    }
}
