<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamSession extends Model
{
    protected $fillable = ['exam_id', 'student_id', 'started_at', 'completed_at', 'score'];

    /**
     * Exam to which this session belongs.
     */
    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    /**
     * Responses recorded in this session.
     */
    public function responses()
    {
        return $this->hasMany(Response::class);
    }
}
