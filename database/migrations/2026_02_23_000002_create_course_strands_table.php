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
        Schema::create('course_strands', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['shs', 'college']);
            $table->timestamps();

            $table->unique(['name', 'type']);
        });

        DB::table('course_strands')->insert([
            [
                'name' => 'ABM',
                'description' => 'Accountancy, Business, and Management',
                'type' => 'shs',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'STEM',
                'description' => 'Science, Technology, Engineering, and Mathematics',
                'type' => 'shs',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'HUMSS',
                'description' => 'Humanities and Social Sciences',
                'type' => 'shs',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'BSIT',
                'description' => 'Bachelor of Science in Information Technology',
                'type' => 'college',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'BSCS',
                'description' => 'Bachelor of Science in Computer Science',
                'type' => 'college',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_strands');
    }
};
