<?php

namespace App\Console\Commands;

use App\Models\Exam;
use App\Models\ExamSession;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class FixIncompleteExamSessions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exam:fix-incomplete-sessions {--exam-id= : Specific exam ID to fix} {--dry-run : Show what would be fixed without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix exam sessions that have incomplete question assignments';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $examId = $this->option('exam-id');
        $dryRun = $this->option('dry-run');

        $this->info('Scanning for incomplete exam sessions...');
        $this->newLine();

        // Build base query for exam sessions
        $query = ExamSession::with(['exam', 'student'])
            ->whereNull('completed_at'); // Only check active sessions

        if ($examId) {
            $query->where('exam_id', $examId);
            $this->info("Filtering by exam ID: {$examId}");
        }

        $sessions = $query->get();
        $this->info("Found {$sessions->count()} active exam sessions to check.");
        $this->newLine();

        $incompleteCount = 0;
        $fixedCount = 0;
        $errorCount = 0;
        $questionsAdded = 0;

        $progressBar = $this->output->createProgressBar($sessions->count());
        $progressBar->start();

        foreach ($sessions as $session) {
            try {
                if ($session->hasIncompleteQuestionAssignment()) {
                    $incompleteCount++;

                    $currentCount = $session->sessionQuestions()->count();
                    $expected = $session->exam->questions_per_session;

                    if ($dryRun) {
                        $this->newLine();
                        $this->warn("  [DRY RUN] Would fix Session #{$session->id}:");
                        $this->line("    Exam: {$session->exam->course->course_code} - {$session->exam->exam_type}");
                        $this->line("    Student ID: {$session->student_id}");
                        $this->line("    Current questions: {$currentCount}");
                        $this->line("    Expected questions: {$expected}");
                        $this->line('    Deficit: '.($expected - $currentCount));
                    } else {
                        $added = $session->regenerateIncompleteQuestions();

                        if ($added > 0) {
                            $fixedCount++;
                            $questionsAdded += $added;

                            Log::info('Fixed incomplete exam session via command', [
                                'session_id' => $session->id,
                                'exam_id' => $session->exam_id,
                                'student_id' => $session->student_id,
                                'questions_added' => $added,
                            ]);
                        }
                    }
                }
            } catch (\Exception $e) {
                $errorCount++;
                $this->newLine();
                $this->error("  Error fixing Session #{$session->id}: {$e->getMessage()}");

                Log::error('Failed to fix incomplete session via command', [
                    'session_id' => $session->id,
                    'error' => $e->getMessage(),
                ]);
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Display summary
        $this->info('=== Summary ===');
        $this->line("Total sessions checked: {$sessions->count()}");
        $this->line("Incomplete sessions found: {$incompleteCount}");

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes were made');
            $this->line('Run without --dry-run to apply fixes');
        } else {
            $this->line("Sessions fixed: {$fixedCount}");
            $this->line("Total questions added: {$questionsAdded}");

            if ($errorCount > 0) {
                $this->error("Errors encountered: {$errorCount}");
            }
        }

        $this->newLine();

        if (! $dryRun && $fixedCount > 0) {
            $this->info('✅ Successfully fixed incomplete exam sessions!');
        }

        return 0;
    }
}
