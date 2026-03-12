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
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropForeign(['teacher_id']);
            $table->dropForeign(['section_id']);
            $table->dropUnique('unique_teacher_schedule');
            $table->dropUnique('unique_section_schedule');
            // Ensure FK-supporting indexes remain after dropping composite uniques.
            $table->index('teacher_id', 'idx_schedules_teacher_id');
            $table->index('section_id', 'idx_schedules_section_id');
            $table->foreign('teacher_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('section_id')->references('id')->on('sections')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropForeign(['teacher_id']);
            $table->dropForeign(['section_id']);
            $table->unique(['teacher_id', 'day', 'time_start', 'time_end', 'term_id'], 'unique_teacher_schedule');
            $table->unique(['section_id', 'day', 'time_start', 'time_end', 'term_id'], 'unique_section_schedule');
            $table->dropIndex('idx_schedules_teacher_id');
            $table->dropIndex('idx_schedules_section_id');
            $table->foreign('teacher_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('section_id')->references('id')->on('sections')->cascadeOnDelete();
        });
    }
};
