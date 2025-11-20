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
        Schema::create('student_grades', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('student_id');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->unsignedInteger('college_class_id');
            $table->foreign('college_class_id')->references('id')->on('college_classes')->onDelete('cascade');
            $table->unsignedInteger('grade_id');
            $table->foreign('grade_id')->references('id')->on('grades')->onDelete('restrict');
            $table->text('comments')->nullable();
            $table->unsignedBigInteger('graded_by')->nullable();
            $table->foreign('graded_by')->references('id')->on('users')->onDelete('set null');
            $table->timestamps();

            // Unique constraint to prevent duplicate grades for the same student and class
            $table->unique(['student_id', 'college_class_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_grades');
    }
};
