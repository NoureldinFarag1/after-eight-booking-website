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
        // Convert any existing 'other' gender values to null
        // since we're removing the 'other' option
        DB::table('users')
            ->where('gender', 'other')
            ->update(['gender' => null]);

        // Now update the enum to only include male and female
        DB::statement("ALTER TABLE users MODIFY COLUMN gender ENUM('male', 'female') NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore the enum to include 'other' option
        DB::statement("ALTER TABLE users MODIFY COLUMN gender ENUM('male', 'female', 'other') NULL");
    }
};
