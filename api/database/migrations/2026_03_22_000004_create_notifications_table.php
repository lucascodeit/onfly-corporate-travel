<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_to_id')->constrained('users');
            $table->foreignId('user_from_id')->constrained('users');
            $table->foreignId('request_id')->nullable()->constrained('travel_requests')->nullOnDelete();
            $table->enum('notification_type', ['response_travel', 'travel_create', 'general']);
            $table->text('message')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
