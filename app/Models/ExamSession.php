<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
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
        'last_activity'  // Timestamp of last activity
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'extra_time_added_at' => 'datetime',
        'last_activity' => 'datetime',
        'score' => 'integer',
        'extra_time_minutes' => 'integer',
        'auto_submitted' => 'boolean'
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
        if (!$this->started_at) {
            return null;
        }
        
        // If the exam is already completed, return the actual completion time
        // This prevents extra time from being applied to completed exams
        if ($this->completed_at) {
            return $this->completed_at;
        }
        
        // First, get the original completion time based on the exam duration
        $originalEndTime = null;
        
        // Get the exam duration in minutes
        $durationMinutes = $this->exam->duration ?? 0;
        $originalEndTime = $this->started_at->copy()->addMinutes($durationMinutes);
        
        // Add any extra time minutes to the end time
        if ($this->extra_time_minutes > 0) {
            return $originalEndTime->copy()->addMinutes($this->extra_time_minutes);
        }
        
        return $originalEndTime;
    }

    /**
     * Get the remaining time for this exam session in seconds.
     *
     * @return int
     */
    public function getRemainingTimeAttribute()
    {
        // If the session is completed, return 0
        if (!$this->completed_at || Carbon::now()->gt($this->adjustedCompletionTime)) {
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
        return $this->started_at && !$this->completed_at;
    }

    /**
     * Check if this session is currently being accessed from a different device
     *
     * @param string $currentToken
     * @param string $currentDevice
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
     * @param string $token
     * @param string $deviceInfo
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
                'is_completed' => (bool)$this->completed_at
            ]);
        }
    }

    /**
     * Record device access attempt to help with monitoring and fraud detection
     *
     * @param string $token
     * @param string $deviceInfo
     * @param bool $isConflict
     * @return void
     */
    public function recordDeviceAccessAttempt($token, $deviceInfo, $isConflict = false)
    {
        // Create access log entry
        DeviceAccessLog::create([
            'exam_session_id' => $this->id,
            'student_id' => $this->student_id,
            'exam_id' => $this->exam_id,
            'device_info' => $deviceInfo,
            'session_token' => $token,
            'is_conflict' => $isConflict,
            'ip_address' => request()->ip(),
            'access_time' => now()
        ]);
    }

    /**
     * Check if the device access is considered suspicious
     * (multiple device changes in a short period)
     * 
     * @param int $threshold Number of device changes that trigger suspicion
     * @param int $timeWindow Time window in minutes to check for changes
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
}
