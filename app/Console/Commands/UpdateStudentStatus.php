<?php

namespace App\Console\Commands;

use App\Models\Cohort;
use App\Models\Student;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateStudentStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'students:update-status
                            {--status=Active : The status to set for the students (Active, Inactive, Pending, etc.)}
                            {--cohort= : Optional cohort name to filter students by}
                            {--dry-run : Run the command without making any changes to see what would be affected}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the status of students, optionally filtered by cohort';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $status = $this->option('status');
        $cohortName = $this->option('cohort');
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->info('Running in dry-run mode. No changes will be made.');
        }

        // Build the query
        $query = Student::query();

        // If cohort is provided, filter by cohort
        if ($cohortName) {
            $cohort = Cohort::where('name', $cohortName)->first();

            if (! $cohort) {
                $this->error("Cohort '{$cohortName}' not found.");

                return 1;
            }

            $query->where('cohort_id', $cohort->id);
            $this->info("Filtering students by cohort: {$cohortName}");
        }

        // Count the students that will be affected
        $studentCount = $query->count();

        if ($studentCount === 0) {
            $this->warn('No students match the criteria.');

            return 0;
        }

        $this->info("{$studentCount} students will have their status updated to '{$status}'.");

        // Confirm before proceeding unless in dry-run mode
        if (! $isDryRun && ! $this->confirm('Do you wish to continue?', true)) {
            $this->info('Operation cancelled.');

            return 0;
        }

        // Proceed with the update
        if (! $isDryRun) {
            $updated = $query->update(['status' => $status]);

            $this->info("Successfully updated status to '{$status}' for {$updated} students.");

            // Log the action
            Log::info('Student status bulk update', [
                'status' => $status,
                'cohort' => $cohortName,
                'affected_students' => $updated,
            ]);
        } else {
            // In dry-run mode, just show a sample of students that would be updated
            $sampleStudents = $query->take(5)->get(['id', 'student_id', 'first_name', 'last_name', 'status', 'cohort_id']);

            $this->info('Sample of students that would be affected:');
            $this->table(
                ['ID', 'Student ID', 'Name', 'Current Status', 'Cohort ID'],
                $sampleStudents->map(function ($student) {
                    return [
                        $student->id,
                        $student->student_id,
                        $student->first_name.' '.$student->last_name,
                        $student->status ?? 'Not set',
                        $student->cohort_id,
                    ];
                })
            );
        }

        return 0;
    }
}
