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
            // Add QR code field like tickets
            $table->string('qr_code')->unique()->nullable()->after('token');

            // Update status enum to match ticket-like statuses
            $table->enum('qr_status', ['valid', 'used', 'cancelled'])->default('valid')->after('qr_code');
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
            $table->dropColumn(['qr_code', 'qr_status']);
        });
    }
};
