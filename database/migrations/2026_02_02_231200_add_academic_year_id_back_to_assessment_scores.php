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
        // Step 1: Add column as nullable if it doesn't exist
        Schema::table('assessment_scores', function (Blueprint $table) {
            if (! Schema::hasColumn('assessment_scores', 'academic_year_id')) {
                $table->unsignedInteger('academic_year_id')->nullable()->after('semester_id');
            }
        });

        // Step 2: Populate existing records with current academic year
        $currentAcademicYear = DB::table('academic_years')
            ->where('is_current', true)
            ->first();

        if ($currentAcademicYear) {
            DB::table('assessment_scores')
                ->whereNull('academic_year_id')
                ->update(['academic_year_id' => $currentAcademicYear->id]);
        } else {
            // If no current academic year, use the most recent one
            $latestAcademicYear = DB::table('academic_years')
                ->orderBy('start_date', 'desc')
                ->first();

            if ($latestAcademicYear) {
                DB::table('assessment_scores')
                    ->whereNull('academic_year_id')
                    ->update(['academic_year_id' => $latestAcademicYear->id]);
            }
        }

        // Step 2b: Delete any records that still have NULL or invalid academic_year_id
        // Get all valid academic year IDs
        $validAcademicYearIds = DB::table('academic_years')->pluck('id')->toArray();
        
        // Delete records with NULL academic_year_id
        DB::table('assessment_scores')
            ->whereNull('academic_year_id')
            ->delete();
        
        // Delete records with invalid academic_year_id (not in academic_years table)
        if (!empty($validAcademicYearIds)) {
            DB::table('assessment_scores')
                ->whereNotNull('academic_year_id')
                ->whereNotIn('academic_year_id', $validAcademicYearIds)
                ->delete();
        }

        // Step 3: Make column NOT NULL and add foreign key
        Schema::table('assessment_scores', function (Blueprint $table) {
            $table->unsignedInteger('academic_year_id')->nullable(false)->change();
        });
        
        // Add foreign key in separate schema call after column is NOT NULL
        // Only add if it doesn't already exist
        $foreignKeyExists = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'assessment_scores' 
            AND COLUMN_NAME = 'academic_year_id' 
            AND REFERENCED_TABLE_NAME = 'academic_years'
        ");
        
        if (empty($foreignKeyExists)) {
            Schema::table('assessment_scores', function (Blueprint $table) {
                $table->foreign('academic_year_id')->references('id')->on('academic_years')->onDelete('cascade');
            });
        }

        // Step 4: Update unique constraint to include academic_year_id
        // Check if old constraint exists before trying to drop it
        $oldConstraintExists = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'assessment_scores' 
            AND CONSTRAINT_NAME = 'unique_student_course_semester'
        ");
        
        if (!empty($oldConstraintExists)) {
            Schema::table('assessment_scores', function (Blueprint $table) {
                $table->dropUnique('unique_student_course_semester');
            });
        }

        // Check if new constraint already exists
        $newConstraintExists = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'assessment_scores' 
            AND CONSTRAINT_NAME = 'unique_student_course_year_semester'
        ");
        
        if (empty($newConstraintExists)) {
            Schema::table('assessment_scores', function (Blueprint $table) {
                $table->unique(['course_id', 'student_id', 'cohort_id', 'semester_id', 'academic_year_id'], 'unique_student_course_year_semester');
            });
        }
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
