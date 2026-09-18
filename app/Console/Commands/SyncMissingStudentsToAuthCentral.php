<?php

namespace App\Console\Commands;

use App\Models\Student;
use App\Models\User;
use App\Services\AuthCentralSyncBridgeService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncMissingStudentsToAuthCentral extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'authcentral:sync-missing-students 
                            {--dry-run : Check how many students are missing without writing to AuthCentral}
                            {--limit= : Limit number of students to process}
                            {--cohort= : Limit to a specific cohort ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize students who exist in College but are missing in AuthCentral so they can use SSO';

    /**
     * Execute the console command.
     */
    public function handle(AuthCentralSyncBridgeService $bridge): int
    {
        $this->info('Checking AuthCentral database connection...');

        if (! $bridge->isAvailable()) {
            $this->error('AuthCentral database is not configured or not accessible.');
            $this->line('Check database.php connection "authcentral" and AUTHCENTRAL_DB_* environment variables.');
            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $limit = $this->option('limit') ? (int) $this->option('limit') : null;
        $cohortId = $this->option('cohort') ? (int) $this->option('cohort') : null;

        if ($dryRun) {
            $this->warn('--- DRY RUN MODE (No changes will be written to AuthCentral) ---');
        }

        // Fetch all existing emails in AuthCentral
        $this->line('Fetching existing AuthCentral users...');
        $authCentralEmails = DB::connection('authcentral')
            ->table('users')
            ->pluck('email')
            ->map(fn ($e) => strtolower(trim($e)))
            ->flip()
            ->all();

        $this->info("Found " . count($authCentralEmails) . " existing user accounts in AuthCentral.");

        // Query students in College
        $query = Student::whereNotNull('email')
            ->where('email', '!=', '')
            ->orderBy('id');

        if ($cohortId) {
            $query->where('cohort_id', $cohortId);
        }

        if ($limit) {
            $query->limit($limit);
        }

        $students = $query->get();
        $totalStudents = $students->count();

        $this->info("Scanning {$totalStudents} College student records...");

        $missingStudents = [];
        foreach ($students as $student) {
            $email = strtolower(trim($student->email));
            if (! isset($authCentralEmails[$email])) {
                $missingStudents[] = $student;
            }
        }

        $missingCount = count($missingStudents);
        $alreadyInAuthCount = $totalStudents - $missingCount;

        $this->line('');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total College Students Scanned', $totalStudents],
                ['Already Exist in AuthCentral', $alreadyInAuthCount],
                ['Missing in AuthCentral', $missingCount],
            ]
        );

        if ($missingCount === 0) {
            $this->info('All scanned students already exist in AuthCentral! Nothing to do.');
            return self::SUCCESS;
        }

        if ($dryRun) {
            $this->info("Dry run complete. {$missingCount} students would be synced to AuthCentral.");
            return self::SUCCESS;
        }

        if (! $this->confirm("Do you want to provision these {$missingCount} missing students into AuthCentral now?", true)) {
            $this->warn('Operation cancelled by user.');
            return self::SUCCESS;
        }

        $this->line("Provisioning {$missingCount} students into AuthCentral with default temporary credentials...");
        $bar = $this->output->createProgressBar($missingCount);
        $bar->start();

        $synced = 0;
        $failed = 0;

        foreach ($missingStudents as $student) {
            // Generate readable initial credential: Pass#<last_4_id_digits_or_random>
            $cleanId = preg_replace('/[^0-9]/', '', (string) $student->student_id);
            $pin = strlen($cleanId) >= 4 ? substr($cleanId, -4) : (string) rand(1000, 9999);
            $initialPassword = "Pass#{$pin}";

            $result = $bridge->syncStudent($student, $initialPassword);

            if ($result['success']) {
                $synced++;
                // Ensure local college user has force_password_change set so they create their own password on first login
                if ($student->user_id) {
                    User::where('id', $student->user_id)->update(['force_password_change' => true]);
                }
            } else {
                $failed++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Synchronization Complete!");
        $this->table(
            ['Result', 'Count'],
            [
                ['Successfully Provisioned into AuthCentral', $synced],
                ['Failed', $failed],
            ]
        );

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
