<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('event_requests', function (Blueprint $table) {
            // Only add if not exists (Laravel doesn't have native conditional index check; rely on naming convention)
            $table->index(['status','event_id'], 'event_requests_status_event_id_index');
        });
    }

    public function down(): void
    {
        Schema::table('event_requests', function (Blueprint $table) {
            $table->dropIndex('event_requests_status_event_id_index');
        });
    }
};
