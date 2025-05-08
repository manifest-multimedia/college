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
        if (!$this->completed_at) {
            return null;
        }
        
        // If no extra time was added, return the original completion time
        if ($this->extra_time_minutes <= 0) {
            return $this->completed_at;
        }
        
        // Add extra time to the original completion time
        return $this->completed_at->addMinutes($this->extra_time_minutes);
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
