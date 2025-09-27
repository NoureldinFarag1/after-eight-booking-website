<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            // MySQL supports MODIFY
            DB::statement('ALTER TABLE `users` MODIFY `provider_token` TEXT NULL');
            DB::statement('ALTER TABLE `users` MODIFY `provider_refresh_token` TEXT NULL');
        } elseif ($driver === 'sqlite') {
            // SQLite does not support MODIFY – we skip or rebuild manually
            // If they don’t exist yet, just ensure columns are TEXT
            Schema::table('users', function ($table) {
                if (Schema::hasColumn('users', 'provider_token')) {
                    // SQLite can't alter column types, so skip
                }
                if (Schema::hasColumn('users', 'provider_refresh_token')) {
                    // Skip
                }
            });
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE `users` MODIFY `provider_token` VARCHAR(255) NULL');
            DB::statement('ALTER TABLE `users` MODIFY `provider_refresh_token` VARCHAR(255) NULL');
        } elseif ($driver === 'sqlite') {
            // No down migration possible in SQLite without rebuild
        }
    }
};