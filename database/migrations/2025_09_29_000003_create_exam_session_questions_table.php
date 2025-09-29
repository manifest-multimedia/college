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
        Schema::create('exam_session_questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('exam_session_id');
            $table->unsignedInteger('question_id');
            $table->integer('display_order')->default(1);
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('exam_session_id')->references('id')->on('exam_sessions')->onDelete('cascade');
            $table->foreign('question_id')->references('id')->on('questions')->onDelete('cascade');
            
            // Unique constraint to prevent duplicate questions in same session
            $table->unique(['exam_session_id', 'question_id']);
            
            // Index for ordering
            $table->index(['exam_session_id', 'display_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_session_questions');
    }
};