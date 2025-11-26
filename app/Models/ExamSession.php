<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExamSession extends Model
{
    protected $fillable = [
        'exam_id',
        'student_id',
        'started_at',
        'completed_at',
        'score',
        'auto_submitted',
        'extra_time_minutes',
        'extra_time_added_by',
        'extra_time_added_at',
        'session_token', // Unique token for this access session
        'device_info',   // Stores device identification
        'last_activity',  // Timestamp of last activity
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'extra_time_added_at' => 'datetime',
        'last_activity' => 'datetime',
        'score' => 'integer',
        'extra_time_minutes' => 'integer',
        'auto_submitted' => 'boolean',
    ];

    /**
     * The exam to which this session belongs.
     */
    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    /**
     * The student who is taking this exam session.
     */
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Responses recorded in this session.
     */
    public function responses()
    {
        return $this->hasMany(Response::class);
    }

    /**
     * Questions assigned to this exam session (new feature for dynamic question selection).
     */
    public function sessionQuestions()
    {
        return $this->hasMany(ExamSessionQuestion::class)->orderBy('display_order');
    }

    /**
     * Get the questions for this session (supports both legacy and new question set approach).
     */
    public function getQuestionsAttribute()
    {
        // Check if this session has specific question assignments
        if ($this->sessionQuestions()->exists()) {
            // Use dynamically assigned questions
            return $this->sessionQuestions()
                ->with('question.options')
                ->get()
                ->pluck('question');
        }

        // Fallback to exam's questions (backward compatibility)
        return $this->exam->questions()->with('options')->get();
    }

    /**
     * Scored questions for this session.
     */
    public function scoredQuestions()
    {
        return $this->hasMany(ScoredQuestion::class);
    }

    /**
     * The user who added extra time to this session.
     */
    public function extraTimeAddedBy()
    {
        return $this->belongsTo(User::class, 'extra_time_added_by');
    }

    /**
     * Get the adjusted completion time that includes any extra time granted.
     *
     * @return \Carbon\Carbon
     */
    public function getAdjustedCompletionTimeAttribute()
    {
        if (! $this->started_at) {
            return null;
        }

        // Calculate the proper end time based on exam duration and extra time
        // This is the source of truth for when the exam should end
        $durationMinutes = $this->exam->duration ?? 0;
        $extraTimeMinutes = $this->extra_time_minutes ?? 0;
        $totalDuration = $durationMinutes + $extraTimeMinutes;

        $calculatedEndTime = $this->started_at->copy()->addMinutes($totalDuration);

        // For completed sessions that are in the past, return the actual completion time
        // This preserves historical data for completed exams
        if ($this->completed_at && $this->completed_at->isPast() && $this->auto_submitted) {
            return $this->completed_at;
        }

        // For restored sessions where completed_at is set to a future time,
        // validate that it's reasonable (within 3 hours of calculated end time)
        // This prevents showing incorrect times due to data corruption or bugs
        if ($this->completed_at && $this->completed_at->isFuture() && ! $this->auto_submitted) {
            // Calculate the difference between the stored completed_at and calculated end time
            $diffInMinutes = abs($calculatedEndTime->diffInMinutes($this->completed_at));

            // If the difference is reasonable (less than 3 hours), use completed_at
            // This allows for legitimate restoration scenarios
            if ($diffInMinutes <= 180) {
                return $this->completed_at;
            }

            // Otherwise, the completed_at is likely corrupted - use calculated time
            Log::warning('ExamSession has invalid completed_at date, using calculated time', [
                'session_id' => $this->id,
                'stored_completed_at' => $this->completed_at->toDateTimeString(),
                'calculated_end_time' => $calculatedEndTime->toDateTimeString(),
                'diff_in_minutes' => $diffInMinutes,
            ]);
        }

        // Return the calculated end time based on duration and extra time
        return $calculatedEndTime;
    }

    /**
     * Get the remaining time for this exam session in seconds.
     *
     * @return int
     */
    public function getRemainingTimeAttribute()
    {
        // If the session is completed, return 0
        if (! $this->completed_at || Carbon::now()->gt($this->adjustedCompletionTime)) {
            return 0;
        }

        return Carbon::now()->diffInSeconds($this->adjustedCompletionTime);
    }

    /**
     * Check if the session has extra time added.
     *
     * @return bool
     */
    public function getHasExtraTimeAttribute()
    {
        return $this->extra_time_minutes > 0;
    }

    /**
     * Check if the session is currently active.
     *
     * @return bool
     */
    public function getIsActiveAttribute()
    {
        return $this->started_at && ! $this->completed_at;
    }

    /**
     * Check if this session is currently being accessed from a different device
     *
     * @param  string  $currentToken
     * @param  string  $currentDevice
     * @return bool
     */
    public function isBeingAccessedFromDifferentDevice($currentToken, $currentDevice)
    {
        // If session_token exists but doesn't match current token, and last activity is recent (within 2 minutes)
        return
            $this->session_token &&
            $this->session_token !== $currentToken &&
            $this->device_info !== $currentDevice &&
            $this->last_activity &&
            $this->last_activity->gt(now()->subMinutes(2));
    }

    /**
     * Update the device access information for this session
     *
     * @param  string  $token
     * @param  string  $deviceInfo
     * @return void
     */
    public function updateDeviceAccess($token, $deviceInfo)
    {
        // Check if device info has changed - if so, record it as a new access attempt
        $deviceChanged = $this->device_info !== $deviceInfo;

        // Update the current device information
        $this->session_token = $token;
        $this->device_info = $deviceInfo;
        $this->last_activity = now();
        $this->save();

        // Log the device change as an access attempt
        if ($deviceChanged) {
            $this->recordDeviceAccessAttempt($token, $deviceInfo, true);

            // Log the device change for monitoring
            Log::info('Device change detected for exam session', [
                'exam_session_id' => $this->id,
                'student_id' => $this->student_id,
                'previous_device' => $this->getOriginal('device_info'),
                'new_device' => $deviceInfo,
                'is_completed' => (bool) $this->completed_at,
            ]);
        }
    }

    /**
     * Record device access attempt to help with monitoring and fraud detection
     *
     * @param  string  $token
     * @param  string  $deviceInfo
     * @param  bool  $isConflict
     * @return void
     */
    public function recordDeviceAccessAttempt($token, $deviceInfo, $isConflict = false)
    {
        try {
            // Get the student record to retrieve the user_id
            $student = Student::find($this->student_id);

            // Only create log if student exists (to avoid foreign key constraint violation)
            if (! $student) {
                Log::warning('Attempted to record device access for non-existent student', [
                    'exam_session_id' => $this->id,
                    'student_id' => $this->student_id,
                    'exam_id' => $this->exam_id,
                ]);

                return;
            }

            $studentUserId = $student->user_id;

            // Create access log entry
            DeviceAccessLog::create([
                'exam_session_id' => $this->id,
                'student_id' => $this->student_id,
                'student_user_id' => $studentUserId,
                'exam_id' => $this->exam_id,
                'device_info' => $deviceInfo,
                'session_token' => $token,
                'is_conflict' => $isConflict,
                'ip_address' => request()->ip(),
                'access_time' => now(),
            ]);
        } catch (\Exception $e) {
            // Log the error but don't break the application
            Log::error('Failed to record device access attempt', [
                'exam_session_id' => $this->id,
                'student_id' => $this->student_id,
                'exam_id' => $this->exam_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            // Silently fail - device logging is not critical to exam functionality
        }
    }

    /**
     * Check if the device access is considered suspicious
     * (multiple device changes in a short period)
     *
     * @param  int  $threshold  Number of device changes that trigger suspicion
     * @param  int  $timeWindow  Time window in minutes to check for changes
     * @return bool
     */
    public function hasSuspiciousDeviceActivity($threshold = 3, $timeWindow = 30)
    {
        // Count how many unique devices have accessed this session recently
        $uniqueDeviceCount = DeviceAccessLog::where('exam_session_id', $this->id)
            ->where('access_time', '>', now()->subMinutes($timeWindow))
            ->distinct('device_info')
            ->count('device_info');

        return $uniqueDeviceCount >= $threshold;
    }

    /**
     * Get the formatted remaining time as a string (HH:MM:SS).
     *
     * @return string
     */
    public function getFormattedRemainingTimeAttribute()
    {
        $seconds = $this->remainingTimeAttribute;
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
    }

    /**
     * Check if this session has incomplete question assignments
     * compared to the exam's configured questions_per_session
     *
     * @return bool
     */
    public function hasIncompleteQuestionAssignment()
    {
        // If questions_per_session is not set, there's no limit to enforce
        $questionsPerSession = $this->exam->questions_per_session;
        if (! $questionsPerSession || $questionsPerSession <= 0) {
            return false;
        }

        // Count existing session questions
        $currentCount = $this->sessionQuestions()->count();

        // Session is incomplete if it has fewer questions than configured
        return $currentCount < $questionsPerSession;
    }

    /**
     * Regenerate questions for this session when incomplete
     * Preserves questions that have been answered
     *
     * @return int Number of questions added/regenerated
     */
    public function regenerateIncompleteQuestions()
    {
        return DB::transaction(function () {
            try {
                // Get questions_per_session from exam
                $questionsPerSession = $this->exam->questions_per_session;
                if (! $questionsPerSession || $questionsPerSession <= 0) {
                    Log::warning('Cannot regenerate questions: questions_per_session not configured', [
                        'session_id' => $this->id,
                        'exam_id' => $this->exam_id,
                    ]);

                    return 0;
                }

                // Get answered question IDs to preserve them
                $answeredQuestionIds = $this->responses()->pluck('question_id')->toArray();

                // Get currently assigned questions
                $currentQuestions = ExamSessionQuestion::where('exam_session_id', $this->id)
                    ->pluck('question_id')
                    ->toArray();

                $currentCount = count($currentQuestions);
                $deficit = $questionsPerSession - $currentCount;

                if ($deficit <= 0) {
                    Log::info('No deficit to regenerate', [
                        'session_id' => $this->id,
                        'current_count' => $currentCount,
                        'questions_per_session' => $questionsPerSession,
                    ]);

                    return 0;
                }

                Log::info('Regenerating incomplete session questions', [
                    'session_id' => $this->id,
                    'student_id' => $this->student_id,
                    'exam_id' => $this->exam_id,
                    'current_count' => $currentCount,
                    'deficit' => $deficit,
                    'answered_count' => count($answeredQuestionIds),
                ]);

                // Generate new pool excluding already assigned questions
                $allAvailableQuestions = $this->exam->generateSessionQuestions(true);
                $newQuestions = $allAvailableQuestions->whereNotIn('id', $currentQuestions)->take($deficit);

                if ($newQuestions->isEmpty()) {
                    Log::warning('No additional questions available for regeneration', [
                        'session_id' => $this->id,
                        'total_available' => $allAvailableQuestions->count(),
                        'already_assigned' => count($currentQuestions),
                    ]);

                    return 0;
                }

                // Get next display_order
                $maxOrder = ExamSessionQuestion::where('exam_session_id', $this->id)
                    ->max('display_order') ?? 0;

                // Insert new questions with strict limit enforcement
                $addedCount = 0;
                foreach ($newQuestions as $question) {
                    // Double-check we haven't exceeded the limit (defense in depth)
                    if ($currentCount + $addedCount >= $questionsPerSession) {
                        Log::warning('Stopping regeneration - would exceed target count', [
                            'session_id' => $this->id,
                            'current_plus_added' => $currentCount + $addedCount,
                            'target_count' => $questionsPerSession,
                        ]);
                        break;
                    }

                    ExamSessionQuestion::create([
                        'exam_session_id' => $this->id,
                        'question_id' => $question->id,
                        'display_order' => ++$maxOrder,
                    ]);
                    $addedCount++;
                }

                $newTotal = $currentCount + $addedCount;

                Log::info('Successfully regenerated session questions', [
                    'session_id' => $this->id,
                    'questions_added' => $addedCount,
                    'new_total' => $newTotal,
                    'expected_total' => $questionsPerSession,
                ]);

                return $addedCount;
            } catch (\Exception $e) {
                Log::error('Failed to regenerate incomplete session questions', [
                    'session_id' => $this->id,
                    'exam_id' => $this->exam_id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                throw $e;
            }
        });
    }
}
