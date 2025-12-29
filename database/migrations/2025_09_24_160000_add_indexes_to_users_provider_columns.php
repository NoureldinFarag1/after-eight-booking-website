<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add safe indexes without raw SQL
            if (!Schema::hasColumn('users', 'provider_id')) {
                $table->string('provider_id')->nullable()->after('password');
            }

            if (!Schema::hasColumn('users', 'provider_name')) {
                $table->string('provider_name')->nullable()->after('provider_id');
            }

            // ✅ Create indexes safely
            $table->index('provider_id', 'users_provider_id_index');
            $table->index('provider_name', 'users_provider_name_index');
            $table->index(['provider_name', 'provider_id'], 'users_provider_combo_index');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // ✅ Drop indexes by name (safe rollback)
            $table->dropIndex('users_provider_combo_index');
            $table->dropIndex('users_provider_id_index');
            $table->dropIndex('users_provider_name_index');

            // Drop columns if needed
            $table->dropColumn(['provider_id', 'provider_name']);
        });
    }
};