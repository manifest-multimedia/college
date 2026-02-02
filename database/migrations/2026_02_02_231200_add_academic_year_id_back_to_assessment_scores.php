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

        // Step 3: Make column NOT NULL and add foreign key
        Schema::table('assessment_scores', function (Blueprint $table) {
            $table->unsignedInteger('academic_year_id')->nullable(false)->change();
            
            // Add foreign key if it doesn't exist
            try {
                $table->foreign('academic_year_id')->references('id')->on('academic_years')->onDelete('cascade');
            } catch (\Exception $e) {
                // Foreign key might already exist, continue
            }
        });

        // Step 4: Update unique constraint to include academic_year_id
        Schema::table('assessment_scores', function (Blueprint $table) {
            // Drop old unique constraint if it exists
            try {
                $table->dropUnique('unique_student_course_semester');
            } catch (\Exception $e) {
                // Constraint might not exist, continue
            }

            // Add new unique constraint with academic_year_id
            try {
                $table->unique(['course_id', 'student_id', 'cohort_id', 'semester_id', 'academic_year_id'], 'unique_student_course_year_semester');
            } catch (\Exception $e) {
                // Constraint might already exist, continue
            }
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
