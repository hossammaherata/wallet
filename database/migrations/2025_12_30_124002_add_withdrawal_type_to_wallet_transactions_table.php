<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adds 'withdrawal' to the enum type column without losing existing data.
     */
    public function up(): void
    {
        // Modify enum to include 'withdrawal' without losing data
        // MySQL requires ALTER TABLE to modify enum values
        DB::statement("ALTER TABLE `wallet_transactions` MODIFY COLUMN `type` ENUM('credit', 'debit', 'transfer', 'purchase', 'refund', 'withdrawal') NOT NULL");
    }

    /**
     * Reverse the migrations.
     * 
     * Removes 'withdrawal' from the enum type column.
     * Note: This will fail if there are any transactions with type='withdrawal'
     */
    public function down(): void
    {
        // Check if there are any withdrawal transactions before removing the enum value
        $withdrawalCount = DB::table('wallet_transactions')
            ->where('type', 'withdrawal')
            ->count();
        
        if ($withdrawalCount > 0) {
            throw new \Exception('Cannot remove withdrawal type: There are ' . $withdrawalCount . ' transactions with type=withdrawal. Please delete or update them first.');
        }
        
        // Remove 'withdrawal' from enum
        DB::statement("ALTER TABLE `wallet_transactions` MODIFY COLUMN `type` ENUM('credit', 'debit', 'transfer', 'purchase', 'refund') NOT NULL");
    }
};
