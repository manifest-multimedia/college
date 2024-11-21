<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = ['exam_id', 'question_text', 'exam_section', 'correct_option', 'mark', 'explanation'];

    /**
     * Exam to which this question belongs.
     */
    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    /**
     * Options associated with this question.
     */
    public function options()
    {
        return $this->hasMany(Option::class);
    }
}
