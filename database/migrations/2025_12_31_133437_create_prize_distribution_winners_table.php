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
        Schema::create('prize_distribution_winners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prize_distribution_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('midan_user_id')->comment('Midan user ID from the request');
            $table->integer('position')->comment('Winner position (1-5)');
            $table->string('category')->comment('attendance_fan, online_fan, or ugc_creator');
            $table->decimal('prize_amount', 10, 2)->comment('Prize amount awarded');
            $table->integer('points_scored')->default(0)->comment('Points scored by the winner');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->foreignId('transaction_id')->nullable()->constrained('wallet_transactions')->onDelete('set null');
            $table->string('status')->default('success')->comment('success or failed');
            $table->text('error_message')->nullable();
            $table->timestamps();
            
            $table->index('prize_distribution_id');
            $table->index('user_id');
            $table->index('midan_user_id');
            $table->index('position');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prize_distribution_winners');
    }
};
