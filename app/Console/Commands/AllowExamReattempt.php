<?php

namespace App\Console\Commands;

use App\Models\ExamSession;
use App\Models\Response;
use App\Models\Student;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AllowExamReattempt extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exam:allow-reattempt 
                            {student_id : The student ID to allow reattempt}
                            {exam_id : The exam ID to allow reattempt for}
                            {--keep-responses : Keep existing responses instead of deleting them}
                            {--force : Skip confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Allow a student to reattempt an exam by resetting their exam session';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $studentId = $this->argument('student_id');
        $examId = $this->argument('exam_id');
        $keepResponses = $this->option('keep-responses');
        $force = $this->option('force');

        // Find the student
        $student = Student::where('student_id', $studentId)->first();

        if (! $student) {
            $this->error("❌ Student with ID '{$studentId}' not found.");

            return 1;
        }

        $this->info("Found student: {$student->name} ({$student->student_id})");

        // Find the exam session
        $session = ExamSession::with('exam')
            ->where('student_id', $student->user_id)
            ->where('exam_id', $examId)
            ->first();

        if (! $session) {
            $this->error('❌ No exam session found for this student and exam.');
            $this->info("Student: {$student->student_id}, Exam ID: {$examId}");

            return 1;
        }

        // Get response count
        $responseCount = Response::where('exam_session_id', $session->id)->count();

        $this->newLine();
        $this->info('📋 Exam Session Details:');
        $this->table(
            ['Field', 'Value'],
            [
                ['Session ID', $session->id],
                ['Exam', $session->exam->name ?? "Exam #{$examId}"],
                ['Started At', $session->started_at->format('Y-m-d H:i:s')],
                ['Completed At', $session->completed_at ? $session->completed_at->format('Y-m-d H:i:s') : 'Not completed'],
                ['Score', $session->score ?? 'Not scored'],
                ['Responses Stored', $responseCount],
                ['Auto Submitted', $session->auto_submitted ? 'Yes' : 'No'],
                ['Extra Time', $session->extra_time_minutes ? "{$session->extra_time_minutes} minutes" : 'None'],
            ]
        );

        $this->newLine();

        if (! $keepResponses) {
            $this->warn("⚠️  This will DELETE {$responseCount} stored response(s) and allow a fresh attempt.");
        } else {
            $this->info("ℹ️  Existing {$responseCount} response(s) will be kept (student can continue from where they left off).");
        }

        $this->newLine();

        if (! $force && ! $this->confirm('Allow this student to reattempt the exam?', true)) {
            $this->info('Operation cancelled.');

            return 0;
        }

        // Begin transaction
        DB::beginTransaction();

        try {
            // Delete responses if requested
            if (! $keepResponses && $responseCount > 0) {
                Response::where('exam_session_id', $session->id)->delete();
                $this->info("✅ Deleted {$responseCount} response(s).");
            }

            // Reset session fields
            $session->completed_at = null;
            $session->score = null;
            $session->auto_submitted = false;
            $session->session_token = null;
            $session->device_info = null;
            $session->last_activity = null;
            $session->save();

            Log::info('Exam reattempt allowed', [
                'session_id' => $session->id,
                'student_id' => $student->student_id,
                'exam_id' => $examId,
                'responses_deleted' => ! $keepResponses,
                'response_count' => $responseCount,
                'allowed_by' => 'artisan_command',
            ]);

            DB::commit();

            $this->info("✅ Successfully reset exam session #{$session->id}.");
            $this->info("Student '{$student->student_id}' can now reattempt the exam.");
            $this->newLine();
            $this->info('💡 Tips:');
            $this->info('   • Student should clear browser cache/cookies before retrying');
            $this->info('   • If device conflict occurs, run: php artisan exam:exempt-device-restriction '.$student->student_id);
            $this->info('   • Student will need to log in again with their exam password');

            return 0;
        } catch (\Exception $e) {
            DB::rollBack();

            $this->error("❌ Failed to reset exam session: {$e->getMessage()}");
            Log::error('Failed to allow exam reattempt', [
                'session_id' => $session->id,
                'student_id' => $student->student_id,
                'exam_id' => $examId,
                'error' => $e->getMessage(),
            ]);

            return 1;
        }
    }
}
