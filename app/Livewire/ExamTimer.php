<?php

namespace App\Livewire;

use Carbon\Carbon;
use Livewire\Component;
use App\Models\ExamSession;
use App\Models\Exam;
use Illuminate\Support\Facades\Log;

class ExamTimer extends Component
{
    public $started_at;
    public $completed_at;
    public $exam_session_id;

    public function mount($startedAt = null, $completedAt = null, $examSessionId = null)
    {
        $this->exam_session_id = $examSessionId;
        
        try {
            // Log the incoming values for debugging
            Log::info('ExamTimer initialized', [
                'examSessionId' => $examSessionId,
                'startedAt' => $startedAt,
                'completedAt' => $completedAt
            ]);
            
            // If we have an exam session ID, use it to get the most accurate times
            if ($this->exam_session_id) {
                $session = ExamSession::with('exam')->find($this->exam_session_id);
                
                if ($session) {
                    // Use the actual time when the student started the exam
                    $startedAt = $session->started_at;
                    
                    // Use the adjustedCompletionTime property which includes extra time
                    $completedAt = $session->adjustedCompletionTime;
                    
                    Log::info('ExamTimer using session adjustedCompletionTime', [
                        'session_id' => $session->id, 
                        'start_time' => $startedAt,
                        'extra_time' => $session->extra_time_minutes,
                        'adjustedCompletionTime' => $completedAt,
                        'has_extra_time' => $session->extra_time_minutes > 0 ? 'Yes' : 'No'
                    ]);
                }
            }
            
            // Ensure dates are converted to ISO-8601 format
            $this->started_at = Carbon::parse($startedAt)->toIso8601String();
            $this->completed_at = Carbon::parse($completedAt)->toIso8601String();
        } catch (\Exception $e) {
            Log::error('Error initializing ExamTimer', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Default values if there's an error
            $this->started_at = Carbon::now()->toIso8601String();
            $this->completed_at = Carbon::now()->addMinutes(60)->toIso8601String();
        }
    }
    
    /**
     * Get the most up-to-date completion time, including any extra time that was added
     * This method will be called from JavaScript to sync the client-side timer
     */
    public function getRemainingTime()
    {
        try {
            // If we have an exam session ID, fetch the latest data from the database
            if ($this->exam_session_id) {
                $session = ExamSession::with('exam')->find($this->exam_session_id);
                
                if ($session) {
                    // Use the adjustedCompletionTime accessor which handles all time calculations
                    $endTime = $session->adjustedCompletionTime;
                    
                    // Update the completed_at property to reflect latest end time
                    $this->completed_at = $endTime->toIso8601String();
                    
                    Log::info('Timer updated from server', [
                        'session_id' => $session->id,
                        'start_time' => $session->started_at->toDateTimeString(),
                        'end_time' => $endTime->toDateTimeString(),
                        'extra_time' => $session->extra_time_minutes,
                        'extra_time_added_at' => $session->extra_time_added_at ? $session->extra_time_added_at->toDateTimeString() : 'N/A',
                        'time_remaining_seconds' => $endTime->diffInSeconds(now(), false)
                    ]);
                    
                    return $endTime->toIso8601String();
                }
            }
            
            // If no session or no changes, return the original completion time
            return $this->completed_at;
        } catch (\Exception $e) {
            Log::error('Error getting remaining time', [
                'error' => $e->getMessage()
            ]);
            
            return $this->completed_at;
        }
    }
    
    /**
     * Check if extra time has been added to the exam session
     * Returns information about extra time for client-side updates
     */
    public function checkForExtraTime()
    {
        try {
            // If we have an exam session ID, fetch the latest data from the database
            if ($this->exam_session_id) {
                $session = ExamSession::with('exam')->find($this->exam_session_id);
                
                if ($session && $session->exam) {
                    // Calculate base end time from start time + duration (without extra time)
                    $startTime = Carbon::parse($session->started_at);
                    $baseEndTime = $startTime->copy()->addMinutes($session->exam->duration);
                    
                    // Get the adjusted end time which includes extra time
                    $adjustedEndTime = $session->adjustedCompletionTime;
                    
                    // Check if extra time has been added
                    if ($session->extra_time_minutes > 0) {
                        // Calculate how recently the extra time was added (if available)
                        $recentlyAdded = false;
                        $addedAgo = null;
                        
                        if ($session->extra_time_added_at) {
                            $recentlyAdded = $session->extra_time_added_at->diffInMinutes(now()) < 5; // Added in last 5 minutes
                            $addedAgo = $session->extra_time_added_at->diffForHumans();
                        }
                        
                        Log::info('Extra time detected', [
                            'session_id' => $session->id,
                            'extra_minutes' => $session->extra_time_minutes,
                            'base_end_time' => $baseEndTime->toDateTimeString(),
                            'adjusted_end_time' => $adjustedEndTime->toDateTimeString(),
                            'recently_added' => $recentlyAdded,
                            'added_ago' => $addedAgo
                        ]);
                        
                        return [
                            'hasExtraTime' => true,
                            'extraMinutes' => $session->extra_time_minutes,
                            'newEndTime' => $adjustedEndTime->toIso8601String(),
                            'recentlyAdded' => $recentlyAdded,
                            'addedAgo' => $addedAgo
                        ];
                    }
                }
            }
            
            // No extra time or unable to find session
            return [
                'hasExtraTime' => false
            ];
        } catch (\Exception $e) {
            Log::error('Error checking for extra time', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'hasExtraTime' => false,
                'error' => 'Error checking for extra time'
            ];
        }
    }

    public function render()
    {
        return view('livewire.exam-timer');
    }
}
