<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateResponsesTable extends Migration
{
    public function up()
    {
        Schema::create('responses', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('exam_session_id');
            $table->unsignedInteger('question_id')->nullable();
            $table->string('selected_option'); // Stores the chosen option (e.g., "option_one", "option_two")
            $table->boolean('is_correct')->nullable(); // Whether the answer is correct
            $table->timestamps();

            $table->foreign('exam_session_id')->references('id')->on('exam_sessions')->onDelete('cascade');
            $table->foreign('question_id')->references('id')->on('questions')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('responses');
    }
}
