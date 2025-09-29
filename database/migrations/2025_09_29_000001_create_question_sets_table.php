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
        Schema::create('question_sets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedInteger('course_id'); // Reference to subjects table
            $table->enum('difficulty_level', ['easy', 'medium', 'hard'])->default('medium');
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            $table->softDeletes();

            // Foreign key constraints
            $table->foreign('course_id')->references('id')->on('subjects')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_sets');
    }
};