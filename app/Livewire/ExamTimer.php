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
                $session = ExamSession::find($this->exam_session_id);
                if ($session) {
                    $exam = Exam::find($session->exam_id);
                    
                    if ($exam) {
                        // Use the scheduled exam time from the exam model
                        $startedAt = $exam->date;
                        $completedAt = Carbon::parse($startedAt)->addMinutes($exam->duration);
                        
                        // If extra time exists, add it
                        if ($session->extra_time_minutes > 0) {
                            $completedAt = $completedAt->addMinutes($session->extra_time_minutes);
                        }
                        
                        Log::info('ExamTimer using data from exam', [
                            'exam_id' => $exam->id,
                            'scheduled_date' => $exam->date,
                            'duration' => $exam->duration,
                            'extra_time' => $session->extra_time_minutes,
                            'start_time' => $startedAt,
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
            
            // Default to current time + 60 minutes if there's an error
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
                    // Use the scheduled exam date
                    $startTime = Carbon::parse($exam->date);
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

    public function render()
    {
        return view('livewire.exam-timer');
    }
}
