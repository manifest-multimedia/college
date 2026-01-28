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
            // ADD NEW COLUMN (if not exists)
            // -----------------------------
            if (!Schema::hasColumn('assessment_scores', 'cohort_id')) {
                // Cohorts table uses unsignedInteger for its primary key, keep types compatible
                $table->unsignedInteger('cohort_id')->nullable()->after('student_id');
            }
        });

        // -----------------------------
        // BACKFILL DATA FOR cohort_id
        // -----------------------------
        // Set cohort_id to a default valid value (e.g., the first cohort ID)
        $defaultCohortId = DB::table('cohorts')->value('id'); // Fetch the first cohort ID
        if ($defaultCohortId) {
            DB::table('assessment_scores')->update(['cohort_id' => $defaultCohortId]);
        } else {
            // If no cohorts exist, create a placeholder cohort and use its ID
            $defaultCohortId = DB::table('cohorts')->insertGetId([
                'name' => 'Default Cohort',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('assessment_scores')->update(['cohort_id' => $defaultCohortId]);
        }

        Schema::table('assessment_scores', function (Blueprint $table) {

            // -----------------------------
            // ADD NEW FK (if not exists)
            // -----------------------------
            $table->foreign('cohort_id')
                ->references('id')
                ->on('cohorts')
                ->onDelete('cascade');

            // -----------------------------
            // ADD NEW INDEXES (if not exists)
            // -----------------------------
            if (!$this->indexExists('assessment_scores', 'assessment_scores_student_id_cohort_id_semester_id_index')) {
                $table->index(
                    ['student_id', 'cohort_id', 'semester_id'],
                    'assessment_scores_student_id_cohort_id_semester_id_index'
                );
            }

            if (!$this->indexExists('assessment_scores', 'assessment_scores_course_id_cohort_id_semester_id_index')) {
                $table->index(
                    ['course_id', 'cohort_id', 'semester_id'],
                    'assessment_scores_course_id_cohort_id_semester_id_index'
                );
            }

            if (!$this->indexExists('assessment_scores', 'unique_student_course_semester_cohort')) {
                $table->unique(
                    ['course_id', 'student_id', 'cohort_id', 'semester_id'],
                    'unique_student_course_semester_cohort'
                );
            }
        });
    }

    public function down(): void
    {
        Schema::table('assessment_scores', function (Blueprint $table) {

            // -----------------------------
            // REMOVE NEW FK (if exists)
            // -----------------------------
            if ($this->foreignKeyExists('assessment_scores', 'assessment_scores_cohort_id_foreign')) {
                $table->dropForeign('assessment_scores_cohort_id_foreign');
            }

            // -----------------------------
            // REMOVE NEW INDEXES (if exists)
            // -----------------------------
            if ($this->indexExists('assessment_scores', 'assessment_scores_student_id_cohort_id_semester_id_index')) {
                $table->dropIndex('assessment_scores_student_id_cohort_id_semester_id_index');
            }

            if ($this->indexExists('assessment_scores', 'assessment_scores_course_id_cohort_id_semester_id_index')) {
                $table->dropIndex('assessment_scores_course_id_cohort_id_semester_id_index');
            }

            if ($this->indexExists('assessment_scores', 'unique_student_course_semester_cohort')) {
                $table->dropIndex('unique_student_course_semester_cohort');
            }

            // -----------------------------
            // REMOVE NEW COLUMN (if exists)
            // -----------------------------
            if (Schema::hasColumn('assessment_scores', 'cohort_id')) {
                $table->dropColumn('cohort_id');
            }
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
};