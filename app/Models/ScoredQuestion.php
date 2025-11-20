<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScoredQuestion extends Model
{
    protected $fillable = ['exam_session_id', 'question_id', 'response_id'];

    public function examSession()
    {
        return $this->belongsTo(ExamSession::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function response()
    {
        return $this->belongsTo(Response::class);
    }
}
