<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('invitations')) {
            return;
        }

        // 1. Add the column if it does not exist (nullable first so existing rows don't break)
        if (!Schema::hasColumn('invitations', 'event_id')) {
            Schema::table('invitations', function (Blueprint $table) {
                $table->unsignedBigInteger('event_id')->nullable()->after('id');
            });
        }

        // 2. Clean any rows whose event_id points to a non-existent event (set to null)
        try {
            DB::statement(<<<SQL
UPDATE invitations i
LEFT JOIN events e ON e.id = i.event_id
SET i.event_id = NULL
WHERE i.event_id IS NOT NULL AND e.id IS NULL;
SQL);
        } catch (\Throwable $e) {
            // ignore, best-effort cleanup
        }

        // 3. Attempt to add the foreign key if it is not already present and all non-null values are valid.
        // Check if a FK with the expected name exists
        $fkExists = DB::selectOne(<<<SQL
SELECT 1 FROM information_schema.TABLE_CONSTRAINTS
WHERE CONSTRAINT_SCHEMA = DATABASE()
  AND TABLE_NAME = 'invitations'
  AND CONSTRAINT_NAME = 'invitations_event_id_foreign'
  AND CONSTRAINT_TYPE = 'FOREIGN KEY'
LIMIT 1;
SQL);

        if (!$fkExists) {
            // Ensure there are no orphan non-null event_ids before adding FK
            $orphans = DB::selectOne(<<<SQL
SELECT 1 orphan_exists FROM invitations i
LEFT JOIN events e ON e.id = i.event_id
WHERE i.event_id IS NOT NULL AND e.id IS NULL
LIMIT 1;
SQL);
            if (!$orphans) {
                try {
                    Schema::table('invitations', function (Blueprint $table) {
                        // Make column not nullable only after constraint if desired; keep nullable for flexibility
                        $table->foreign('event_id')
                              ->references('id')->on('events')
                              ->onDelete('cascade');
                    });
                } catch (\Throwable $e) {
                    // If this fails we leave the column nullable and without FK.
                }
            }
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('invitations')) {
            return;
        }
        Schema::table('invitations', function (Blueprint $table) {
            // Drop FK if exists
            try {
                $table->dropForeign(['event_id']);
            } catch (\Throwable $e) {
                // ignore
            }
            if (Schema::hasColumn('invitations', 'event_id')) {
                $table->dropColumn('event_id');
            }
        });
    }
};
