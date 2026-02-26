<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuestionSet extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'course_id',
        'difficulty_level',
        'created_by',
    ];

    protected $casts = [
        'difficulty_level' => 'string',
    ];

    /**
     * Subject (course) this question set belongs to
     */
    public function course()
    {
        return $this->belongsTo(Subject::class, 'course_id');
    }

    /**
     * User who created this question set
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Questions in this set
     */
    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    /**
     * Exams using this question set
     */
    public function exams()
    {
        return $this->belongsToMany(Exam::class, 'exam_question_set')
            ->withPivot(['shuffle_questions', 'questions_to_pick'])
            ->withTimestamps();
    }

    /**
     * Scope to filter by difficulty level
     */
    public function scopeDifficulty($query, $level)
    {
        return $query->where('difficulty_level', $level);
    }

    /**
     * Scope to filter by course
     */
    public function scopeByCourse($query, $courseId)
    {
        return $query->where('course_id', $courseId);
    }

    /**
     * Scope to filter by creator (for role-based access)
     */
    public function scopeByCreator($query, $userId)
    {
        return $query->where('created_by', $userId);
    }

    /**
     * Get the total number of questions in this set
     */
    public function getQuestionCountAttribute()
    {
        return $this->questions()->count();
    }

    /**
     * Check if this question set has any questions
     */
    public function hasQuestions()
    {
        return $this->questions()->exists();
    }
}
