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
        $exams = Exam::where('status', '!=', 'completed')->get();

        foreach ($exams as $exam) {
            $oldStatus = $exam->status;
            $newStatus = $this->determineStatus($exam, $now);

            if ($oldStatus !== $newStatus) {
                $exam->status = $newStatus;
                $exam->save();
                $updatedCount++;
                
                $this->info("Exam #{$exam->id} ({$exam->title}): {$oldStatus} → {$newStatus}");

                // When an exam transitions to completed, auto-complete any lingering active student sessions
                if ($newStatus === 'completed') {
                    $closedSessions = $exam->sessions()
                        ->whereNull('completed_at')
                        ->update([
                            'completed_at' => $now,
                            'auto_submitted' => true,
                        ]);

                    if ($closedSessions > 0) {
                        $this->info("  └ Auto-closed {$closedSessions} active student session(s) for Exam #{$exam->id}");
                    }
                }
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

        // Check if exam is within the active time window (start_date reached, end_date not passed)
        $isWithinTimeWindow = $exam->start_date && 
                              $now->greaterThanOrEqualTo($exam->start_date) &&
                              (!$exam->end_date || $now->lessThan($exam->end_date));

        if ($isWithinTimeWindow) {
            return 'active';
        }

        return 'upcoming';
    }
}
