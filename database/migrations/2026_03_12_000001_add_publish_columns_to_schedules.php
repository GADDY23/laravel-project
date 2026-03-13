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
            if (!Schema::hasColumn('schedules', 'is_published')) {
                $table->boolean('is_published')->default(false)->after('time_end');
            }
            if (!Schema::hasColumn('schedules', 'published_at')) {
                $table->timestamp('published_at')->nullable()->after('is_published');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            if (Schema::hasColumn('schedules', 'published_at')) {
                $table->dropColumn('published_at');
            }
            if (Schema::hasColumn('schedules', 'is_published')) {
                $table->dropColumn('is_published');
            }
        });
    }
};
