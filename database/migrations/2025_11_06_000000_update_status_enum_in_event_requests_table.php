<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ensure the status ENUM on MySQL/MariaDB includes all current lifecycle values.
     */
    public function up(): void
    {
        if (! Schema::hasTable('event_requests') || ! Schema::hasColumn('event_requests', 'status')) {
            return; // Table/column missing (unlikely) – nothing to do.
        }

        $driver = config('database.default');
        $connection = config("database.connections.$driver.driver");
        if (! in_array($connection, ['mysql','mariadb'], true)) {
            // Non-ENUM backends (e.g. sqlite, pgsql with TEXT) don't need alteration.
            return;
        }

        // Desired full set of statuses reflected in App\Enums\EventRequestStatus
        $desired = [
            'pending',
            'approved', // legacy retained for historical records
            'declined',
            'awaiting_payment',
            'paid',
            'expired',
        ];

        // Inspect current ENUM definition; skip if it already matches (contains all desired values).
        $column = DB::selectOne("SHOW COLUMNS FROM event_requests WHERE Field = 'status'");
        if ($column && isset($column->Type) && str_starts_with($column->Type, 'enum(')) {
            $raw = $column->Type; // e.g. enum('pending','approved','declined')
            preg_match_all("/'([^']+)'/", $raw, $matches);
            $current = $matches[1] ?? [];
            $missing = array_diff($desired, $current);
            if (count($missing) === 0 && count($current) === count($desired)) {
                return; // Already up-to-date.
            }
        }

        $enumList = "'" . implode("','", $desired) . "'";
        DB::statement("ALTER TABLE event_requests MODIFY COLUMN status ENUM($enumList) NOT NULL DEFAULT 'pending'");
    }

    /**
     * Revert to the pre-expiration minimal set (excluding awaiting_payment/paid/expired).
     */
    public function down(): void
    {
        if (! Schema::hasTable('event_requests') || ! Schema::hasColumn('event_requests', 'status')) {
            return;
        }
        $driver = config('database.default');
        $connection = config("database.connections.$driver.driver");
        if (! in_array($connection, ['mysql','mariadb'], true)) {
            return;
        }

        DB::statement("ALTER TABLE event_requests MODIFY COLUMN status ENUM('pending','approved','declined') NOT NULL DEFAULT 'pending'");
    }
};
