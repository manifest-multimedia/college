<?php

namespace App\Console\Commands;

use App\Models\ExamSession;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BackfillExamCompletionTimes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exams:backfill-completion-times
                            {--dry-run : Show what would be updated without making changes}
                            {--exam-id= : Only process sessions for a specific exam}
                            {--limit= : Limit the number of sessions to process}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill missing completed_at timestamps for exam sessions based on started_at, exam duration, and extra time';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $examId = $this->option('exam-id');
        $limit = $this->option('limit');

        $this->info('========================================');
        $this->info('Exam Completion Times Backfill Command');
        $this->info('========================================');
        $this->newLine();

        if ($dryRun) {
            $this->warn('🔍 DRY RUN MODE - No changes will be made');
            $this->newLine();
        }

        // Query for sessions missing completed_at but have started_at
        $query = ExamSession::with('exam')
            ->whereNull('completed_at')
            ->whereNotNull('started_at')
            ->where(function ($q) {
                // Include auto-submitted sessions OR sessions where adjusted completion time is in the past
                $q->where('auto_submitted', true)
                    ->orWhereRaw('DATE_ADD(started_at, INTERVAL (
                        SELECT COALESCE(duration, 0) 
                        FROM exams 
                        WHERE exams.id = exam_sessions.exam_id
                    ) + COALESCE(extra_time_minutes, 0) MINUTE) < NOW()');
            });

        if ($examId) {
            $query->where('exam_id', $examId);
            $this->info("📋 Filtering by exam ID: {$examId}");
        }

        if ($limit) {
            $query->limit($limit);
            $this->info("🔢 Processing limit: {$limit} sessions");
        }

        $sessions = $query->get();
        $totalSessions = $sessions->count();

        if ($totalSessions === 0) {
            $this->info('✅ No sessions found that need completion time backfill.');
            return Command::SUCCESS;
        }

        $this->info("📊 Found {$totalSessions} session(s) missing completed_at timestamps");
        $this->newLine();

        // Show summary table
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Sessions', $totalSessions],
                ['Auto-submitted', $sessions->where('auto_submitted', true)->count()],
                ['With Extra Time', $sessions->where('extra_time_minutes', '>', 0)->count()],
            ]
        );
        $this->newLine();

        if (!$dryRun && !$this->confirm('Do you want to proceed with updating these sessions?')) {
            $this->warn('❌ Operation cancelled by user');
            return Command::SUCCESS;
        }

        $this->newLine();
        $progressBar = $this->output->createProgressBar($totalSessions);
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');
        $progressBar->setMessage('Starting...');
        $progressBar->start();

        $updated = 0;
        $skipped = 0;
        $errors = 0;
        $updates = [];

        foreach ($sessions as $session) {
            try {
                $exam = $session->exam;
                
                if (!$exam || !$exam->duration) {
                    $progressBar->setMessage("⚠️  Session {$session->id}: No exam or duration found");
                    $progressBar->advance();
                    $skipped++;
                    continue;
                }

                // Calculate the expected completion time
                $startedAt = Carbon::parse($session->started_at);
                $examDuration = (int) $exam->duration; // in minutes
                $extraTime = (int) ($session->extra_time_minutes ?? 0);
                $totalDuration = $examDuration + $extraTime;

                $calculatedCompletedAt = $startedAt->copy()->addMinutes($totalDuration);

                // Prepare update data
                $updateData = [
                    'session_id' => $session->id,
                    'student_id' => optional($session->student)->student->student_id ?? 'N/A',
                    'exam_name' => optional($exam->course)->name ?? 'Unknown',
                    'started_at' => $startedAt->format('Y-m-d H:i:s'),
                    'calculated_completed_at' => $calculatedCompletedAt->format('Y-m-d H:i:s'),
                    'exam_duration' => $examDuration,
                    'extra_time' => $extraTime,
                    'total_duration' => $totalDuration,
                    'auto_submitted' => $session->auto_submitted ? 'Yes' : 'No',
                ];

                $updates[] = $updateData;

                if (!$dryRun) {
                    // Perform the update
                    DB::table('exam_sessions')
                        ->where('id', $session->id)
                        ->update([
                            'completed_at' => $calculatedCompletedAt,
                            'updated_at' => now(),
                        ]);

                    Log::info('Backfilled exam completion time', $updateData);
                }

                $updated++;
                $progressBar->setMessage("✓ Updated session {$session->id}");
                $progressBar->advance();

            } catch (\Exception $e) {
                $errors++;
                $progressBar->setMessage("✗ Error on session {$session->id}");
                $progressBar->advance();

                Log::error('Error backfilling completion time', [
                    'session_id' => $session->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        $progressBar->finish();
        $this->newLine(2);

        // Display detailed results
        $this->info('========================================');
        $this->info('📊 RESULTS SUMMARY');
        $this->info('========================================');
        $this->newLine();

        $this->table(
            ['Status', 'Count'],
            [
                ['✅ Successfully Updated', $updated],
                ['⚠️  Skipped', $skipped],
                ['❌ Errors', $errors],
                ['📝 Total Processed', $totalSessions],
            ]
        );

        if ($dryRun && $updated > 0) {
            $this->newLine();
            $this->warn('🔍 DRY RUN - The following updates would be made:');
            $this->newLine();
            
            // Show first 10 updates as examples
            $exampleUpdates = array_slice($updates, 0, 10);
            $this->table(
                ['Session ID', 'Student ID', 'Exam', 'Started At', 'Would Set Completed At', 'Duration (min)', 'Extra Time (min)'],
                array_map(function ($update) {
                    return [
                        $update['session_id'],
                        $update['student_id'],
                        substr($update['exam_name'], 0, 30),
                        $update['started_at'],
                        $update['calculated_completed_at'],
                        $update['exam_duration'],
                        $update['extra_time'],
                    ];
                }, $exampleUpdates)
            );

            if (count($updates) > 10) {
                $this->info("... and " . (count($updates) - 10) . " more");
            }
        }

        $this->newLine();

        if (!$dryRun) {
            $this->info('✅ Backfill completed successfully!');
            $this->info("💾 {$updated} session(s) updated with calculated completion times");
            
            if ($errors > 0) {
                $this->warn("⚠️  {$errors} error(s) encountered - check logs for details");
            }
        } else {
            $this->info('🔍 Dry run completed - no changes were made');
            $this->info('💡 Run without --dry-run to apply these changes');
        }

        $this->newLine();

        return Command::SUCCESS;
    }
}
