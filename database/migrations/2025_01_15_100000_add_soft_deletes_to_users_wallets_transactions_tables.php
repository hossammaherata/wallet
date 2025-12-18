<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Add SoftDeletes to Users, Wallets, and WalletTransactions tables
 * 
 * This migration adds the `deleted_at` column to enable soft deletes for:
 * - users: Allows recovery of deleted user accounts
 * - wallets: Preserves transaction history even if wallet is deleted
 * - wallet_transactions: Maintains audit trail for financial records
 * 
 * Soft deletes allow records to be "deleted" without actually removing them
 * from the database, enabling data recovery and maintaining referential integrity.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * @return void
     */
    public function up(): void
    {
        // Add soft deletes to users table
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'deleted_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->softDeletes()->after('updated_at');
            });
        }

        // Add soft deletes to wallets table
        if (Schema::hasTable('wallets') && !Schema::hasColumn('wallets', 'deleted_at')) {
            Schema::table('wallets', function (Blueprint $table) {
                $table->softDeletes()->after('updated_at');
            });
        }

        // Add soft deletes to wallet_transactions table
        if (Schema::hasTable('wallet_transactions') && !Schema::hasColumn('wallet_transactions', 'deleted_at')) {
            Schema::table('wallet_transactions', function (Blueprint $table) {
                $table->softDeletes()->after('updated_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     * 
     * @return void
     */
    public function down(): void
    {
        // Remove soft deletes from wallet_transactions table
        if (Schema::hasTable('wallet_transactions') && Schema::hasColumn('wallet_transactions', 'deleted_at')) {
            Schema::table('wallet_transactions', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        // Remove soft deletes from wallets table
        if (Schema::hasTable('wallets') && Schema::hasColumn('wallets', 'deleted_at')) {
            Schema::table('wallets', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        // Remove soft deletes from users table
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'deleted_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};

