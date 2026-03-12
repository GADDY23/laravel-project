<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('schedules', 'teacher_id')) {
            return;
        }

        if (DB::getDriverName() === 'sqlite') {
            // SQLite doesn't support MODIFY COLUMN; skip to keep tests running.
            return;
        }

        Schema::table('schedules', function (Blueprint $table) {
            $table->dropForeign(['teacher_id']);
        });

        DB::statement('ALTER TABLE schedules MODIFY teacher_id BIGINT UNSIGNED NULL');

        Schema::table('schedules', function (Blueprint $table) {
            $table->foreign('teacher_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasColumn('schedules', 'teacher_id')) {
            return;
        }

        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('schedules', function (Blueprint $table) {
            $table->dropForeign(['teacher_id']);
        });

        DB::statement('ALTER TABLE schedules MODIFY teacher_id BIGINT UNSIGNED NOT NULL');

        Schema::table('schedules', function (Blueprint $table) {
            $table->foreign('teacher_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
