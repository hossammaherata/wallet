<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('wallet_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->timestamp('last_sync_at')->nullable()->comment('Last successful sync timestamp');
            $table->date('last_sync_date')->nullable()->comment('Last successful sync date (for querying)');
            $table->integer('users_fetched')->default(0)->comment('Number of users fetched in last sync');
            $table->integer('users_created')->default(0)->comment('Number of new users created');
            $table->integer('users_updated')->default(0)->comment('Number of existing users updated');
            $table->integer('users_failed')->default(0)->comment('Number of users that failed to sync');
            $table->enum('status', ['success', 'failed', 'partial'])->default('success');
            $table->text('error_message')->nullable();
            $table->json('metadata')->nullable()->comment('Additional sync metadata');
            $table->timestamps();
            
            $table->index('last_sync_at');
            $table->index('last_sync_date');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_sync_logs');
    }
};
