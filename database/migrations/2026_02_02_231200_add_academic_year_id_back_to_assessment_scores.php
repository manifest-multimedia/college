<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('assessment_scores', function (Blueprint $table) {
            // Check if column doesn't exist before adding
            if (! Schema::hasColumn('assessment_scores', 'academic_year_id')) {
                $table->unsignedInteger('academic_year_id')->after('semester_id');
                $table->foreign('academic_year_id')->references('id')->on('academic_years')->onDelete('cascade');
            }
        });

        // Update unique constraint to include academic_year_id
        Schema::table('assessment_scores', function (Blueprint $table) {
            // Drop old unique constraint if it exists
            try {
                $table->dropUnique('unique_student_course_semester');
            } catch (\Exception $e) {
                // Constraint might not exist, continue
            }

            // Add new unique constraint with academic_year_id
            $table->unique(['course_id', 'student_id', 'cohort_id', 'semester_id', 'academic_year_id'], 'unique_student_course_year_semester');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assessment_scores', function (Blueprint $table) {
            // Drop new unique constraint
            $table->dropUnique('unique_student_course_year_semester');

            // Restore old unique constraint
            $table->unique(['course_id', 'student_id', 'semester_id'], 'unique_student_course_semester');

            // Drop foreign key and column
            $table->dropForeign(['academic_year_id']);
            $table->dropColumn('academic_year_id');
        });
    }
};
