<?php

namespace App\Console\Commands;

use App\Models\Student;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SyncStudentUserIds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'students:sync-user-ids 
                            {--dry-run : Simulate the process without making changes}
                            {--force : Skip confirmation and proceed}
                            {--batch=100 : Number of records to process in each batch}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync student records with user accounts, creating new user accounts if needed';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Set up logging
        $logFile = 'students-sync.log';
        Log::channel('daily')->info('Starting student user ID sync '.($this->option('dry-run') ? '(DRY RUN)' : ''));
        $this->info('Starting student user ID sync process...');

        // Check if this is a dry run
        $isDryRun = $this->option('dry-run');
        if ($isDryRun) {
            $this->info('DRY RUN MODE: No database changes will be made');
        }

        // Get total count for progress bar
        $totalStudents = Student::count();

        if ($totalStudents === 0) {
            $this->warn('No students found in the database.');

            return 0;
        }

        $this->info("Found {$totalStudents} students to process");

        // Request confirmation unless --force flag is used
        if (! $this->option('force') && ! $isDryRun) {
            if (! $this->confirm('This will update user_id for all students and create user accounts where needed. Continue?')) {
                $this->info('Operation cancelled by user.');

                return 0;
            }
        }

        // Initialize counters
        $updated = 0;
        $created = 0;
        $skipped = 0;
        $errored = 0;

        // Create progress bar
        $bar = $this->output->createProgressBar($totalStudents);
        $bar->start();

        // Process in batches
        $batchSize = (int) $this->option('batch');

        Student::query()
            ->chunk($batchSize, function ($students) use ($isDryRun, $bar, &$updated, &$created, &$skipped, &$errored) {
                foreach ($students as $student) {
                    try {
                        // Skip if student already has a user_id
                        if (! empty($student->user_id)) {
                            $this->logInfo("Student ID {$student->id} already has user_id {$student->user_id}", $isDryRun);
                            $skipped++;
                            $bar->advance();

                            continue;
                        }

                        // Skip if student doesn't have an email
                        if (empty($student->email)) {
                            $this->logWarning("Student ID {$student->id} has no email address, skipping", $isDryRun);
                            $skipped++;
                            $bar->advance();

                            continue;
                        }

                        // Validate email
                        $validator = Validator::make(['email' => $student->email], [
                            'email' => 'required|email',
                        ]);

                        if ($validator->fails()) {
                            $this->logWarning("Student ID {$student->id} has invalid email: {$student->email}, skipping", $isDryRun);
                            $skipped++;
                            $bar->advance();

                            continue;
                        }

                        // Find user with matching email
                        $user = User::where('email', $student->email)->first();

                        if ($user) {
                            // Case 1: User exists, update student's user_id
                            if (! $isDryRun) {
                                $student->user_id = $user->id;
                                $student->save();
                            }

                            $this->logInfo("Updated student ID {$student->id} with user_id {$user->id}", $isDryRun);
                            $updated++;
                        } else {
                            // Case 2: No user exists, create one
                            $name = $student->full_name ?? $student->first_name.' '.$student->last_name ?? 'Student '.$student->id;
                            $password = Str::random(12);

                            if (! $isDryRun) {
                                DB::transaction(function () use ($student, $name, $password, &$user) {
                                    // Create user
                                    $user = User::create([
                                        'name' => $name,
                                        'email' => $student->email,
                                        'password' => Hash::make($password),
                                    ]);

                                    // Assign Student role to newly created user
                                    $studentRole = \Spatie\Permission\Models\Role::where('name', 'Student')->first();
                                    if ($studentRole) {
                                        $user->assignRole($studentRole);
                                        Log::info("Assigned Student role to user {$user->email}");
                                    } else {
                                        Log::warning('Student role not found in system');
                                    }

                                    // Update student
                                    $student->user_id = $user->id;
                                    $student->save();
                                });
                            } else {
                                // For dry run, simulate user ID
                                $user = (object) [
                                    'id' => '[new-user]',
                                    'email' => $student->email,
                                ];
                            }

                            $this->logInfo("Created user (ID: {$user->id}, Email: {$user->email}) for student ID {$student->id}", $isDryRun);
                            $created++;
                        }
                    } catch (\Exception $e) {
                        $this->logError("Error processing student ID {$student->id}: ".$e->getMessage(), $isDryRun);
                        $errored++;
                    }

                    $bar->advance();
                }
            });

        $bar->finish();
        $this->newLine(2);

        // Summary
        $this->info('Sync completed!');
        $this->info("Updated: {$updated} students");
        $this->info("Created: {$created} new user accounts");
        $this->info("Skipped: {$skipped} students");
        $this->info("Errors: {$errored} students");

        if ($isDryRun) {
            $this->warn('This was a dry run. No actual changes were made to the database.');
            $this->info('To perform actual changes, run the command without the --dry-run option.');
        }

        // Final log entry
        $message = "Completed student user ID sync: Updated {$updated}, Created {$created}, Skipped {$skipped}, Errors {$errored}".
                   ($isDryRun ? ' (DRY RUN)' : '');
        Log::channel('daily')->info($message);

        return 0;
    }

    /**
     * Log info message to console and log file
     */
    private function logInfo($message, $isDryRun)
    {
        $prefix = $isDryRun ? '[DRY RUN] ' : '';
        $this->line($prefix.$message);
        Log::channel('daily')->info($prefix.$message);
    }

    /**
     * Log warning message to console and log file
     */
    private function logWarning($message, $isDryRun)
    {
        $prefix = $isDryRun ? '[DRY RUN] ' : '';
        $this->warn($prefix.$message);
        Log::channel('daily')->warning($prefix.$message);
    }

    /**
     * Log error message to console and log file
     */
    private function logError($message, $isDryRun)
    {
        $prefix = $isDryRun ? '[DRY RUN] ' : '';
        $this->error($prefix.$message);
        Log::channel('daily')->error($prefix.$message);
    }
}
