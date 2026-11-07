<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('building_id')->constrained()->restrictOnDelete();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->unsignedTinyInteger('floor')->default(1);
            $table->string('type', 30)->default('ruang_sidang')->index();
            $table->unsignedSmallInteger('capacity')->default(20);
            $table->string('status', 20)->default('tersedia')->index();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['building_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
