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
        Schema::create('wallet_configurations', function (Blueprint $table) {
            $table->id();
            $table->decimal('transfer_fee_percentage', 5, 2)->default(0)->comment('نسبة رسوم التحويل لصديق (0-100)');
            $table->decimal('withdrawal_fee_percentage', 5, 2)->default(0)->comment('نسبة رسوم السحب البنكي (0-100)');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_configurations');
    }
};
