<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamSession extends Model
{
    protected $fillable = ['exam_id', 'student_id', 'started_at', 'completed_at', 'score'];

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
        return $this->belongsTo(Student::class);
    }

    /**
     * Responses recorded in this session.
     */
    public function responses()
    {
        return $this->hasMany(Response::class);
    }
}
