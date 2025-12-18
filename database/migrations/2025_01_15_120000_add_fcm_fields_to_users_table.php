<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Add FCM Token and Firebase Enabled fields to Users table
 * 
 * This migration adds:
 * - fcm_token: Firebase Cloud Messaging token for push notifications
 * - firebase_enabled: Boolean flag to enable/disable Firebase notifications
 * 
 * @package Database\Migrations
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
        if (Schema::hasTable('users')) {
            
            Schema::table('users', function (Blueprint $table) {
                $table->string('phone')->unique()->nullable()->after('email');
                if (!Schema::hasColumn('users', 'fcm_token')) {
                    $table->text('fcm_token')->nullable()->after('phone');
                }
                if (!Schema::hasColumn('users', 'firebase_enabled')) {
                    $table->boolean('firebase_enabled')->default(true)->after('fcm_token');
                }
      

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
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('phone');

                if (Schema::hasColumn('users', 'firebase_enabled')) {
                    $table->dropColumn('firebase_enabled');
                }
                if (Schema::hasColumn('users', 'fcm_token')) {
                    $table->dropColumn('fcm_token');
                }
            });

        }
    }
};

