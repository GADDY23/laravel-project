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
            if (!Schema::hasColumn('schedules', 'schedule_name')) {
                $table->string('schedule_name')->nullable()->after('term_id');
                $table->index('schedule_name', 'idx_schedules_schedule_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            if (Schema::hasColumn('schedules', 'schedule_name')) {
                $table->dropIndex('idx_schedules_schedule_name');
                $table->dropColumn('schedule_name');
            }
        });
    }
};
