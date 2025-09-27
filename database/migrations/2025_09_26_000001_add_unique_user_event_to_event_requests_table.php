<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('event_requests')) {
            Schema::table('event_requests', function (Blueprint $table) {
                // Add unique index directly
                $table->unique(['user_id', 'event_id'], 'user_event_unique');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('event_requests')) {
            Schema::table('event_requests', function (Blueprint $table) {
                $table->dropUnique('user_event_unique');
            });
        }
    }
};