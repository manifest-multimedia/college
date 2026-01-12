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
            // Drop the unique constraint first
            $table->dropUnique('unique_student_course_semester');
        });
        
        Schema::table('assessment_scores', function (Blueprint $table) {
            // Drop existing indexes
            $table->dropIndex('assessment_scores_student_id_academic_year_id_semester_id_index');
            $table->dropIndex('assessment_scores_course_id_academic_year_id_semester_id_index');
            
            // Drop foreign key
            $table->dropForeignKey('assessment_scores_academic_year_id_foreign');
            
            // Drop the old column
            $table->dropColumn('academic_year_id');
        });
        
        Schema::table('assessment_scores', function (Blueprint $table) {
            // Add new cohort_id column
            $table->unsignedInteger('cohort_id')->after('student_id');
            $table->foreign('cohort_id')->references('id')->on('cohorts')->onDelete('cascade');
            
            // Add indexes with cohort_id
            $table->index(['student_id', 'cohort_id', 'semester_id']);
            $table->index(['course_id', 'cohort_id', 'semester_id']);
            
            // Add unique constraint with cohort_id
            $table->unique(['course_id', 'student_id', 'cohort_id', 'semester_id'], 'unique_student_course_semester');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assessment_scores', function (Blueprint $table) {
            // Drop unique constraint
            $table->dropUnique('unique_student_course_semester');
        });
        
        Schema::table('assessment_scores', function (Blueprint $table) {
            // Drop new indexes and foreign key
            $table->dropIndex('assessment_scores_student_id_cohort_id_semester_id_index');
            $table->dropIndex('assessment_scores_course_id_cohort_id_semester_id_index');
            $table->dropForeignKey('assessment_scores_cohort_id_foreign');
            
            // Drop cohort_id column
            $table->dropColumn('cohort_id');
        });
        
        Schema::table('assessment_scores', function (Blueprint $table) {
            // Restore academic_year_id
            $table->unsignedInteger('academic_year_id')->after('student_id');
            $table->foreign('academic_year_id')->references('id')->on('academic_years')->onDelete('cascade');
            
            // Restore old indexes
            $table->index(['student_id', 'academic_year_id', 'semester_id']);
            $table->index(['course_id', 'academic_year_id', 'semester_id']);
            
            // Restore old unique constraint
            $table->unique(['course_id', 'student_id', 'academic_year_id', 'semester_id'], 'unique_student_course_semester');
        });
    }
};


