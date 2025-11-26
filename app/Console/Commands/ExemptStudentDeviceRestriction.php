<?php

namespace App\Console\Commands;

use App\Models\ExamSession;
use App\Models\Student;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ExemptStudentDeviceRestriction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exam:exempt-device-restriction 
                            {student_id : The student ID to exempt}
                            {--exam-id= : Optional exam ID to restrict exemption to specific exam}
                            {--all : Clear device restrictions for all exams}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Exempt a student from device restrictions, allowing them to log back in after timeout or device issues';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $studentId = $this->argument('student_id');
        $examId = $this->option('exam-id');
        $allExams = $this->option('all');

        // Find the student
        $student = Student::where('student_id', $studentId)->first();

        if (! $student) {
            $this->error("❌ Student with ID '{$studentId}' not found.");

            return 1;
        }

        $this->info("Found student: {$student->name} ({$student->student_id})");

        // Build query for exam sessions
        $query = ExamSession::where('student_id', $student->user_id);

        if ($examId && ! $allExams) {
            $query->where('exam_id', $examId);
        }

        $sessions = $query->get();

        if ($sessions->isEmpty()) {
            $this->warn('⚠️  No exam sessions found for this student.');

            if ($examId) {
                $this->info('Try running without --exam-id to see all sessions, or use --all to clear all restrictions.');
            }

            return 0;
        }

        $this->info("Found {$sessions->count()} exam session(s).");
        $this->newLine();

        // Show session details
        $this->table(
            ['Session ID', 'Exam ID', 'Started At', 'Status', 'Last Activity'],
            $sessions->map(function ($session) {
                return [
                    $session->id,
                    $session->exam_id,
                    $session->started_at->format('Y-m-d H:i:s'),
                    $session->completed_at ? 'Completed' : 'In Progress',
                    $session->last_activity ? $session->last_activity->diffForHumans() : 'N/A',
                ];
            })->toArray()
        );

        $this->newLine();

        if (! $this->confirm('Clear device restrictions for these sessions?', true)) {
            $this->info('Operation cancelled.');

            return 0;
        }

        // Clear device restrictions
        $cleared = 0;
        foreach ($sessions as $session) {
            // Clear the device tracking fields
            $session->session_token = null;
            $session->device_info = null;
            $session->last_activity = null;
            $session->save();

            Log::info('Device restriction cleared for exam session', [
                'session_id' => $session->id,
                'student_id' => $student->student_id,
                'exam_id' => $session->exam_id,
                'cleared_by' => 'artisan_command',
            ]);

            $cleared++;
        }

        $this->info("✅ Successfully cleared device restrictions for {$cleared} session(s).");
        $this->info("Student '{$student->student_id}' can now log in from any device.");
        $this->newLine();
        $this->info('💡 Tip: The student should clear their browser cache/cookies or use an incognito window for best results.');

        return 0;
    }
}
