<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (!Schema::hasColumn('events', 'finance_officer_id')) {
                $table->foreignId('finance_officer_id')->nullable()->constrained('users')->nullOnDelete()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (Schema::hasColumn('events', 'finance_officer_id')) {
                try {
                    $table->dropForeign(['finance_officer_id']);
                } catch (\Throwable $e) {}
                $table->dropColumn('finance_officer_id');
            }
        });
    }
};
