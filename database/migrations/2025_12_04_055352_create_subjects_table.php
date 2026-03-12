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
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique()->nullable();
            $table->enum('year_level', ['grade_11', 'grade_12', '1st_year', '2nd_year', '3rd_year', '4th_year']);
            $table->string('course_strand');
            $table->decimal('lec_unit', 4, 2)->default(0);
            $table->decimal('lab_unit', 4, 2)->default(0);
            $table->enum('required_room_type', ['lecture', 'computer_lab', 'chemistry_lab'])->default('lecture');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};
