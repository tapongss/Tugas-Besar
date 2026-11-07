<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('operator')->index()->after('password');
            $table->string('phone', 30)->nullable()->after('role');
            $table->boolean('is_active')->default(true)->after('phone');
            $table->string('api_token', 80)->nullable()->unique()->after('is_active');
            $table->timestamp('last_login_at')->nullable()->after('api_token');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'phone', 'is_active', 'api_token', 'last_login_at']);
        });
    }
};
