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
        Schema::create('assessment_score_resits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assessment_score_id');
            $table->unsignedInteger('course_id');
            $table->unsignedInteger('student_id');
            $table->unsignedInteger('cohort_id');
            $table->unsignedInteger('semester_id');
            $table->unsignedInteger('academic_year_id');

            $table->unsignedInteger('attempt_number');
            $table->decimal('previous_exam_score', 5, 2)->nullable();
            $table->decimal('resit_score', 5, 2);
            $table->decimal('updated_average_score', 5, 2)->nullable();

            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('recorded_by');
            $table->timestamps();

            $table->foreign('assessment_score_id')->references('id')->on('assessment_scores')->onDelete('cascade');
            $table->foreign('course_id')->references('id')->on('subjects')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('cohort_id')->references('id')->on('cohorts')->onDelete('cascade');
            $table->foreign('semester_id')->references('id')->on('semesters')->onDelete('cascade');
            $table->foreign('academic_year_id')->references('id')->on('academic_years')->onDelete('cascade');
            $table->foreign('recorded_by')->references('id')->on('users')->onDelete('cascade');

            $table->unique(['assessment_score_id', 'attempt_number'], 'unique_assessment_resit_attempt');
            $table->index(['student_id', 'course_id', 'semester_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessment_score_resits');
    }
};
