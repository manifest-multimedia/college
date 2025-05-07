<?php

namespace App\Observers;

use App\Jobs\ProcessExamClearanceJob;
use App\Models\Exam;
use Illuminate\Support\Facades\Log;

class ExamObserver
{
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
     * Dispatch job to process clearance for this exam
     */
    protected function processClearance(Exam $exam): void
    {
        try {
            Log::info("Dispatching exam clearance job for Exam #{$exam->id}");
            ProcessExamClearanceJob::dispatch($exam)
                ->onQueue('exam_clearances');
        } catch (\Exception $e) {
            Log::error("Error dispatching exam clearance job: " . $e->getMessage());
        }
    }
}