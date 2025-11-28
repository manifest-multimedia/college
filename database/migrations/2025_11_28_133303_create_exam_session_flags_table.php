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
        Schema::create('exam_session_flags', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('exam_session_id');
            $table->unsignedInteger('question_id');
            $table->timestamps();
            
            // Ensure a question can only be flagged once per session
            $table->unique(['exam_session_id', 'question_id']);
            
            // Foreign key constraints with cascade delete
            $table->foreign('exam_session_id')
                  ->references('id')
                  ->on('exam_sessions')
                  ->onDelete('cascade');
                  
            $table->foreign('question_id')
                  ->references('id')
                  ->on('questions')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_session_flags');
    }
};
