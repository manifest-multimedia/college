<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ---- STEP 1: DROP OLD CONSTRAINTS SAFELY ----
        Schema::table('assessment_scores', function (Blueprint $table) {

            // Drop FK only if it exists
            if ($this->foreignKeyExists('assessment_scores', 'assessment_scores_academic_year_id_foreign')) {
                $table->dropForeign('assessment_scores_academic_year_id_foreign');
            }

            // Drop indexes only if they exist
            $this->dropIndexIfExists($table, 'unique_student_course_semester');
            $this->dropIndexIfExists($table, 'assessment_scores_student_id_academic_year_id_semester_id_index');
            $this->dropIndexIfExists($table, 'assessment_scores_course_id_academic_year_id_semester_id_index');

            // Drop column only if it exists
            if (Schema::hasColumn('assessment_scores', 'academic_year_id')) {
                $table->dropColumn('academic_year_id');
            }
        });

        // ---- STEP 2: ADD NEW STRUCTURE ----
        Schema::table('assessment_scores', function (Blueprint $table) {

            if (!Schema::hasColumn('assessment_scores', 'cohort_id')) {
                $table->unsignedBigInteger('cohort_id')->after('student_id');
            }

            $table->foreign('cohort_id')
                ->references('id')
                ->on('cohorts')
                ->onDelete('cascade');

            // Indexes
            $table->index(
                ['student_id', 'cohort_id', 'semester_id'],
                'assessment_scores_student_id_cohort_id_semester_id_index'
            );

            $table->index(
                ['course_id', 'cohort_id', 'semester_id'],
                'assessment_scores_course_id_cohort_id_semester_id_index'
            );

            // Unique constraint
            $table->unique(
                ['course_id', 'student_id', 'cohort_id', 'semester_id'],
                'unique_student_course_semester_cohort'
            );
        });
    }

    public function down(): void
    {
        // ---- STEP 1: REMOVE COHORT SETUP ----
        Schema::table('assessment_scores', function (Blueprint $table) {

            if ($this->foreignKeyExists('assessment_scores', 'assessment_scores_cohort_id_foreign')) {
                $table->dropForeign('assessment_scores_cohort_id_foreign');
            }

            $this->dropIndexIfExists($table, 'unique_student_course_semester_cohort');
            $this->dropIndexIfExists($table, 'assessment_scores_student_id_cohort_id_semester_id_index');
            $this->dropIndexIfExists($table, 'assessment_scores_course_id_cohort_id_semester_id_index');

            if (Schema::hasColumn('assessment_scores', 'cohort_id')) {
                $table->dropColumn('cohort_id');
            }
        });

        // ---- STEP 2: RESTORE ACADEMIC YEAR ----
        Schema::table('assessment_scores', function (Blueprint $table) {

            if (!Schema::hasColumn('assessment_scores', 'academic_year_id')) {
                $table->unsignedBigInteger('academic_year_id')->after('student_id');
            }

            $table->foreign('academic_year_id')
                ->references('id')
                ->on('academic_years')
                ->onDelete('cascade');

            $table->index(
                ['student_id', 'academic_year_id', 'semester_id'],
                'assessment_scores_student_id_academic_year_id_semester_id_index'
            );

            $table->index(
                ['course_id', 'academic_year_id', 'semester_id'],
                'assessment_scores_course_id_academic_year_id_semester_id_index'
            );

            $table->unique(
                ['course_id', 'student_id', 'academic_year_id', 'semester_id'],
                'unique_student_course_semester'
            );
        });
    }

    // ------------------------------------------------
    // Helpers
    // ------------------------------------------------

    private function foreignKeyExists(string $table, string $keyName): bool
    {
        $result = DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND CONSTRAINT_NAME = ?
        ", [$table, $keyName]);

        return !empty($result);
    }

    private function dropIndexIfExists(Blueprint $table, string $indexName): void
    {
        try {
            $table->dropIndex($indexName);
        } catch (\Throwable $e) {
            // safe ignore
        }
    }
};
