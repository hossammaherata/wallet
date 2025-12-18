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
        // Add phone column (unique)
     
        
        // Add status column first
        Schema::table('users', function (Blueprint $table) {
            $table->enum('status', ['active', 'suspended'])->default('active');
        });
        
        // Add the new type enum column
        Schema::table('users', function (Blueprint $table) {
            $table->enum('type', ['user', 'store', 'admin'])->nullable();
        });
        
    
        
        // Set default for null values
        DB::table('users')->whereNull('type')->update(['type' => 'user']);
        
        // Make type not nullable and set default
        Schema::table('users', function (Blueprint $table) {
            $table->enum('type', ['user', 'store', 'admin'])->default('user')->change();
        });
        
   
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([ 'type', 'status']);
           
        });
    }
};

