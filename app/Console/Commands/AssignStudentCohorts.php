<?php

namespace App\Console\Commands;

use App\Models\Cohort;
use App\Models\Student;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AssignStudentCohorts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'students:assign-cohorts {--dry-run : Run in simulation mode without making changes} {--force : Skip confirmation prompts}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign students to cohorts based on their program';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->info('DRY RUN MODE: No database changes will be made');
        }

        // Define the cohort mapping: program name => cohort name
        $cohortMapping = [
            'Registered General Nursing' => 'RGN 7',
            'Registered Midwifery' => 'DM 9',
        ];

        // Ensure the cohorts exist
        $cohorts = [];
        foreach ($cohortMapping as $programName => $cohortName) {
            $cohort = $this->findOrCreateCohort($cohortName, $isDryRun);
            $cohorts[$programName] = $cohort;

            if (! $isDryRun) {
                $this->info("Cohort '{$cohortName}' ".($cohort->wasRecentlyCreated ? 'created' : 'found'));
            } else {
                $this->info('Would '.($cohort->exists ? 'use existing' : 'create new')." cohort '{$cohortName}'");
            }
        }

        // Get all students that belong to these programs
        $this->info('Retrieving students from specified programs...');
        $students = Student::whereHas('collegeClass', function ($query) use ($cohortMapping) {
            $query->whereIn('name', array_keys($cohortMapping));
        })->get();

        $totalStudents = $students->count();

        if ($totalStudents === 0) {
            $this->info('No students found in the specified programs.');

            return;
        }

        $this->info("Found {$totalStudents} students to process");

        // Request confirmation unless --force flag is used
        if (! $this->option('force') && ! $isDryRun) {
            if (! $this->confirm("This will assign {$totalStudents} students to the specified cohorts. Continue?")) {
                $this->info('Operation cancelled by user.');

                return;
            }
        }

        // Initialize counters
        $assigned = 0;
        $skipped = 0;
        $unchanged = 0;

        // Create progress bar
        $bar = $this->output->createProgressBar($totalStudents);
        $bar->start();

        // Group students by program for processing
        $studentsByProgram = $students->groupBy(function ($student) {
            return $student->collegeClass->name ?? 'Unknown';
        });

        foreach ($studentsByProgram as $programName => $programStudents) {
            // Skip if program not in mapping
            if (! isset($cohorts[$programName])) {
                foreach ($programStudents as $student) {
                    $this->logWarning("Student ID {$student->id} has program '{$programName}' which is not mapped to any cohort, skipping", $isDryRun);
                    $skipped++;
                    $bar->advance();
                }

                continue;
            }

            $cohort = $cohorts[$programName];

            // In dry run mode, the cohort object might not have a real ID
            $cohortId = $isDryRun && ! $cohort->exists ? null : $cohort->id;

            foreach ($programStudents as $student) {
                try {
                    // Skip if student already has this cohort (and it exists)
                    if (! $isDryRun && $cohort->exists && $student->cohort_id == $cohort->id) {
                        $this->logInfo("Student ID {$student->id} already assigned to cohort '{$cohort->name}', skipping", $isDryRun);
                        $unchanged++;
                        $bar->advance();

                        continue;
                    }

                    // In dry run, try to check if there's a cohort with this name
                    if ($isDryRun) {
                        $existingCohort = Cohort::where('name', $cohort->name)->first();
                        if ($existingCohort && $student->cohort_id == $existingCohort->id) {
                            $this->logInfo("Student ID {$student->id} already assigned to cohort '{$cohort->name}', skipping", $isDryRun);
                            $unchanged++;
                            $bar->advance();

                            continue;
                        }
                    }

                    // Update the student's cohort
                    if (! $isDryRun) {
                        $student->cohort_id = $cohortId;
                        $student->save();
                    }

                    $this->logInfo("Assigned student ID {$student->id} to cohort '{$cohort->name}'", $isDryRun);
                    $assigned++;
                } catch (\Exception $e) {
                    $this->logError("Error processing student ID {$student->id}: ".$e->getMessage(), $isDryRun);
                    $skipped++;
                }

                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine(2);

        // Summary
        $this->info('Assignment completed!');
        $this->info("Assigned: {$assigned} students");
        $this->info("Unchanged: {$unchanged} students");
        $this->info("Skipped: {$skipped} students");

        if ($isDryRun) {
            $this->newLine();
            $this->info('This was a dry run. No actual changes were made to the database.');
        }
    }

    /**
     * Find or create a cohort by name
     *
     * @param  string  $name  The cohort name
     * @param  bool  $isDryRun  Whether this is a dry run
     * @return Cohort
     */
    protected function findOrCreateCohort($name, $isDryRun)
    {
        $cohort = Cohort::where('name', $name)->first();

        if (! $cohort && ! $isDryRun) {
            $cohort = Cohort::create([
                'name' => $name,
                'description' => 'Automatically created cohort',
                'slug' => Str::slug($name),
                'is_active' => true,
                'is_deleted' => false,
                'created_by' => null,
            ]);

            Log::channel('academics')->info('Created cohort', [
                'name' => $name,
                'id' => $cohort->id,
            ]);

            $cohort->wasRecentlyCreated = true;
            $cohort->exists = true;
        } elseif (! $cohort && $isDryRun) {
            // For dry run, create a dummy object for reporting
            $cohort = new Cohort(['name' => $name]);
            $cohort->exists = false;
            $cohort->wasRecentlyCreated = true;
        } else {
            $cohort->wasRecentlyCreated = false;
            $cohort->exists = true;
        }

        return $cohort;
    }

    /**
     * Log an info level message
     */
    protected function logInfo($message, $isDryRun)
    {
        $prefix = $isDryRun ? '[DRY RUN] ' : '';
        Log::channel('academics')->info($prefix.$message);

        // For verbose output, you could uncomment this
        // $this->line($prefix . $message);
    }

    /**
     * Log a warning level message
     */
    protected function logWarning($message, $isDryRun)
    {
        $prefix = $isDryRun ? '[DRY RUN] ' : '';
        Log::channel('academics')->warning($prefix.$message);

        // For verbose output, you could uncomment this
        // $this->line($prefix . $message);
    }

    /**
     * Log an error level message
     */
    protected function logError($message, $isDryRun)
    {
        $prefix = $isDryRun ? '[DRY RUN] ' : '';
        Log::channel('academics')->error($prefix.$message);
        $this->error($prefix.$message);
    }
}
