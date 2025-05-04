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
        Schema::create('course_registrations', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('student_id');
            $table->foreign('student_id')->references('id')->on('students');
            $table->unsignedInteger('subject_id'); // Using existing subject table
            $table->foreign('subject_id')->references('id')->on('subjects');
            $table->unsignedInteger('academic_year_id');
            $table->foreign('academic_year_id')->references('id')->on('academic_years');
            $table->unsignedInteger('semester_id');
            $table->foreign('semester_id')->references('id')->on('semesters');
            $table->timestamp('registered_at');
            $table->decimal('payment_percentage_at_registration', 5, 2);
            $table->boolean('is_approved')->default(true);
            $table->timestamps();
            
            // Unique constraint to prevent duplicate registrations
            $table->unique(['student_id', 'subject_id', 'academic_year_id', 'semester_id'], 'unique_course_registration');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_registrations');
    }
};