<?php

namespace App\Console\Commands;

use App\Models\Student;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class ResetStudentPasswords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'students:reset-passwords
                            {--dry-run : Run the command without making any changes}
                            {--batch=100 : Number of records to process per batch}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset all student account passwords to their student_id (index number)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');
        $batchSize = (int) $this->option('batch');

        if ($isDryRun) {
            $this->warn('DRY RUN MODE: No database changes will be made');
            $this->newLine();
        }

        $this->info('Starting student password reset process...');
        $this->newLine();

        // Get count of students with user accounts
        $totalStudents = Student::whereNotNull('user_id')
            ->whereNotNull('student_id')
            ->whereNotNull('email')
            ->count();

        if ($totalStudents === 0) {
            $this->error('No eligible students found with user accounts and student IDs.');

            return self::FAILURE;
        }

        $this->info("Found {$totalStudents} students with user accounts and student IDs.");
        $this->newLine();

        // Initialize counters
        $updated = 0;
        $skipped = 0;
        $errored = 0;

        // Create progress bar
        $bar = $this->output->createProgressBar($totalStudents);
        $bar->start();

        // Process in batches
        Student::whereNotNull('user_id')
            ->whereNotNull('student_id')
            ->whereNotNull('email')
            ->with('user')
            ->chunk($batchSize, function ($students) use ($isDryRun, $bar, &$updated, &$skipped, &$errored) {
                foreach ($students as $student) {
                    try {
                        // Skip if student doesn't have a valid student_id
                        if (empty(trim($student->student_id))) {
                            $this->logInfo("Student ID {$student->id} has empty student_id", $isDryRun);
                            $skipped++;
                            $bar->advance();

                            continue;
                        }

                        // Skip if student doesn't have a user account
                        if (! $student->user) {
                            $this->logInfo("Student ID {$student->id} has no user account", $isDryRun);
                            $skipped++;
                            $bar->advance();

                            continue;
                        }

                        // Reset password to student_id
                        if (! $isDryRun) {
                            $student->user->password = Hash::make($student->student_id);
                            $student->user->save();

                            Log::info("Reset password for student ID {$student->id} ({$student->email}) - User ID {$student->user->id}");
                        }

                        $this->logInfo("Reset password for student ID {$student->id} ({$student->email}) to '{$student->student_id}'", $isDryRun);
                        $updated++;
                    } catch (\Exception $e) {
                        $this->logError("Error processing student ID {$student->id}: {$e->getMessage()}", $isDryRun);
                        $errored++;
                    }

                    $bar->advance();
                }
            });

        $bar->finish();
        $this->newLine(2);

        // Display summary
        $this->info('Password Reset Summary:');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Students Processed', $totalStudents],
                ['Passwords Reset', $updated],
                ['Skipped', $skipped],
                ['Errors', $errored],
            ]
        );

        if ($isDryRun) {
            $this->newLine();
            $this->warn('This was a dry run. No actual changes were made to the database.');
            $this->info('Run without --dry-run to apply the changes.');
        } else {
            $this->newLine();
            $this->success('Password reset completed!');
            $this->info('Students can now login with their email and student ID (index number) as password.');
        }

        return self::SUCCESS;
    }

    /**
     * Log information message
     */
    private function logInfo(string $message, bool $isDryRun): void
    {
        if ($this->output->isVerbose()) {
            $prefix = $isDryRun ? '[DRY RUN] ' : '';
            $this->line("{$prefix}{$message}");
        }
    }

    /**
     * Log error message
     */
    private function logError(string $message, bool $isDryRun): void
    {
        $prefix = $isDryRun ? '[DRY RUN] ' : '';
        $this->error("{$prefix}{$message}");
        Log::error($message);
    }
}
