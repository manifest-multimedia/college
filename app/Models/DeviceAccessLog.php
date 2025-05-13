<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceAccessLog extends Model
{
    protected $fillable = [
        'exam_session_id',
        'student_id',
        'exam_id',
        'device_info',
        'session_token',
        'ip_address',
        'is_conflict',
        'access_time'
    ];
    
    protected $casts = [
        'is_conflict' => 'boolean',
        'access_time' => 'datetime'
    ];
    
    /**
     * Get the exam session associated with this log
     */
    public function examSession()
    {
        return $this->belongsTo(ExamSession::class);
    }
    
    /**
     * Get the student associated with this log
     */
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
    
    /**
     * Get the exam associated with this log
     */
    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }
    
    /**
     * Scope for suspicious activity patterns
     */
    public function scopeSuspiciousActivity($query)
    {
        return $query->where('is_conflict', true);
    }
    
    /**
     * Scope for getting logs within a specific time window
     */
    public function scopeWithinTimeWindow($query, $minutes = 30)
    {
        return $query->where('access_time', '>', now()->subMinutes($minutes));
    }
}
