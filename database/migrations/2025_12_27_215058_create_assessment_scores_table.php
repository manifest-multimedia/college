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
        Schema::create('assessment_scores', function (Blueprint $table) {
            $table->id();

            // Foreign Keys (all parent tables use increments('id'))
            $table->unsignedInteger('course_id');
            $table->unsignedInteger('student_id');
            $table->unsignedInteger('academic_year_id');
            $table->unsignedInteger('semester_id');

            // Assignment Scores (each out of 100)
            $table->decimal('assignment_1_score', 5, 2)->nullable();
            $table->decimal('assignment_2_score', 5, 2)->nullable();
            $table->decimal('assignment_3_score', 5, 2)->nullable();
            $table->decimal('assignment_4_score', 5, 2)->nullable();
            $table->decimal('assignment_5_score', 5, 2)->nullable();
            $table->integer('assignment_count')->default(3);

            // Mid-Semester Exam
            $table->decimal('mid_semester_score', 5, 2)->nullable();

            // End-of-Semester Exam
            $table->decimal('end_semester_score', 5, 2)->nullable();

            // Weight Configuration (percentages, must sum to 100)
            $table->decimal('assignment_weight', 5, 2)->default(20.00);
            $table->decimal('mid_semester_weight', 5, 2)->default(20.00);
            $table->decimal('end_semester_weight', 5, 2)->default(60.00);

            // Metadata
            $table->unsignedBigInteger('recorded_by');
            $table->text('remarks')->nullable();

            $table->timestamps();

            // Foreign Key Constraints
            $table->foreign('course_id')->references('id')->on('subjects')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('academic_year_id')->references('id')->on('academic_years')->onDelete('cascade');
            $table->foreign('semester_id')->references('id')->on('semesters')->onDelete('cascade');
            $table->foreign('recorded_by')->references('id')->on('users')->onDelete('cascade');

            // Unique constraint: One record per student per course per semester
            $table->unique(['course_id', 'student_id', 'academic_year_id', 'semester_id'], 'unique_student_course_semester');

            // Indexes for performance
            $table->index(['student_id', 'academic_year_id', 'semester_id']);
            $table->index(['course_id', 'academic_year_id', 'semester_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessment_scores');
    }
};
