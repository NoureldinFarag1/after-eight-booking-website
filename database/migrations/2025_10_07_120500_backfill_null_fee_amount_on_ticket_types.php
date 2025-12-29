<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Set any NULL fee_amount to 0 for consistency (explicit zero fee)
        DB::table('ticket_types')
            ->whereNull('fee_amount')
            ->update(['fee_amount' => 0]);
    }

    public function down(): void
    {
        // Can't reliably revert to NULL (data loss concern), so leave as-is.
    }
};
