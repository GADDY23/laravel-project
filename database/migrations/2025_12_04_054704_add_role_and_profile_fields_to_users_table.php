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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'teacher', 'student'])->default('student')->after('email');
            $table->string('username')->unique()->nullable()->after('name');
            $table->string('employee_id')->nullable()->after('username');
            $table->string('student_id')->nullable()->after('username');
            $table->string('department')->nullable()->after('employee_id');
            $table->string('course_strand')->nullable()->after('student_id');
            $table->string('year_level')->nullable()->after('course_strand');
            $table->string('section')->nullable()->after('year_level');
            $table->text('expertise')->nullable()->after('department');
            $table->enum('account_status', ['active', 'inactive'])->default('active')->after('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'role',
                'username',
                'employee_id',
                'student_id',
                'department',
                'course_strand',
                'year_level',
                'section',
                'expertise',
                'account_status'
            ]);
        });
    }
};
