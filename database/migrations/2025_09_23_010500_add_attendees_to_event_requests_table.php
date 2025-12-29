<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('event_requests', function (Blueprint $table) {
            $table->string('primary_name')->nullable()->after('user_id');
            $table->string('primary_email')->nullable()->after('primary_name');
            $table->string('primary_social_url')->nullable()->after('primary_email');
            $table->json('guests')->nullable()->after('primary_social_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_requests', function (Blueprint $table) {
            $table->dropColumn(['primary_name', 'primary_email', 'primary_social_url', 'guests']);
        });
    }
};
