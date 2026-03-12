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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('capacity');
            $table->string('building')->nullable();
            $table->enum('floor', ['1st_floor', '2nd_floor', '3rd_floor', '4th_floor'])->nullable();
            $table->enum('room_type', ['lecture', 'computer_lab', 'chemistry_lab'])->default('lecture');
            $table->enum('status', ['available', 'unavailable'])->default('available');
            $table->text('description')->nullable();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
