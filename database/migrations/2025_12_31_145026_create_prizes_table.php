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
        Schema::create('prizes', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('اسم الجائزة');
            $table->json('dates')->comment('التواريخ المتاحة للجائزة');
            $table->integer('total_winners')->comment('عدد الفائزين الإجمالي');
            $table->decimal('total_amount', 15, 2)->comment('مجموع الجوائز الإجمالي');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade')->comment('الأدمن الذي أنشأ الجائزة');
            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active');
            $table->timestamps();
            
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prizes');
    }
};
