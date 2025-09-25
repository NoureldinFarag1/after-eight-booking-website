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
        // Use raw SQL to avoid requiring doctrine/dbal for change()
        $connection = DB::getDriverName();
        if ($connection === 'mysql') {
            DB::statement('ALTER TABLE `events` MODIFY `price` DECIMAL(10,2) NULL');
        } elseif ($connection === 'pgsql') {
            DB::statement('ALTER TABLE "events" ALTER COLUMN "price" DROP NOT NULL');
        } else {
            // Fallback to schema change for other drivers
            Schema::table('events', function (Blueprint $table) {
                $table->decimal('price', 10, 2)->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $connection = DB::getDriverName();
        if ($connection === 'mysql') {
            DB::statement('ALTER TABLE `events` MODIFY `price` DECIMAL(10,2) NOT NULL DEFAULT 0');
        } elseif ($connection === 'pgsql') {
            DB::statement('ALTER TABLE "events" ALTER COLUMN "price" SET NOT NULL');
            DB::statement('ALTER TABLE "events" ALTER COLUMN "price" SET DEFAULT 0');
        } else {
            Schema::table('events', function (Blueprint $table) {
                $table->decimal('price', 10, 2)->nullable(false)->default(0)->change();
            });
        }
    }
};
