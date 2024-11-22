<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Response extends Model
{
    protected $fillable = ['exam_session_id', 'question_id', 'selected_option', 'is_correct'];

    /**
     * The exam session this response is part of.
     */
    public function examSession()
    {
        return $this->belongsTo(ExamSession::class);
    }

    /**
     * The question that this response is answering.
     */
    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}