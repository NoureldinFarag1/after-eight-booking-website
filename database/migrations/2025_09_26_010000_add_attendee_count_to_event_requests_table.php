<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('event_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('event_requests', 'attendee_count')) {
                $table->unsignedInteger('attendee_count')->default(0)->after('guests');
            }
        });
    }

    public function down(): void
    {
        Schema::table('event_requests', function (Blueprint $table) {
            if (Schema::hasColumn('event_requests', 'attendee_count')) {
                $table->dropColumn('attendee_count');
            }
        });
    }
};
