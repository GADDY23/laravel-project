<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->nullable()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('section_id')->constrained('sections')->cascadeOnDelete();
            $table->foreignId('room_id')->constrained('rooms')->cascadeOnDelete();
            $table->foreignId('term_id')->constrained('terms')->cascadeOnDelete();
            $table->enum('day', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']);
            $table->time('time_start');
            $table->time('time_end');
            $table->timestamps();
            
            // Prevent duplicate schedules
            $table->unique(['teacher_id', 'day', 'time_start', 'time_end', 'term_id'], 'unique_teacher_schedule');
            $table->unique(['room_id', 'day', 'time_start', 'time_end', 'term_id'], 'unique_room_schedule');
            $table->unique(['section_id', 'day', 'time_start', 'time_end', 'term_id'], 'unique_section_schedule');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
