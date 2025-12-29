<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('event_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('event_requests', 'primary_ticket_type_id')) {
                $table->foreignId('primary_ticket_type_id')
                    ->nullable()
                    ->after('primary_social_url')
                    ->constrained('ticket_types')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('event_requests', function (Blueprint $table) {
            if (Schema::hasColumn('event_requests', 'primary_ticket_type_id')) {
                $table->dropConstrainedForeignId('primary_ticket_type_id');
            }
        });
    }
};
