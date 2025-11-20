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
        Schema::table('college_classes', function (Blueprint $table) {
            // Add semester relationship if it doesn't exist
            if (! Schema::hasColumn('college_classes', 'semester_id')) {
                $table->unsignedInteger('semester_id')->nullable();
                $table->foreign('semester_id')->references('id')->on('semesters')->onDelete('set null');
            }

            // Add course relationship if it doesn't exist
            if (! Schema::hasColumn('college_classes', 'course_id')) {
                $table->unsignedInteger('course_id')->nullable();
                $table->foreign('course_id')->references('id')->on('courses')->onDelete('set null');
            }

            // Add instructor relationship if it doesn't exist
            if (! Schema::hasColumn('college_classes', 'instructor_id')) {
                $table->unsignedBigInteger('instructor_id')->nullable();
                $table->foreign('instructor_id')->references('id')->on('users')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('college_classes', function (Blueprint $table) {
            // Remove foreign keys
            if (Schema::hasColumn('college_classes', 'semester_id')) {
                $table->dropForeign(['semester_id']);
                $table->dropColumn('semester_id');
            }
            if (Schema::hasColumn('college_classes', 'course_id')) {
                $table->dropForeign(['course_id']);
                $table->dropColumn('course_id');
            }
            if (Schema::hasColumn('college_classes', 'instructor_id')) {
                $table->dropForeign(['instructor_id']);
                $table->dropColumn('instructor_id');
            }
        });
    }
};
