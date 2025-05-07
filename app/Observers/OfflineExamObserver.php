<?php

namespace App\Observers;

use App\Jobs\ProcessExamClearanceJob;
use App\Models\OfflineExam;
use Illuminate\Support\Facades\Log;

class OfflineExamObserver
{
    /**
     * Handle the OfflineExam "created" event.
     */
    public function created(OfflineExam $exam): void
    {
        // Only process clearances for published exams
        if ($exam->status === 'published') {
            $this->processClearance($exam);
        }
    }

    /**
     * Handle the OfflineExam "updated" event.
     */
    public function updated(OfflineExam $exam): void
    {
        // Check if the status changed to published
        if ($exam->isDirty('status') && $exam->status === 'published') {
            $this->processClearance($exam);
        }
        
        // Check if the clearance_threshold was changed
        if ($exam->isDirty('clearance_threshold') && $exam->status === 'published') {
            $this->processClearance($exam);
        }
        
        // Check if the venue was changed
        if ($exam->isDirty('venue') && $exam->status === 'published') {
            // Potentially notify students of venue change
            $this->notifyVenueChange($exam);
        }
    }

    /**
     * Dispatch job to process clearance for this exam
     */
    protected function processClearance(OfflineExam $exam): void
    {
        try {
            Log::info("Dispatching exam clearance job for OfflineExam #{$exam->id}");
            ProcessExamClearanceJob::dispatch($exam)
                ->onQueue('exam_clearances');
        } catch (\Exception $e) {
            Log::error("Error dispatching exam clearance job: " . $e->getMessage());
        }
    }
    
    /**
     * Notify students of venue change
     */
    protected function notifyVenueChange(OfflineExam $exam): void
    {
        // This would integrate with the Communication module
        // For now, just log the change
        Log::info("Venue changed for OfflineExam #{$exam->id}. New venue: {$exam->venue}");
        
        // Future implementation would notify cleared students:
        // Get all clearances for this exam
        // Filter to only those that are cleared
        // Dispatch notification jobs
    }
}