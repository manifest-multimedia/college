<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Drop if Table exists
        Schema::dropIfExists('scored_questions');

        Schema::create('scored_questions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('exam_session_id');
            $table->unsignedInteger('question_id');
            $table->unsignedInteger('response_id');
            $table->timestamps();

            // Add foreign key constraints
            $table->foreign('exam_session_id')
                ->references('id')
                ->on('exam_sessions')
                ->onDelete('cascade');

            $table->foreign('question_id')
                ->references('id')
                ->on('questions')
                ->onDelete('cascade');

            $table->foreign('response_id')
                ->references('id')
                ->on('responses')
                ->onDelete('cascade');

            // Ensure we don't store duplicate questions for a session
            $table->unique(['exam_session_id', 'question_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('scored_questions');
    }
};
