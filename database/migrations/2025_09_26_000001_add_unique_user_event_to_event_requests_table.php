<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('event_requests')) {
            return; // Nothing to do
        }

        // 1. Remove duplicate (user_id,event_id) pairs keeping the lowest id (earliest record)
        // This raw delete joins the table to a derived list of duplicates and deletes all but one per pair.
        DB::statement(<<<SQL
DELETE er FROM event_requests er
JOIN (
    SELECT MIN(id) AS keep_id, user_id, event_id
    FROM event_requests
    GROUP BY user_id, event_id
    HAVING COUNT(*) > 1
) d ON d.user_id = er.user_id AND d.event_id = er.event_id
WHERE er.id <> d.keep_id;
SQL);

        // 2. Drop the existing non-unique composite index if it exists (Laravel default naming convention)
        // MySQL will error if we try to drop a non-existing index, so wrap in try/catch.
        try {
            DB::statement('ALTER TABLE event_requests DROP INDEX event_requests_event_id_user_id_index');
        } catch (\Throwable $e) {
            // Index might not exist; ignore.
        }

        // 3. Add the unique composite index (idempotent-ish: guard against re-adding if it already exists)
        // MySQL 8 doesn't have a straightforward portable "IF NOT EXISTS" for ADD UNIQUE, so we defensively check via SHOW INDEX.
        $alreadyExists = DB::selectOne(<<<SQL
            SELECT 1 FROM information_schema.statistics
            WHERE table_schema = DATABASE()
              AND table_name = 'event_requests'
              AND index_name = 'user_event_unique'
            LIMIT 1
        SQL);

        if (!$alreadyExists) {
            Schema::table('event_requests', function (Blueprint $table) {
                $table->unique(['user_id', 'event_id'], 'user_event_unique');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('event_requests')) {
            return;
        }
        // Drop unique index if present
        try {
            Schema::table('event_requests', function (Blueprint $table) {
                $table->dropUnique('user_event_unique');
            });
        } catch (\Throwable $e) {
            // Ignore if already gone.
        }

        // (Optional) We do not recreate the old non-unique index; likely unnecessary once uniqueness enforced.
    }
};
