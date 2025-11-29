<?php

namespace App\Console\Commands;

use App\Models\Exam;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdateExamStatuses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exams:update-statuses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically update exam statuses based on dates and active sessions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();
        $updatedCount = 0;

        // Get all exams that aren't already completed
        $exams = Exam::whereIn('status', ['upcoming', 'active'])->get();

        foreach ($exams as $exam) {
            $oldStatus = $exam->status;
            $newStatus = $this->determineStatus($exam, $now);

            if ($oldStatus !== $newStatus) {
                $exam->status = $newStatus;
                $exam->save();
                $updatedCount++;
                
                $this->info("Exam #{$exam->id}: {$oldStatus} → {$newStatus}");
            }
        }

        if ($updatedCount > 0) {
            $this->info("Updated {$updatedCount} exam(s).");
        } else {
            $this->info('No exam status updates required.');
        }

        return Command::SUCCESS;
    }

    /**
     * Determine the correct status for an exam based on dates and active sessions.
     *
     * @param \App\Models\Exam $exam
     * @param \Carbon\Carbon $now
     * @return string
     */
    private function determineStatus(Exam $exam, Carbon $now): string
    {
        // If exam end date has passed, it's completed (regardless of active sessions)
        if ($exam->end_date && $now->greaterThanOrEqualTo($exam->end_date)) {
            return 'completed';
        }

        // Check if exam is within the active time window
        $isWithinTimeWindow = $exam->start_date && 
                              $now->greaterThanOrEqualTo($exam->start_date) &&
                              (!$exam->end_date || $now->lessThan($exam->end_date));

        // An exam is only "active" if it's within the time window AND has at least one active session
        if ($isWithinTimeWindow && $exam->hasActiveSession()) {
            return 'active';
        }

        // Default to upcoming (even if start_date has passed but no one has started yet)
        return 'upcoming';
    }
}
