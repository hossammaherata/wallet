<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modify the type enum to include 'prize_manager'
        DB::statement("ALTER TABLE users MODIFY COLUMN type ENUM('user', 'store', 'admin', 'prize_manager') NOT NULL DEFAULT 'user'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove prize_manager from enum
        DB::statement("ALTER TABLE users MODIFY COLUMN type ENUM('user', 'store', 'admin') NOT NULL DEFAULT 'user'");
    }
};
