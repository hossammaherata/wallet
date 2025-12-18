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
        Schema::create('external_balance_updates', function (Blueprint $table) {
            $table->id();
            $table->string('reference_id')->unique();
            $table->json('balances'); // Store the balances data: {user_id => balance}
            $table->integer('processed_count')->default(0); // Number of successfully processed balances
            $table->integer('failed_count')->default(0); // Number of failed updates
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamps();
            
            // Indexes for better query performance
            $table->index('reference_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('external_balance_updates');
    }
};

