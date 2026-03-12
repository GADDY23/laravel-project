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
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('course_strand');
            $table->enum('year_level', ['grade_11', 'grade_12', '1st_year', '2nd_year', '3rd_year', '4th_year']);
            $table->integer('capacity');
            $table->unsignedBigInteger('term_id')->nullable();
            $table->unsignedBigInteger('curriculum_id')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->foreignId('adviser_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};
