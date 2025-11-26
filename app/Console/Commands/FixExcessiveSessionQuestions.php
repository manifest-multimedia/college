<?php

namespace App\Console\Commands;

use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\ExamSessionQuestion;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixExcessiveSessionQuestions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exam:fix-excessive-questions 
                            {exam_id? : The exam ID to fix (optional, will process all if not provided)}
                            {--dry-run : Show what would be fixed without making changes}
                            {--active-only : Only fix active (not completed) sessions}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix exam sessions that have more questions than the configured questions_per_session limit';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $examId = $this->argument('exam_id');
        $dryRun = $this->option('dry-run');
        $activeOnly = $this->option('active-only');

        $this->info('🔍 Scanning for exam sessions with excessive questions...');
        $this->newLine();

        // Build query for exams with questions_per_session configured
        $examsQuery = Exam::whereNotNull('questions_per_session')
            ->where('questions_per_session', '>', 0);

        if ($examId) {
            $examsQuery->where('id', $examId);
        }

        $exams = $examsQuery->get();

        if ($exams->isEmpty()) {
            $this->warn('No exams found with questions_per_session configured.');

            return 0;
        }

        $this->info("Found {$exams->count()} exam(s) to check.");
        $this->newLine();

        $totalFixed = 0;
        $totalAffectedSessions = 0;

        foreach ($exams as $exam) {
            $this->info("📝 Checking Exam: {$exam->name} (ID: {$exam->id})");
            $this->info("   Configured limit: {$exam->questions_per_session} questions per session");

            // Get sessions for this exam
            $sessionsQuery = ExamSession::where('exam_id', $exam->id);

            if ($activeOnly) {
                $sessionsQuery->whereNull('completed_at');
            }

            $sessions = $sessionsQuery->get();

            if ($sessions->isEmpty()) {
                $this->comment('   No sessions found for this exam.');
                $this->newLine();

                continue;
            }

            $this->info("   Found {$sessions->count()} session(s) to check.");

            $affectedSessions = [];

            foreach ($sessions as $session) {
                $currentQuestionCount = ExamSessionQuestion::where('exam_session_id', $session->id)->count();

                if ($currentQuestionCount > $exam->questions_per_session) {
                    $excess = $currentQuestionCount - $exam->questions_per_session;
                    $affectedSessions[] = [
                        'session_id' => $session->id,
                        'student_id' => $session->student_id,
                        'current_count' => $currentQuestionCount,
                        'expected_count' => $exam->questions_per_session,
                        'excess' => $excess,
                        'is_completed' => ! is_null($session->completed_at),
                    ];
                }
            }

            if (empty($affectedSessions)) {
                $this->info('   ✅ All sessions have correct question counts.');
                $this->newLine();

                continue;
            }

            $this->warn("   ⚠️  Found {count($affectedSessions)} session(s) with excessive questions:");
            $this->newLine();

            // Display table of affected sessions
            $this->table(
                ['Session ID', 'Student ID', 'Current', 'Expected', 'Excess', 'Status'],
                collect($affectedSessions)->map(function ($session) {
                    return [
                        $session['session_id'],
                        $session['student_id'],
                        $session['current_count'],
                        $session['expected_count'],
                        $session['excess'],
                        $session['is_completed'] ? 'Completed' : 'Active',
                    ];
                })->toArray()
            );

            $totalAffectedSessions += count($affectedSessions);

            if ($dryRun) {
                $this->comment('   [DRY RUN] Would remove excess questions from these sessions');
                $this->newLine();

                continue;
            }

            // Ask for confirmation
            if (! $this->confirm("Fix these {count($affectedSessions)} session(s)?", true)) {
                $this->comment('   Skipped.');
                $this->newLine();

                continue;
            }

            // Fix each session
            foreach ($affectedSessions as $sessionData) {
                try {
                    DB::beginTransaction();

                    $session = ExamSession::find($sessionData['session_id']);
                    $excessCount = $sessionData['excess'];

                    // Get responses for this session to preserve answered questions
                    $answeredQuestionIds = $session->responses()->pluck('question_id')->toArray();

                    // Get all session questions ordered by display_order
                    $sessionQuestions = ExamSessionQuestion::where('exam_session_id', $session->id)
                        ->orderBy('display_order')
                        ->get();

                    // Separate into answered and unanswered
                    $answeredQuestions = $sessionQuestions->whereIn('question_id', $answeredQuestionIds);
                    $unansweredQuestions = $sessionQuestions->whereNotIn('question_id', $answeredQuestionIds);

                    $questionsToRemove = collect();

                    // First, try to remove unanswered questions
                    if ($unansweredQuestions->count() >= $excessCount) {
                        // Remove the last unanswered questions
                        $questionsToRemove = $unansweredQuestions->sortByDesc('display_order')->take($excessCount);
                    } else {
                        // Remove all unanswered questions
                        $questionsToRemove = $unansweredQuestions;
                        $remaining = $excessCount - $unansweredQuestions->count();

                        // If still need to remove more, remove from the end (last answered questions)
                        if ($remaining > 0) {
                            $lastAnswered = $answeredQuestions->sortByDesc('display_order')->take($remaining);
                            $questionsToRemove = $questionsToRemove->merge($lastAnswered);
                        }
                    }

                    // Delete the excessive questions
                    $removedIds = $questionsToRemove->pluck('id')->toArray();
                    ExamSessionQuestion::whereIn('id', $removedIds)->delete();

                    // Recalculate display_order for remaining questions
                    $remainingQuestions = ExamSessionQuestion::where('exam_session_id', $session->id)
                        ->orderBy('display_order')
                        ->get();

                    $displayOrder = 1;
                    foreach ($remainingQuestions as $question) {
                        $question->update(['display_order' => $displayOrder++]);
                    }

                    Log::info('Fixed exam session with excessive questions', [
                        'session_id' => $session->id,
                        'student_id' => $session->student_id,
                        'exam_id' => $exam->id,
                        'previous_count' => $sessionData['current_count'],
                        'new_count' => $exam->questions_per_session,
                        'removed_count' => $questionsToRemove->count(),
                        'answered_removed' => $questionsToRemove->whereIn('question_id', $answeredQuestionIds)->count(),
                    ]);

                    DB::commit();

                    $this->info("   ✅ Fixed session {$session->id} - Removed {$questionsToRemove->count()} questions");
                    $totalFixed++;

                } catch (\Exception $e) {
                    DB::rollBack();

                    $this->error("   ❌ Failed to fix session {$sessionData['session_id']}: {$e->getMessage()}");
                    Log::error('Failed to fix session with excessive questions', [
                        'session_id' => $sessionData['session_id'],
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            }

            $this->newLine();
        }

        // Summary
        $this->newLine();
        $this->info('📊 Summary:');
        $this->info("   Total sessions checked: {$sessions->count()}");
        $this->info("   Sessions with issues found: {$totalAffectedSessions}");

        if ($dryRun) {
            $this->comment('   [DRY RUN] No changes made');
        } else {
            $this->info("   Sessions fixed: {$totalFixed}");
        }

        return 0;
    }
}
