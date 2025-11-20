<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = [
        'exam_id',
        'question_set_id',
        'question_text',
        'exam_section',
        'correct_option',
        'mark',
        'explanation',
        'type',
        'difficulty_level',
    ];

    protected $casts = [
        'mark' => 'integer',
        'type' => 'string',
        'difficulty_level' => 'string',
    ];

    /**
     * Exam to which this question belongs (backward compatibility).
     */
    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    /**
     * Question set to which this question belongs (new feature).
     */
    public function questionSet()
    {
        return $this->belongsTo(QuestionSet::class);
    }

    /**
     * Options associated with this question.
     */
    public function options()
    {
        return $this->hasMany(Option::class);
    }

    /**
     * Exam session assignments for this question.
     */
    public function sessionAssignments()
    {
        return $this->hasMany(ExamSessionQuestion::class);
    }

    /**
     * Get the course/subject this question belongs to
     * (either through exam or question set)
     */
    public function getCourseAttribute()
    {
        if ($this->questionSet) {
            return $this->questionSet->course;
        }

        if ($this->exam) {
            return $this->exam->course;
        }

        return null;
    }

    /**
     * Scope to filter by question type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to filter by difficulty level
     */
    public function scopeDifficulty($query, $level)
    {
        return $query->where('difficulty_level', $level);
    }

    /**
     * Scope to filter by exam (backward compatibility)
     */
    public function scopeForExam($query, $examId)
    {
        return $query->where('exam_id', $examId);
    }

    /**
     * Scope to filter by question set
     */
    public function scopeForQuestionSet($query, $questionSetId)
    {
        return $query->where('question_set_id', $questionSetId);
    }

    /**
     * Get the correct options for this question
     */
    public function getCorrectOptionsAttribute()
    {
        return $this->options()->where('is_correct', true)->get();
    }

    /**
     * Get the correct option for this question (legacy support)
     */
    public function getCorrectOptionAttribute()
    {
        return $this->correctOptions->first();
    }

    /**
     * Check if this question belongs to a question set or directly to an exam
     */
    public function belongsToQuestionSet()
    {
        return ! is_null($this->question_set_id);
    }

    /**
     * Check if this question belongs directly to an exam (legacy mode)
     */
    public function belongsToExam()
    {
        return ! is_null($this->exam_id) && is_null($this->question_set_id);
    }
}
