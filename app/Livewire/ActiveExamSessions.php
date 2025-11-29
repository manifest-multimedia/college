<?php

namespace App\Livewire;

use App\Models\Exam;
use Livewire\Component;
use Livewire\Attributes\On;

class ActiveExamSessions extends Component
{
    public Exam $exam;
    public $activeSessions = [];
    public $totalActiveCount = 0;
    public $expectedParticipants = 0;
    public $showExcessWarning = false;

    /**
     * Mount the component with the exam.
     */
    public function mount(Exam $exam, int $expectedParticipants = 0)
    {
        $this->exam = $exam;
        $this->expectedParticipants = $expectedParticipants;
        $this->loadActiveSessions();
    }

    /**
     * Load active sessions with student and device information.
     * A session is considered "active" if:
     * 1. It has started (started_at is not null)
     * 2. It hasn't been completed (completed_at is null)
     * 3. The time hasn't expired (current time < adjusted_completion_time)
     */
    public function loadActiveSessions()
    {
        // Get potentially active sessions (started but not completed)
        $sessions = $this->exam->sessions()
            ->whereNotNull('started_at')
            ->whereNull('completed_at')
            ->with([
                'student:id,name,email',
                'student.student:id,email,student_id,first_name,last_name,other_name',
                'deviceAccessLogs' => function ($query) {
                    $query->orderBy('created_at', 'desc')->limit(5);
                }
            ])
            ->orderBy('started_at', 'desc')
            ->get();

        // Filter to only truly active sessions (time hasn't expired)
        $this->activeSessions = $sessions
            ->filter(function ($session) {
                // Check if the session time has expired using adjusted_completion_time
                // This accounts for exam duration + extra time
                $adjustedEndTime = $session->adjusted_completion_time;
                
                // Session is only active if the end time hasn't been reached
                return $adjustedEndTime && now()->lessThan($adjustedEndTime);
            })
            ->map(function ($session) {
                // Get student info from User model
                $user = $session->student;
                $studentRecord = $user?->student;
                
                // Calculate remaining time in seconds
                $remainingSeconds = $session->remaining_time;
                $remainingMinutes = max(0, floor($remainingSeconds / 60));
                
                // Calculate elapsed time properly for restored sessions
                // For restored sessions, only count time from when they resumed (last_activity or restored_at)
                $elapsedMinutes = 0;
                if ($session->is_restored && $session->restored_at) {
                    // For restored sessions, elapsed time = time since restoration
                    $elapsedMinutes = round(now()->floatDiffInMinutes($session->restored_at), 1);
                } elseif ($session->started_at) {
                    // For normal sessions, elapsed time = time since start
                    // Use max(0, ...) to prevent negative values
                    $elapsedMinutes = round(max(0, now()->floatDiffInMinutes($session->started_at)), 1);
                }
                
                // Calculate the base exam duration (without extra time)
                $baseDuration = $session->exam->duration ?? 0;
                
                // Calculate original extra time (extra time granted before restoration)
                // For restored sessions, the extra_time_minutes includes both:
                // 1. Minutes to bring session to current time when restored
                // 2. Additional minutes granted for the student to complete
                $totalExtraTime = $session->extra_time_minutes ?? 0;
                
                // For restored sessions, try to separate the "catch-up" time from actual extra time
                // The actual extra time for display is what extends beyond the original duration
                $displayExtraTime = $totalExtraTime;
                if ($session->is_restored && $session->restored_at && $session->started_at) {
                    // Calculate how much time passed from start to restoration
                    $minutesFromStartToRestore = $session->started_at->diffInMinutes($session->restored_at);
                    // The "catch-up" time needed = (time from start to restore) - base duration
                    $catchUpTime = max(0, $minutesFromStartToRestore - $baseDuration);
                    // Display extra time = total extra time - catch up time
                    $displayExtraTime = max(0, $totalExtraTime - $catchUpTime);
                }
                
                return [
                    'id' => $session->id,
                    'student_name' => $studentRecord?->name ?? $user?->name ?? 'Unknown',
                    'student_id' => $studentRecord?->student_id ?? 'N/A',
                    'student_email' => $user?->email ?? '',
                    'started_at' => $session->started_at,
                    'expected_end_time' => $session->adjusted_completion_time,
                    'remaining_seconds' => $remainingSeconds,
                    'remaining_minutes' => $remainingMinutes,
                    'device_info' => $session->device_info ? json_decode($session->device_info, true) : null,
                    'last_activity' => $session->last_activity,
                    'extra_time' => $displayExtraTime,
                    'total_extra_time' => $totalExtraTime,
                    'is_restored' => $session->is_restored ?? false,
                    'restored_at' => $session->restored_at,
                    'has_device_conflict' => $session->hasSuspiciousDeviceActivity(),
                    'device_changes_count' => $session->deviceAccessLogs->where('is_conflict', true)->count(),
                    'session_duration_minutes' => $elapsedMinutes,
                ];
            })
            ->values()
            ->toArray();

        $this->totalActiveCount = count($this->activeSessions);
        
        // Show warning if active sessions exceed expected participants
        $this->showExcessWarning = $this->expectedParticipants > 0 && 
                                   $this->totalActiveCount > $this->expectedParticipants;
    }

    /**
     * Refresh sessions data (called by polling or manual refresh).
     */
    #[On('refresh-sessions')]
    public function refresh()
    {
        $this->loadActiveSessions();
    }

    /**
     * Render the component.
     */
    public function render()
    {
        return view('livewire.active-exam-sessions');
    }
}
