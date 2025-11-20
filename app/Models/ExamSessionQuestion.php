<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamSessionQuestion extends Model
{
    protected $fillable = [
        'exam_session_id',
        'question_id',
        'display_order',
    ];

    protected $casts = [
        'display_order' => 'integer',
    ];

    /**
     * The exam session this question belongs to
     */
    public function examSession()
    {
        return $this->belongsTo(ExamSession::class);
    }

    /**
     * The question assigned to this session
     */
    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * Scope to order by display order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order');
    }

    /**
     * Scope to filter by exam session
     */
    public function scopeForSession($query, $sessionId)
    {
        return $query->where('exam_session_id', $sessionId);
    }
}
