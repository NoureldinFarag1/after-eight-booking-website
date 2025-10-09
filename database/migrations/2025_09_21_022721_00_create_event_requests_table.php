<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->json('payload')->nullable(); // stores form fields (4 fields) as JSON
            $driver = config('database.default');
            $connection = config("database.connections.$driver.driver");
            if (in_array($connection, ['mysql','mariadb'], true)) {
                $table->enum('status', ['pending','approved','declined'])->default('pending');
            } else {
                $table->string('status', 32)->default('pending');
            }
            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['event_id','user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_requests');
    }
};
