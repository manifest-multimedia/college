<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class ExamSession extends Model
{
    protected $fillable = [
        'exam_id', 
        'student_id', 
        'started_at', 
        'completed_at', 
        'score',
        'extra_time_minutes',
        'extra_time_added_by',
        'extra_time_added_at'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'extra_time_added_at' => 'datetime',
        'score' => 'integer',
        'extra_time_minutes' => 'integer'
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
        
        // First, get the original completion time based on the exam duration
        $originalEndTime = $this->completed_at;
        
        // If completed_at is null or equal to started_at (which is an error condition),
        // recalculate the completion time from the exam duration
        if (!$originalEndTime || $originalEndTime->equalTo($this->started_at)) {
            // Get the exam duration in minutes
            $durationMinutes = $this->exam->duration ?? 0;
            $originalEndTime = $this->started_at->copy()->addMinutes($durationMinutes);
        }
        
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
