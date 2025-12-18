<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Create Notifications table
 * 
 * This migration creates the notifications table to store all push notifications
 * sent to users. Notifications are stored for history and to track read/unread status.
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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('type'); // transaction_received, transaction_sent, payment_received, payment_sent, etc.
            $table->string('title'); // Notification title in Arabic
            $table->text('body'); // Notification body in Arabic
            $table->json('data')->nullable(); // Additional data (transaction_id, amount, etc.)
            $table->boolean('read')->default(false); // Read status
            $table->timestamp('read_at')->nullable(); // When notification was read
            $table->timestamps();
            
            // Indexes for better query performance
            $table->index('user_id');
            $table->index('read');
            $table->index('type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     * 
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};

