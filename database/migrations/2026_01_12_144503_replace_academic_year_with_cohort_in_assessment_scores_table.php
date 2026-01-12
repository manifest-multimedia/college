<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assessment_scores', function (Blueprint $table) {

            // -----------------------------
            // DROP OLD FK (if exists)
            // -----------------------------
            if ($this->foreignKeyExists('assessment_scores', 'assessment_scores_academic_year_id_foreign')) {
                $table->dropForeign('assessment_scores_academic_year_id_foreign');
            }

            // -----------------------------
            // DROP OLD INDEXES (if exist)
            // -----------------------------
            $this->dropIndexIfExists('assessment_scores', $table, 'unique_student_course_semester');
            $this->dropIndexIfExists('assessment_scores', $table, 'assessment_scores_student_id_academic_year_id_semester_id_index');
            $this->dropIndexIfExists('assessment_scores', $table, 'assessment_scores_course_id_academic_year_id_semester_id_index');

            // -----------------------------
            // DROP OLD COLUMN (if exists)
            // -----------------------------
            if (Schema::hasColumn('assessment_scores', 'academic_year_id')) {
                $table->dropColumn('academic_year_id');
            }
        });

        Schema::table('assessment_scores', function (Blueprint $table) {

            // -----------------------------
            // ADD NEW COLUMN
            // -----------------------------
            if (!Schema::hasColumn('assessment_scores', 'cohort_id')) {
                $table->unsignedBigInteger('cohort_id')->after('student_id');
            }

            // -----------------------------
            // ADD NEW FK
            // -----------------------------
            $table->foreign('cohort_id')
                ->references('id')
                ->on('cohorts')
                ->onDelete('cascade');

            // -----------------------------
            // ADD INDEXES
            // -----------------------------
            $table->index(
                ['student_id', 'cohort_id', 'semester_id'],
                'assessment_scores_student_id_cohort_id_semester_id_index'
            );

            $table->index(
                ['course_id', 'cohort_id', 'semester_id'],
                'assessment_scores_course_id_cohort_id_semester_id_index'
            );

            // -----------------------------
            // ADD UNIQUE CONSTRAINT
            // -----------------------------
            $table->unique(
                ['course_id', 'student_id', 'cohort_id', 'semester_id'],
                'unique_student_course_semester_cohort'
            );
        });
    }

    public function down(): void
    {
        Schema::table('assessment_scores', function (Blueprint $table) {

            // -----------------------------
            // DROP NEW FK
            // -----------------------------
            if ($this->foreignKeyExists('assessment_scores', 'assessment_scores_cohort_id_foreign')) {
                $table->dropForeign('assessment_scores_cohort_id_foreign');
            }

            // -----------------------------
            // DROP NEW INDEXES
            // -----------------------------
            $this->dropIndexIfExists('assessment_scores', $table, 'unique_student_course_semester_cohort');
            $this->dropIndexIfExists('assessment_scores', $table, 'assessment_scores_student_id_cohort_id_semester_id_index');
            $this->dropIndexIfExists('assessment_scores', $table, 'assessment_scores_course_id_cohort_id_semester_id_index');

            // -----------------------------
            // DROP NEW COLUMN
            // -----------------------------
            if (Schema::hasColumn('assessment_scores', 'cohort_id')) {
                $table->dropColumn('cohort_id');
            }
        });

        Schema::table('assessment_scores', function (Blueprint $table) {

            // -----------------------------
            // RESTORE OLD COLUMN
            // -----------------------------
            if (!Schema::hasColumn('assessment_scores', 'academic_year_id')) {
                $table->unsignedBigInteger('academic_year_id')->after('student_id');
            }

            // -----------------------------
            // RESTORE FK
            // -----------------------------
            $table->foreign('academic_year_id')
                ->references('id')
                ->on('academic_years')
                ->onDelete('cascade');

            // -----------------------------
            // RESTORE INDEXES
            // -----------------------------
            $table->index(
                ['student_id', 'academic_year_id', 'semester_id'],
                'assessment_scores_student_id_academic_year_id_semester_id_index'
            );

            $table->index(
                ['course_id', 'academic_year_id', 'semester_id'],
                'assessment_scores_course_id_academic_year_id_semester_id_index'
            );

            // -----------------------------
            // RESTORE UNIQUE
            // -----------------------------
            $table->unique(
                ['course_id', 'student_id', 'academic_year_id', 'semester_id'],
                'unique_student_course_semester'
            );
        });
    }

    // ==================================================
    // Helpers — SAFE existence checks
    // ==================================================

    private function foreignKeyExists(string $table, string $keyName): bool
    {
        $rows = DB::select("
            SELECT 1
            FROM information_schema.TABLE_CONSTRAINTS
            WHERE CONSTRAINT_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND CONSTRAINT_NAME = ?
              AND CONSTRAINT_TYPE = 'FOREIGN KEY'
        ", [$table, $keyName]);

        return !empty($rows);
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $rows = DB::select("
            SELECT 1
            FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND INDEX_NAME = ?
        ", [$table, $indexName]);

        return !empty($rows);
    }

    private function dropIndexIfExists(string $tableName, Blueprint $table, string $indexName): void
    {
        if ($this->indexExists($tableName, $indexName)) {
            $table->dropIndex($indexName);
        }
    }
};
