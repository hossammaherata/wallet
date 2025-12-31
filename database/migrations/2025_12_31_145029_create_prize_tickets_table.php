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
        Schema::create('prize_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prize_id')->constrained('prizes')->onDelete('cascade');
            $table->date('date')->comment('تاريخ القسيمة');
            $table->integer('total_winners')->comment('عدد الفائزين لهذا اليوم');
            $table->decimal('total_amount', 15, 2)->comment('مجموع الجوائز لهذا اليوم');
            $table->decimal('remaining_amount', 15, 2)->comment('المبلغ المتبقي');
            $table->integer('current_winners_count')->default(0)->comment('عدد الفائزين الحالي');
            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active');
            $table->timestamps();
            
            $table->index('prize_id');
            $table->index('date');
            $table->index('status');
            $table->unique(['prize_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prize_tickets');
    }
};
