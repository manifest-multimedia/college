<?php

namespace App\Console\Commands;

use App\Models\ExamSession;
use Illuminate\Console\Command;

class AllowDeviceMismatch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exam:allow-device-mismatch {exam_session_id} {--force : Skip confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Allow a student with device mismatch to continue with their exam';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $sessionId = $this->argument('exam_session_id');
        $force = $this->option('force');

        // Find the exam session
        $examSession = ExamSession::find($sessionId);

        if (!$examSession) {
            $this->error("Exam session with ID {$sessionId} not found.");
            return 1;
        }

        // Get student and exam details
        $student = $examSession->user->student;
        $exam = $examSession->exam;

        $this->info('=== Exam Session Details ===');
        $this->line("Student: {$student->first_name} {$student->last_name} ({$student->student_id})");
        $this->line("Exam: {$exam->title}");
        $this->line("Status: " . ($examSession->completed_at ? 'Completed' : 'Active'));
        $this->line("Started: {$examSession->started_at}");
        $this->newLine();

        // Check if there's actually a device mismatch
        if ($examSession->device_info) {
            $deviceInfo = json_decode($examSession->device_info, true);
            $this->info('Device Info: ' . json_encode($deviceInfo, JSON_PRETTY_PRINT));
            $this->newLine();
        }

        // Check if already bypassed
        if ($examSession->device_mismatch_bypassed) {
            $this->warn('Device mismatch bypass is already enabled for this session.');
            $this->line("Bypassed at: {$examSession->device_mismatch_bypassed_at}");
            $this->line("Bypassed by: {$examSession->device_mismatch_bypassed_by}");
            return 0;
        }

        // Confirm before proceeding
        if (!$force && !$this->confirm('Allow this student to continue despite device mismatch?')) {
            $this->warn('Operation cancelled.');
            return 0;
        }

        // Mark the session as device-verified
        $examSession->device_mismatch_bypassed = true;
        $examSession->device_mismatch_bypassed_at = now();
        $examSession->device_mismatch_bypassed_by = auth()->id() ?? 'console';
        $examSession->save();

        $this->info("✓ Device mismatch bypass enabled for exam session {$sessionId}");
        $this->line("Student can now continue with their exam.");

        return 0;
    }
}
