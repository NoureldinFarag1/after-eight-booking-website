<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if(!Schema::hasColumn('events','google_maps_url')){
                $table->string('google_maps_url', 1024)->nullable()->after('location');
            }
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if(Schema::hasColumn('events','google_maps_url')){
                $table->dropColumn('google_maps_url');
            }
        });
    }
};
