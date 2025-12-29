<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ticket_types', function (Blueprint $table) {
            if (!Schema::hasColumn('ticket_types', 'fee_type')) {
                $table->enum('fee_type', ['fixed','percentage'])->nullable()->after('price');
            }
            if (!Schema::hasColumn('ticket_types', 'fee_amount')) {
                $table->decimal('fee_amount', 10, 2)->nullable()->after('fee_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ticket_types', function (Blueprint $table) {
            if (Schema::hasColumn('ticket_types', 'fee_amount')) {
                $table->dropColumn('fee_amount');
            }
            if (Schema::hasColumn('ticket_types', 'fee_type')) {
                $table->dropColumn('fee_type');
            }
        });
    }
};
