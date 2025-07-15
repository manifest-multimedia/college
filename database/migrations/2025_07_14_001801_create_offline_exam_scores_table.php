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
        Schema::create('offline_exam_scores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('offline_exam_id');
            $table->foreign('offline_exam_id')->references('id')->on('offline_exams')->onDelete('cascade');
            $table->unsignedInteger('student_id');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->decimal('score', 8, 2); // Score obtained (e.g., 85.50)
            $table->decimal('total_marks', 8, 2); // Total marks for the exam (e.g., 100.00)
            $table->decimal('percentage', 5, 2)->nullable(); // Calculated percentage (e.g., 85.50)
            $table->text('remarks')->nullable(); // Optional remarks/comments
            $table->unsignedBigInteger('recorded_by'); // User who recorded the score
            $table->foreign('recorded_by')->references('id')->on('users')->onDelete('restrict');
            $table->timestamp('exam_date')->nullable(); // Date when the exam was taken
            $table->timestamps();
            
            // Unique constraint to prevent duplicate scores for same student and exam
            $table->unique(['offline_exam_id', 'student_id'], 'unique_offline_exam_student_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offline_exam_scores');
    }
};
