<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (!Schema::hasColumn('events', 'initial_capacity')) {
                $table->unsignedInteger('initial_capacity')->nullable()->after('capacity');
            }
        });
        // Backfill existing rows
        DB::statement('UPDATE events SET initial_capacity = capacity WHERE initial_capacity IS NULL');
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (Schema::hasColumn('events', 'initial_capacity')) {
                $table->dropColumn('initial_capacity');
            }
        });
    }
};
