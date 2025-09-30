<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            // Only add sender_id if it doesn't exist
            if (!Schema::hasColumn('invitations', 'sender_id')) {
                $table->foreignId('sender_id')
                      ->after('event_id')
                      ->constrained('users')
                      ->onDelete('cascade');
            }
        });
    }

    public function down(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            if (Schema::hasColumn('invitations', 'sender_id')) {
                $table->dropForeign(['sender_id']);
                $table->dropColumn('sender_id');
            }
        });
    }
};
