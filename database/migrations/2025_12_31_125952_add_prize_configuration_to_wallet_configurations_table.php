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
        Schema::table('wallet_configurations', function (Blueprint $table) {
            $table->json('ugc_prizes')->nullable()->after('withdrawal_fee_percentage')->comment('جوائز UGC: [position_1, position_2, position_3]');
            $table->json('nomination_prizes')->nullable()->after('ugc_prizes')->comment('جوائز Nomination: {attendance_fan: [pos1-5], online_fan: [pos1-5]}');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wallet_configurations', function (Blueprint $table) {
            $table->dropColumn(['ugc_prizes', 'nomination_prizes']);
        });
    }
};
