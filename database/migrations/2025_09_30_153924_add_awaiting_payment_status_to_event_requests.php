<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('event_requests')) {
            return;
        }
        $driver = config('database.default');
        $connection = config("database.connections.$driver.driver");
        if (in_array($connection, ['mysql', 'mariadb'], true)) {
            // Include 'expired' to support auto-expire flow
            DB::statement("ALTER TABLE event_requests MODIFY COLUMN status ENUM('pending', 'approved', 'declined', 'awaiting_payment', 'paid', 'expired') NOT NULL DEFAULT 'pending'");
        } else {
            // SQLite / others: do nothing, keep as TEXT
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('event_requests')) {
            return;
        }
        $driver = config('database.default');
        $connection = config("database.connections.$driver.driver");
        if (in_array($connection, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE event_requests MODIFY COLUMN status ENUM('pending', 'approved', 'declined') NOT NULL DEFAULT 'pending'");
        }
    }
};
