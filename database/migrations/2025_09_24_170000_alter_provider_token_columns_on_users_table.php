<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Use raw SQL to avoid needing doctrine/dbal for column alteration.
        // TEXT is sufficient (up to 65,535 bytes) for encrypted tokens.
        DB::statement('ALTER TABLE `users` MODIFY `provider_token` TEXT NULL');
        DB::statement('ALTER TABLE `users` MODIFY `provider_refresh_token` TEXT NULL');
    }

    public function down(): void
    {
        // Revert to VARCHAR(255) if needed (may truncate existing long values) – document risk.
        DB::statement('ALTER TABLE `users` MODIFY `provider_token` VARCHAR(255) NULL');
        DB::statement('ALTER TABLE `users` MODIFY `provider_refresh_token` VARCHAR(255) NULL');
    }
};
