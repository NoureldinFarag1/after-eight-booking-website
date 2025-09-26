<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('event_requests')) {
            Schema::table('event_requests', function (Blueprint $table) {
                if (!Schema::hasColumn('event_requests','user_id')) return; // safety
                // Add unique index if not already present
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $indexes = $sm->listTableIndexes('event_requests');
                if (!array_key_exists('event_requests_user_id_event_id_unique', $indexes)) {
                    $table->unique(['user_id','event_id']);
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('event_requests')) {
            Schema::table('event_requests', function (Blueprint $table) {
                $table->dropUnique(['user_id','event_id']);
            });
        }
    }
};
