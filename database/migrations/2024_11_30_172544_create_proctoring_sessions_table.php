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
        if (! Schema::hasTable('proctoring_sessions')) {
            Schema::create('proctoring_sessions', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedBigInteger('user_id');
                $table->unsignedInteger('exam_id');
                $table->string('ip_address')->nullable();
                $table->string('user_agent')->nullable();
                $table->unsignedBigInteger('proctor_id')->nullable();
                $table->foreign('proctor_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('exam_id')->references('id')->on('exams')->onDelete('cascade');
                $table->timestamp('started_at');
                $table->timestamp('ended_at')->nullable();
                $table->boolean('flagged')->default(false);
                $table->text('report')->nullable();
                $table->timestamps();
            });
        } else {
            // Log a message or handle the existing table
            echo "Table 'proctoring_sessions' already exists. No changes made.\n";
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proctoring_sessions');
    }
};
