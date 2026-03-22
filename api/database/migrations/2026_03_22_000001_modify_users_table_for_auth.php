<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['name', 'email_verified_at', 'remember_token']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name', 50)->after('id');
            $table->string('last_name', 50)->after('first_name');
            $table->enum('type', ['admin', 'staff'])->default('staff')->after('last_name');
            $table->boolean('is_active')->default(true)->after('password');
            $table->string('email', 120)->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'last_name', 'type', 'is_active']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('name')->after('id');
            $table->timestamp('email_verified_at')->nullable()->after('email');
            $table->rememberToken();
            $table->string('email', 255)->change();
        });
    }
};
