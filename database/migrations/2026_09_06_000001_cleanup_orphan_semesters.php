<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Clean up unreferenced legacy orphan semesters that lack an academic_year_id.
     */
    public function up(): void
    {
        // Find all semesters with NULL academic_year_id
        $orphanIds = DB::table('semesters')->whereNull('academic_year_id')->pluck('id')->toArray();

        if (empty($orphanIds)) {
            return;
        }

        // Tables that reference semester_id
        $tables = ['fee_structures', 'student_fee_bills', 'course_registrations', 'assessment_scores', 'exam_clearances', 'subjects'];

        foreach ($orphanIds as $orphanId) {
            $hasReferences = false;
            foreach ($tables as $tableName) {
                if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'semester_id')) {
                    if (DB::table($tableName)->where('semester_id', $orphanId)->exists()) {
                        $hasReferences = true;
                        break;
                    }
                }
            }

            // Only delete if totally unreferenced
            if (! $hasReferences) {
                DB::table('semesters')->where('id', $orphanId)->delete();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse action needed for unreferenced orphan cleanup
    }
};
