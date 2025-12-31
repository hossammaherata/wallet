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
                'ugc_prizes' => [0, 0, 0], // Default: 0 points for positions 1, 2, 3
                'nomination_prizes' => [
                    'attendance_fan' => [0, 0, 0, 0, 0], // Default: 0 points for positions 1-5
                    'online_fan' => [0, 0, 0, 0, 0], // Default: 0 points for positions 1-5
                ],
            ]);

            $this->command->info('Wallet configuration created successfully!');
        } else {
            // Update existing configuration if prize fields are missing
            if (is_null($configuration->ugc_prizes) || is_null($configuration->nomination_prizes)) {
                $configuration->update([
                    'ugc_prizes' => $configuration->ugc_prizes ?? [0, 0, 0],
                    'nomination_prizes' => $configuration->nomination_prizes ?? [
                        'attendance_fan' => [0, 0, 0, 0, 0],
                        'online_fan' => [0, 0, 0, 0, 0],
                    ],
                ]);
                $this->command->info('Wallet configuration updated with prize fields!');
            } else {
                $this->command->warn('Wallet configuration already exists!');
            }
        }
    }
}
