<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->foreignId('ticket_type_id')->nullable()->after('event_id')->constrained('ticket_types')->nullOnDelete();
            $table->decimal('price', 10, 2)->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('ticket_type_id');
            $table->dropColumn('price');
        });
    }
};
