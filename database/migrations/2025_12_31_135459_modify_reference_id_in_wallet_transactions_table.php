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
     * Changes reference_id from UUID (36 chars) to VARCHAR(255) to support longer reference IDs
     * like prize distribution reference IDs.
     */
    public function up(): void
    {
        // Drop the unique index first
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->dropUnique(['reference_id']);
        });

        // Change column type from UUID to VARCHAR(255)
        DB::statement('ALTER TABLE `wallet_transactions` MODIFY COLUMN `reference_id` VARCHAR(255) NOT NULL');

        // Re-add the unique index
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->unique('reference_id');
        });
    }

    /**
     * Reverse the migrations.
     * 
     * Note: This will fail if there are any reference_ids longer than 36 characters.
     */
    public function down(): void
    {
        // Check if there are any reference_ids longer than 36 characters
        $longRefs = DB::table('wallet_transactions')
            ->whereRaw('CHAR_LENGTH(reference_id) > 36')
            ->count();
        
        if ($longRefs > 0) {
            throw new \Exception('Cannot revert: There are ' . $longRefs . ' transactions with reference_id longer than 36 characters. Please update or delete them first.');
        }

        // Drop the unique index
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->dropUnique(['reference_id']);
        });

        // Change column type back to UUID
        DB::statement('ALTER TABLE `wallet_transactions` MODIFY COLUMN `reference_id` CHAR(36) NOT NULL');

        // Re-add the unique index
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->unique('reference_id');
        });
    }
};
