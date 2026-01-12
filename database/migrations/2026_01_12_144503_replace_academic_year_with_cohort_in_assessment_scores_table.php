<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('assessment_scores', function (Blueprint $table) {
            // 1. Safely drop old foreign key if exists
            $this->dropForeignIfExists($table, 'assessment_scores_academic_year_id_foreign');

            // 2. Drop old unique & regular indexes (MySQL/PostgreSQL friendly)
            $this->dropIndexIfExists($table, 'unique_student_course_semester');
            $this->dropIndexIfExists($table, 'assessment_scores_student_id_academic_year_id_semester_id_index');
            $this->dropIndexIfExists($table, 'assessment_scores_course_id_academic_year_id_semester_id_index');

            // 3. Drop the old column
            $table->dropColumn('academic_year_id');
        });

        // 4. Add new column + constraints in one go
        Schema::table('assessment_scores', function (Blueprint $table) {
            $table->unsignedBigInteger('cohort_id')
                ->after('student_id')
                ->nullable(false); // adjust to ->nullable() if it should be nullable during transition

            $table->foreign('cohort_id')
                ->references('id')
                ->on('cohorts')
                ->onDelete('cascade');

            // Composite indexes
            $table->index(['student_id', 'cohort_id', 'semester_id'],
                'assessment_scores_student_id_cohort_id_semester_id_index');

            $table->index(['course_id', 'cohort_id', 'semester_id'],
                'assessment_scores_course_id_cohort_id_semester_id_index');

            // Unique constraint
            $table->unique(
                ['course_id', 'student_id', 'cohort_id', 'semester_id'],
                'unique_student_course_semester_cohort'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assessment_scores', function (Blueprint $table) {
            // 1. Drop new foreign key & indexes
            $this->dropForeignIfExists($table, 'assessment_scores_cohort_id_foreign');
            $this->dropIndexIfExists($table, 'unique_student_course_semester_cohort');
            $this->dropIndexIfExists($table, 'assessment_scores_student_id_cohort_id_semester_id_index');
            $this->dropIndexIfExists($table, 'assessment_scores_course_id_cohort_id_semester_id_index');

            // 2. Drop new column
            $table->dropColumn('cohort_id');
        });

        // 3. Restore old structure
        Schema::table('assessment_scores', function (Blueprint $table) {
            $table->unsignedBigInteger('academic_year_id')
                ->after('student_id');

            $table->foreign('academic_year_id')
                ->references('id')
                ->on('academic_years')
                ->onDelete('cascade');

            $table->index(['student_id', 'academic_year_id', 'semester_id'],
                'assessment_scores_student_id_academic_year_id_semester_id_index');

            $table->index(['course_id', 'academic_year_id', 'semester_id'],
                'assessment_scores_course_id_academic_year_id_semester_id_index');

            $table->unique(
                ['course_id', 'student_id', 'academic_year_id', 'semester_id'],
                'unique_student_course_semester'
            );
        });
    }

    /**
     * Safely drop a foreign key if it exists
     */
    private function dropForeignIfExists(Blueprint $table, string $foreignKeyName): void
    {
        try {
            $table->dropForeign($foreignKeyName);
        } catch (QueryException $e) {
            // Silent fail - foreign key didn't exist
        }
    }

    /**
     * Safely drop an index if it exists
     */
    private function dropIndexIfExists(Blueprint $table, string $indexName): void
    {
        try {
            $table->dropIndex($indexName);
        } catch (QueryException $e) {
            // Silent fail - index didn't exist
        }
    }
};