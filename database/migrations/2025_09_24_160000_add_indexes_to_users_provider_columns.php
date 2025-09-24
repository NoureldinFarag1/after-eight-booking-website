<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $existing = collect(DB::select("SHOW INDEX FROM `users`"))
            ->pluck('Key_name')
            ->unique()
            ->flip();

        Schema::table('users', function (Blueprint $table) use ($existing) {
            if (!$existing->has('users_provider_id_index')) {
                $table->index('provider_id', 'users_provider_id_index');
            }
            if (!$existing->has('users_provider_name_index')) {
                $table->index('provider_name', 'users_provider_name_index');
            }
            if (!$existing->has('users_provider_combo_index')) {
                $table->index(['provider_name', 'provider_id'], 'users_provider_combo_index');
            }
        });
    }

    public function down(): void
    {
        $existing = collect(DB::select("SHOW INDEX FROM `users`"))
            ->pluck('Key_name')
            ->unique()
            ->flip();

        Schema::table('users', function (Blueprint $table) use ($existing) {
            if ($existing->has('users_provider_combo_index')) {
                $table->dropIndex('users_provider_combo_index');
            }
            if ($existing->has('users_provider_id_index')) {
                $table->dropIndex('users_provider_id_index');
            }
            if ($existing->has('users_provider_name_index')) {
                $table->dropIndex('users_provider_name_index');
            }
        });
    }
};
