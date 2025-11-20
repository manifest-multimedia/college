<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Remove semester_id and instructor_id columns from college_classes table
     * as programs are now semester-independent and not tied to specific instructors
     */
    public function up(): void
    {
        Schema::table('college_classes', function (Blueprint $table) {
            // Drop foreign key constraints first if they exist
            if (Schema::hasColumn('college_classes', 'semester_id')) {
                $table->dropForeign(['semester_id']);
                $table->dropColumn('semester_id');
            }

            if (Schema::hasColumn('college_classes', 'instructor_id')) {
                $table->dropForeign(['instructor_id']);
                $table->dropColumn('instructor_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * Restore semester_id and instructor_id columns if needed
     */
    public function down(): void
    {
        Schema::table('college_classes', function (Blueprint $table) {
            // Add back the columns
            $table->unsignedInteger('semester_id')->nullable();
            $table->unsignedBigInteger('instructor_id')->nullable();

            // Add back foreign key constraints
            $table->foreign('semester_id')->references('id')->on('semesters')->onDelete('set null');
            $table->foreign('instructor_id')->references('id')->on('users')->onDelete('set null');
        });
    }
};
