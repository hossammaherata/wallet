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
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_wallet_id')->nullable()->constrained('wallets')->onDelete('set null');
            $table->foreignId('to_wallet_id')->nullable()->constrained('wallets')->onDelete('set null');
            $table->decimal('amount', 15, 2);
            $table->enum('type', ['credit', 'debit', 'transfer', 'purchase', 'refund']);
            $table->enum('status', ['pending', 'success', 'failed'])->default('pending');
            $table->uuid('reference_id')->unique();
            $table->json('meta')->nullable();
            $table->timestamps();
            
            // Indexes for better query performance
            $table->index('from_wallet_id');
            $table->index('to_wallet_id');
            $table->index('reference_id');
            $table->index('status');
            $table->index('created_at');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};

