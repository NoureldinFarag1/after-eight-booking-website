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
        if (!Schema::hasTable('invitations')) {
            return;
        }
        Schema::table('invitations', function (Blueprint $table) {
            $table->string('token')->unique()->nullable()->after('status');
            $table->string('qr_code_path')->nullable()->after('token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('invitations')) {
            return;
        }
        Schema::table('invitations', function (Blueprint $table) {
            $table->dropColumn(['token', 'qr_code_path']);
        });
    }
};
