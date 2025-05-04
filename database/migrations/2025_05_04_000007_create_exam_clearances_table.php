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
        Schema::create('exam_clearances', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('student_id');
            $table->foreign('student_id')->references('id')->on('students');
            $table->unsignedInteger('academic_year_id');
            $table->foreign('academic_year_id')->references('id')->on('academic_years');
            $table->unsignedInteger('semester_id');
            $table->foreign('semester_id')->references('id')->on('semesters');
            $table->foreignId('exam_type_id')->constrained();
            $table->boolean('is_cleared')->default(false);
            $table->boolean('is_manual_override')->default(false);
            $table->text('override_reason')->nullable();
            $table->unsignedBigInteger('cleared_by')->nullable(); // User ID who cleared the student
            $table->foreign('cleared_by')->references('id')->on('users');
            $table->timestamp('cleared_at')->nullable();
            $table->string('clearance_code')->unique();
            $table->timestamps();
            
            // Unique constraint to prevent duplicate clearances
            $table->unique(['student_id', 'academic_year_id', 'semester_id', 'exam_type_id'], 'unique_student_exam_clearance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_clearances');
    }
};