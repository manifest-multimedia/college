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
        Schema::create('exam_question_set', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('exam_id');
            $table->unsignedBigInteger('question_set_id'); // question_sets uses bigint
            $table->boolean('shuffle_questions')->default(true);
            $table->integer('questions_to_pick')->nullable(); // How many questions to pick from this set
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('exam_id')->references('id')->on('exams')->onDelete('cascade');
            $table->foreign('question_set_id')->references('id')->on('question_sets')->onDelete('cascade');

            // Unique constraint to prevent duplicate entries
            $table->unique(['exam_id', 'question_set_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_question_set');
    }
};
