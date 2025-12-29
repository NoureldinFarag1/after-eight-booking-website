<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop the price column from events table
        if (Schema::hasColumn('events', 'price')) {
            $driver = DB::getDriverName();
            if ($driver === 'sqlite') {
                // SQLite requires table recreation; for simplicity, use schema builder change where possible
                Schema::table('events', function (Blueprint $table) {
                    $table->dropColumn('price');
                });
            } else {
                Schema::table('events', function (Blueprint $table) {
                    $table->dropColumn('price');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-add price as nullable decimal if rolled back
        if (!Schema::hasColumn('events', 'price')) {
            Schema::table('events', function (Blueprint $table) {
                $table->decimal('price', 10, 2)->nullable()->after('type');
            });
        }
    }
};
