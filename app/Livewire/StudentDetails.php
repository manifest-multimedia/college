<?php

namespace App\Livewire;

use App\Models\Student;
use App\Models\ExamSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class StudentDetails extends Component
{
    public $studentId;

    public $student;

    public $loading = true;
    
    // Restoration modal properties
    public $showRestoreModal = false;
    public $restoringSessionId = null;
    public $restoreMinutes = 30;
    public $restoreReason = '';
    public $errorMessage = '';

    public function mount($studentId)
    {
        $this->studentId = $studentId;
        $this->loadStudent();
    }

    public function loadStudent()
    {
        try {
            $this->student = Student::with([
                'CollegeClass',
                'Cohort',
                'User.roles',
                'examSessions' => function ($query) {
                    $query->with([
                        'exam' => function ($q) {
                            $q->with('course', 'questionSets')
                                ->withCount('questions');
                        },
                        'responses.question.options',
                    ])->orderBy('created_at', 'desc');
                },
            ])
                ->find($this->studentId);

            if (! $this->student) {
                session()->flash('error', 'Student not found.');
            }

            $this->loading = false;
        } catch (\Exception $e) {
            Log::error('Error loading student: '.$e->getMessage());
            session()->flash('error', 'Failed to load student information.');
            $this->loading = false;
        }
    }

    public function getSessionScore($session)
    {
        $exam = $session->exam;
        if (! $exam) {
            return ['obtained' => 0, 'total' => 0, 'percentage' => 0];
        }

        // Logic from ExamResponseTracker
        // Use questions_per_session if available, otherwise fall back to total questions count
        $questionsPerSession = $exam->questions_per_session ?? $exam->questions_count;

        $responses = $session->responses;
        $processedResponses = collect();

        foreach ($responses as $response) {
            $question = $response->question;
            if (! $question) {
                continue;
            }

            $correctOption = $question->options->where('is_correct', true)->first();
            $isCorrect = ($correctOption && $response->selected_option == $correctOption->id);
            $questionMark = $question->mark ?? 1;

            $processedResponses->push([
                'is_correct' => $isCorrect,
                'mark_value' => $questionMark,
            ]);
        }

        // Only take the configured number of questions per session
        $limitedResponses = $processedResponses->take($questionsPerSession);

        $obtainedMarks = $limitedResponses->where('is_correct', true)->sum('mark_value');
        $totalMarks = $limitedResponses->sum('mark_value');

        $percentage = $totalMarks > 0 ? round(($obtainedMarks / $totalMarks) * 100, 2) : 0;

        return [
            'obtained' => $obtainedMarks,
            'total' => $totalMarks,
            'percentage' => $percentage,
        ];
    }

    public function getExamName($session)
    {
        $exam = $session->exam;
        if (! $exam) {
            return 'Unknown Exam';
        }

        return $exam->course ? $exam->course->name : 'No Course Name';
    }

    public function deleteExamSession($sessionId)
    {
        if (! auth()->user()->hasRole(['Super Admin', 'System User'])) {
            session()->flash('error', 'You do not have permission to delete exam sessions.');

            return;
        }

        try {
            $session = \App\Models\ExamSession::find($sessionId);
            if ($session) {
                // Delete related records manually to ensure successful deletion
                // in case cascade delete is not configured in the database
                $session->responses()->delete();
                $session->sessionQuestions()->delete();
                $session->scoredQuestions()->delete();

                // Delete device access logs
                \App\Models\DeviceAccessLog::where('exam_session_id', $session->id)->delete();

                $session->delete();

                session()->flash('success', 'Exam session deleted successfully.');
                $this->loadStudent(); // Reload to update the list
            } else {
                session()->flash('error', 'Exam session not found.');
            }
        } catch (\Exception $e) {
            Log::error('Error deleting exam session: '.$e->getMessage());
            session()->flash('error', 'Failed to delete exam session: '.$e->getMessage());
        }
    }

    /**
     * Show restore session modal
     */
    public function showRestoreModal($sessionId)
    {
        if (!auth()->user()->hasRole(['Super Admin', 'System User'])) {
            session()->flash('error', 'You do not have permission to restore exam sessions.');
            return;
        }
        
        $this->restoringSessionId = $sessionId;
        $this->restoreMinutes = 30;
        $this->restoreReason = '';
        $this->errorMessage = '';
        $this->showRestoreModal = true;
    }
    
    /**
     * Cancel restore session modal
     */
    public function cancelRestore()
    {
        $this->showRestoreModal = false;
        $this->restoringSessionId = null;
        $this->restoreMinutes = 30;
        $this->restoreReason = '';
        $this->errorMessage = '';
    }
    
    /**
     * Confirm and execute session restoration
     */
    public function confirmRestore()
    {
        $this->validate([
            'restoreMinutes' => 'required|integer|min:5|max:120',
            'restoreReason' => 'required|string|min:5',
        ]);
        
        try {
            $session = ExamSession::find($this->restoringSessionId);
            
            if (!$session) {
                $this->errorMessage = 'Session not found.';
                return;
            }
            
            // Check if session is already active
            $status = $this->getSessionStatus($session);
            if ($status === 'active') {
                $this->errorMessage = 'Session is already active and does not need to be restored.';
                return;
            }
            
            $now = now();
            
            // Calculate how much extra time is needed to make the session active
            // adjustedCompletionTime = started_at + exam_duration + extra_time_minutes
            $currentAdjustedEndTime = $session->adjustedCompletionTime;
            
            // Calculate minutes needed to bring the session to current time + desired additional minutes
            $minutesNeeded = 0;
            if ($currentAdjustedEndTime && $currentAdjustedEndTime->lt($now)) {
                // Session is in the past, calculate how many minutes to bring it to now
                $minutesNeeded = (int) abs($currentAdjustedEndTime->diffInMinutes($now));
            }
            
            // Total extra time = minutes needed to reach now + additional minutes requested
            $totalExtraMinutes = (int) ($minutesNeeded + $this->restoreMinutes);
            
            // Calculate what the new end time will be
            $newEndTime = $now->copy()->addMinutes((int) $this->restoreMinutes);
            
            // Restore the session with proper logic
            $updates = [
                'completed_at' => null, // Critical: Set to null to make session "active"
                'extra_time_minutes' => $session->extra_time_minutes + $totalExtraMinutes,
                'extra_time_added_by' => Auth::id(),
                'extra_time_added_at' => $now,
                'auto_submitted' => false, // Critical: Reset auto-submission flag
                'score' => null, // Critical: Reset score to allow re-submission
                'is_restored' => true, // Mark as restored session
                'restored_at' => $now, // Track when it was restored
            ];
            
            $session->update($updates);
            
            // Log the restoration
            Log::info('Exam session restored from student details', [
                'session_id' => $this->restoringSessionId,
                'student_id' => $session->student_id,
                'requested_additional_minutes' => $this->restoreMinutes,
                'minutes_needed_to_reach_now' => $minutesNeeded,
                'total_extra_minutes_added' => $totalExtraMinutes,
                'reason' => $this->restoreReason,
                'restored_by' => Auth::id(),
                'restored_at' => $now,
                'new_adjusted_completion_time' => $session->fresh()->adjustedCompletionTime->toDateTimeString(),
            ]);
            
            $actualExtraMessage = $totalExtraMinutes > $this->restoreMinutes ? " (including {$minutesNeeded} minutes to bring session to current time)" : "";
            session()->flash('success', "Session restored successfully. Student can now log in and continue the exam with {$this->restoreMinutes} additional minutes{$actualExtraMessage}. New end time: {$session->fresh()->adjustedCompletionTime->format('M d, Y g:i A')}.");
            
            // Close modal and reload student data
            $this->cancelRestore();
            $this->loadStudent();
            
        } catch (\Exception $e) {
            Log::error('Error restoring exam session from student details: ' . $e->getMessage(), [
                'session_id' => $this->restoringSessionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->errorMessage = 'Failed to restore session: ' . $e->getMessage();
        }
    }
    
    /**
     * Get session status (from ExamExtraTime component)
     */
    private function getSessionStatus($session)
    {
        $now = now();
        $adjustedEndTime = $session->adjustedCompletionTime;
        
        if ($session->completed_at && $session->completed_at <= $now) {
            return 'completed';
        }
        
        if ($adjustedEndTime && $adjustedEndTime <= $now) {
            return 'expired';
        }
        
        return 'active';
    }
    
    /**
     * Check if session can be restored
     */
    public function canRestoreSession($session)
    {
        $status = $this->getSessionStatus($session);
        return in_array($status, ['completed', 'expired']);
    }

    /**
     * Wrapper method to avoid Alpine.js conflicts
     */
    public function openRestoreModal($sessionId)
    {
        return $this->showRestoreModal($sessionId);
    }

    public function render()
    {
        return view('livewire.student-details');
    }
}
