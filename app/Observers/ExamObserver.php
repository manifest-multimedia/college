<?php

namespace App\Observers;

use App\Jobs\ProcessExamClearanceJob;
use App\Models\Exam;
use Illuminate\Support\Facades\Log;

class ExamObserver
{
    /**
     * Handle the Exam "creating" event (before saving).
     */
    public function creating(Exam $exam): void
    {
        // Set initial status based on dates if not already set
        if (empty($exam->status)) {
            $exam->status = $this->determineInitialStatus($exam);
        }
    }

    /**
     * Handle the Exam "created" event.
     */
    public function created(Exam $exam): void
    {
        // Only process clearances for published exams
        if ($exam->status === 'published') {
            $this->processClearance($exam);
        }
    }

    /**
     * Handle the Exam "updating" event (before saving changes).
     */
    public function updating(Exam $exam): void
    {
        // If start_date or end_date changed, recalculate status
        if ($exam->isDirty(['start_date', 'end_date'])) {
            $exam->status = $this->determineInitialStatus($exam);
        }
    }

    /**
     * Handle the Exam "updated" event.
     */
    public function updated(Exam $exam): void
    {
        // Check if the status changed to published
        if ($exam->isDirty('status') && $exam->status === 'published') {
            $this->processClearance($exam);
        }

        // Check if the clearance_threshold was changed
        if ($exam->isDirty('clearance_threshold') && $exam->status === 'published') {
            $this->processClearance($exam);
        }
    }

    /**
     * Determine initial status for an exam based on dates.
     * Note: We can't check hasActiveSession() here because the exam might not have 
     * sessions yet during creation, so we default to 'upcoming' and let the 
     * scheduled command handle the transition to 'active' once sessions exist.
     */
    protected function determineInitialStatus(Exam $exam): string
    {
        $now = now();

        // If end_date has passed, it's completed
        if ($exam->end_date && $now->greaterThanOrEqualTo($exam->end_date)) {
            return 'completed';
        }

        // Default to upcoming (will transition to active via scheduled command when sessions exist)
        return 'upcoming';
    }

    /**
     * Dispatch job to process clearance for this exam
     */
    protected function processClearance(Exam $exam): void
    {
        try {
            Log::info("Dispatching exam clearance job for Exam #{$exam->id}");
            ProcessExamClearanceJob::dispatch($exam)
                ->onQueue('exam_clearances');
        } catch (\Exception $e) {
            Log::error('Error dispatching exam clearance job: '.$e->getMessage());
        }
    }
}
