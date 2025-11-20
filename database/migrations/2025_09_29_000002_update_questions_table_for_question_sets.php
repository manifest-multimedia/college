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
        Schema::table('questions', function (Blueprint $table) {
            // Make exam_id nullable since questions can now belong to question sets
            $table->unsignedInteger('exam_id')->nullable()->change();

            // Add question set relationship
            $table->unsignedBigInteger('question_set_id')->nullable()->after('exam_id');

            // Add question type
            $table->enum('type', ['MCQ', 'MA', 'TF', 'ESSAY'])->default('MCQ')->after('question_text');

            // Add difficulty level
            $table->enum('difficulty_level', ['easy', 'medium', 'hard'])->default('medium')->after('type');

            // Add foreign key for question sets
            $table->foreign('question_set_id')->references('id')->on('question_sets')->onDelete('cascade');

            // Add constraint to ensure question belongs to either exam or question set
            $table->index(['exam_id', 'question_set_id'], 'questions_exam_set_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropForeign(['question_set_id']);
            $table->dropIndex('questions_exam_set_index');
            $table->dropColumn(['question_set_id', 'type', 'difficulty_level']);
            $table->unsignedBigInteger('exam_id')->nullable(false)->change();
        });
    }
};
