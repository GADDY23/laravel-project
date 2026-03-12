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
        Schema::create('curricula', function (Blueprint $table) {
            $table->id();
            $table->string('curriculum_code')->unique();
            $table->integer('school_year_start')->nullable();
            $table->enum('course_type', ['shs', 'college'])->default('college');
            $table->enum('curriculum_type', ['semestral', 'trimestral'])->default('semestral');
            $table->string('course_strand');
            $table->enum('year_level', ['grade_11', 'grade_12', '1st_year', '2nd_year', '3rd_year', '4th_year']);
            $table->unsignedBigInteger('term_id')->nullable();
            $table->timestamps();
        });

        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('curricula');
    }
};
