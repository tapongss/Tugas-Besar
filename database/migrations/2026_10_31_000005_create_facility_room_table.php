<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facility_room', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('facility_id')->constrained()->restrictOnDelete();
            $table->unsignedSmallInteger('quantity')->default(1);
            $table->string('condition', 20)->default('baik');
            $table->string('note')->nullable();
            $table->timestamps();

            $table->unique(['room_id', 'facility_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facility_room');
    }
};
