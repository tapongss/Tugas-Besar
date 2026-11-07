<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('defense_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->restrictOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->string('student_name');
            $table->string('student_nim', 20);
            $table->string('type', 30)->default('skripsi')->index();
            $table->date('date')->index();
            $table->string('start_time', 8);
            $table->string('end_time', 8);
            $table->string('status', 20)->default('dijadwalkan')->index();
            $table->string('supervisor_name')->nullable();
            $table->string('examiner_name')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['room_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('defense_sessions');
    }
};
