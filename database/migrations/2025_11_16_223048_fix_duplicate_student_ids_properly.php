<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    // Use Should run to prevent this from running in production
    public function shouldRun(): bool
    {
        return app()->environment('local', 'staging');
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, remove any previously fixed IDs with suffixes (like _4, _2, etc.)
        $this->cleanupMalformedIds();

        // Then regenerate proper sequential IDs for any duplicates or malformed IDs
        $this->regenerateDuplicateIds();

        Log::info('Student ID duplicate resolution completed', [
            'migration' => '2025_11_16_223048_fix_duplicate_student_ids_properly',
            'method' => 'proper_regeneration',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration improves data quality and should not be reversed
        // If needed, individual student IDs can be manually corrected
        Log::warning('Attempted to reverse student ID fix migration - this is not supported for data quality reasons');
    }

    /**
     * Clean up malformed IDs that were previously "fixed" with suffixes
     */
    private function cleanupMalformedIds(): void
    {
        // Find students with IDs that have suffixes like _2, _4, etc.
        $malformedStudents = DB::table('students')
            ->whereNotNull('student_id')
            ->where('student_id', 'REGEXP', '.*_[0-9]+$')
            ->get(['id', 'student_id', 'first_name', 'last_name']);

        if ($malformedStudents->count() > 0) {
            echo "Found {$malformedStudents->count()} students with malformed IDs (with suffixes)\n";

            foreach ($malformedStudents as $student) {
                // Remove the suffix to restore base ID (which may still be duplicate)
                $baseId = preg_replace('/_[0-9]+$/', '', $student->student_id);

                DB::table('students')
                    ->where('id', $student->id)
                    ->update(['student_id' => $baseId]);

                echo "Cleaned ID for {$student->first_name} {$student->last_name}: {$student->student_id} → {$baseId}\n";
            }
        }
    }

    /**
     * Regenerate proper sequential IDs for duplicates and malformed IDs
     */
    private function regenerateDuplicateIds(): void
    {
        try {
            echo "Regenerating student IDs with proper sequencing...\n";

            // Run our custom command to fix duplicates properly
            Artisan::call('students:regenerate-ids', [
                '--duplicates-only' => true,
                '--force' => true,
            ]);

            $output = Artisan::output();
            echo $output;

        } catch (\Exception $e) {
            Log::error('Error during student ID regeneration in migration', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            echo 'Error during regeneration: '.$e->getMessage()."\n";
            echo "You may need to run 'php artisan students:regenerate-ids --duplicates-only' manually\n";
        }
    }
};
