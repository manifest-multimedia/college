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
                    
                    // Get the exam duration
                    $exam = $session->exam;
                    if ($exam) {
                        // Calculate completion time based on when the student started + duration
                        $durationMinutes = $exam->duration;
                        $completedAt = Carbon::parse($startedAt)->addMinutes($durationMinutes);
                        
                        // Add any extra time that has been granted
                        if ($session->extra_time_minutes > 0) {
                            $completedAt = $completedAt->addMinutes($session->extra_time_minutes);
                        }
                        
                        Log::info('ExamTimer using student session times', [
                            'session_id' => $session->id, 
                            'start_time' => $startedAt,
                            'duration' => $durationMinutes,
                            'extra_time' => $session->extra_time_minutes,
                            'end_time' => $completedAt
                        ]);
                    }
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
                
                if ($session && $session->exam) {
                    $exam = $session->exam;
                    // Use the actual time when the student started the exam
                    $startTime = Carbon::parse($session->started_at);
                    // Calculate end time with duration and extra time
                    $endTime = $startTime->copy()->addMinutes($exam->duration + $session->extra_time_minutes);
                    
                    Log::info('Timer updated from server', [
                        'session_id' => $session->id,
                        'start_time' => $startTime->toDateTimeString(),
                        'end_time' => $endTime->toDateTimeString(),
                        'extra_time' => $session->extra_time_minutes
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
                    // Calculate base end time from start time + duration
                    $startTime = Carbon::parse($session->started_at);
                    $baseEndTime = $startTime->copy()->addMinutes($session->exam->duration);
                    
                    // Check if extra time has been added
                    if ($session->extra_time_minutes > 0) {
                        // Calculate new end time with extra time
                        $newEndTime = $baseEndTime->copy()->addMinutes($session->extra_time_minutes);
                        
                        Log::info('Extra time detected', [
                            'session_id' => $session->id,
                            'extra_minutes' => $session->extra_time_minutes,
                            'base_end_time' => $baseEndTime->toDateTimeString(),
                            'new_end_time' => $newEndTime->toDateTimeString()
                        ]);
                        
                        return [
                            'hasExtraTime' => true,
                            'extraMinutes' => $session->extra_time_minutes,
                            'newEndTime' => $newEndTime->toIso8601String()
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
