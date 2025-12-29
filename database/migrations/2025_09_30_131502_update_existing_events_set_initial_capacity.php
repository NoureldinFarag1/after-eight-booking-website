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
        // Update existing events to set initial_capacity to current capacity if null
        DB::table('events')
            ->whereNull('initial_capacity')
            ->update([
                'initial_capacity' => DB::raw('capacity'),
                'updated_at' => now()
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Set initial_capacity back to null for events where it equals capacity
        DB::table('events')
            ->whereRaw('initial_capacity = capacity')
            ->update([
                'initial_capacity' => null,
                'updated_at' => now()
            ]);
    }
};
