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
        Schema::create('prize_distributions', function (Blueprint $table) {
            $table->id();
            $table->integer('event_id')->unique()->comment('Midan event ID - unique to prevent duplicates');
            $table->string('event_type')->comment('nomination or ugc');
            $table->string('event_subtype')->nullable()->comment('UGC subtype format: {position}_W{amount}');
            $table->json('event_meta')->comment('Event metadata: title, title_ar, date');
            $table->string('reference_id')->unique()->comment('Internal reference ID for this distribution');
            $table->integer('processed_count')->default(0);
            $table->integer('failed_count')->default(0);
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamps();
            
            $table->index('event_id');
            $table->index('event_type');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prize_distributions');
    }
};
