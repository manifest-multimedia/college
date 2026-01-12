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
        // Step 1: Drop the foreign key constraint first (no IF EXISTS for DROP FOREIGN KEY in MySQL)
        try {
            DB::statement('ALTER TABLE assessment_scores DROP FOREIGN KEY assessment_scores_academic_year_id_foreign');
        } catch (\Exception $e) {
            // Foreign key might not exist
        }
        
        // Step 2: Drop the unique constraint
        try {
            DB::statement('ALTER TABLE assessment_scores DROP INDEX unique_student_course_semester');
        } catch (\Exception $e) {
            // Index might not exist with this exact name
        }
        
        // Step 3: Drop the indexes
        try {
            DB::statement('ALTER TABLE assessment_scores DROP INDEX assessment_scores_student_id_academic_year_id_semester_id_index');
        } catch (\Exception $e) {
            // Index might not exist
        }
        
        try {
            DB::statement('ALTER TABLE assessment_scores DROP INDEX assessment_scores_course_id_academic_year_id_semester_id_index');
        } catch (\Exception $e) {
            // Index might not exist
        }
        
        // Step 4: Drop the old column and add the new one
        Schema::table('assessment_scores', function (Blueprint $table) {
            $table->dropColumn('academic_year_id');
        });
        
        // Step 5: Add new cohort_id column with all constraints
        Schema::table('assessment_scores', function (Blueprint $table) {
            $table->unsignedInteger('cohort_id')->after('student_id');
            $table->foreign('cohort_id')->references('id')->on('cohorts')->onDelete('cascade');
            $table->index(['student_id', 'cohort_id', 'semester_id']);
            $table->index(['course_id', 'cohort_id', 'semester_id']);
            $table->unique(['course_id', 'student_id', 'cohort_id', 'semester_id'], 'unique_student_course_semester');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Step 1: Drop the foreign key constraint (no IF EXISTS for DROP FOREIGN KEY in MySQL)
        try {
            DB::statement('ALTER TABLE assessment_scores DROP FOREIGN KEY assessment_scores_cohort_id_foreign');
        } catch (\Exception $e) {
            // Foreign key might not exist
        }
        
        // Step 2: Drop the unique constraint
        try {
            DB::statement('ALTER TABLE assessment_scores DROP INDEX unique_student_course_semester');
        } catch (\Exception $e) {
            // Index might not exist
        }
        
        // Step 3: Drop the indexes
        try {
            DB::statement('ALTER TABLE assessment_scores DROP INDEX assessment_scores_student_id_cohort_id_semester_id_index');
        } catch (\Exception $e) {
            // Index might not exist
        }
        
        try {
            DB::statement('ALTER TABLE assessment_scores DROP INDEX assessment_scores_course_id_cohort_id_semester_id_index');
        } catch (\Exception $e) {
            // Index might not exist
        }
        
        // Step 4: Drop cohort_id and restore academic_year_id
        Schema::table('assessment_scores', function (Blueprint $table) {
            $table->dropColumn('cohort_id');
        });
        
        // Step 5: Restore academic_year_id column with all constraints
        Schema::table('assessment_scores', function (Blueprint $table) {
            $table->unsignedInteger('academic_year_id')->after('student_id');
            $table->foreign('academic_year_id')->references('id')->on('academic_years')->onDelete('cascade');
            $table->index(['student_id', 'academic_year_id', 'semester_id']);
            $table->index(['course_id', 'academic_year_id', 'semester_id']);
            $table->unique(['course_id', 'student_id', 'academic_year_id', 'semester_id'], 'unique_student_course_semester');
        });
    }
};


