<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    use HasFactory;

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'date' => 'datetime',
        'enable_proctoring' => 'boolean',
    ];

    protected $fillable = [
        'course_id',
        'user_id',
        'type',
        'type_id',
        'duration',
        'questions_per_session',
        'passing_percentage',
        'clearance_threshold',
        'password',
        'status',
        'slug',
        'start_date',
        'end_date',
    ];

    /**
     * Get the course that owns the exam
     */
    public function course()
    {
        return $this->belongsTo(Subject::class, 'course_id');
    }

    /**
     * Get the academic year associated with the exam
     */
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Get the semester associated with the exam
     */
    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    /**
     * Get the exam clearances for this exam
     */
    public function examClearances()
    {
        return $this->morphMany(ExamClearance::class, 'clearable');
    }

    /**
     * Questions associated directly with this exam (backward compatibility).
     */
    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    /**
     * Question sets associated with this exam (new feature).
     */
    public function questionSets()
    {
        return $this->belongsToMany(QuestionSet::class, 'exam_question_set')
            ->withPivot(['shuffle_questions', 'questions_to_pick'])
            ->withTimestamps();
    }

    /**
     * Get all questions for this exam (from both direct questions and question sets).
     */
    public function allQuestions()
    {
        // Get direct questions
        $directQuestions = $this->questions();
        
        // Get questions from question sets
        $questionSetIds = $this->questionSets()->pluck('question_sets.id');
        $setQuestions = Question::whereIn('question_set_id', $questionSetIds);
        
        // Union both queries
        return $directQuestions->union($setQuestions);
    }

    /**
     * Calculate total questions count for this exam (from both direct questions and question sets).
     * This respects the 'questions_to_pick' configuration in the pivot table.
     */
    public function getTotalQuestionsCountAttribute()
    {
        $totalQuestions = 0;
        
        // Add direct questions count (backward compatibility)
        $totalQuestions += $this->questions()->count();
        
        // Add questions from question sets
        foreach ($this->questionSets as $questionSet) {
            $questionsToPick = $questionSet->pivot->questions_to_pick;
            
            if ($questionsToPick && $questionsToPick > 0) {
                // Use the configured number of questions to pick
                $totalQuestions += $questionsToPick;
            } else {
                // If no specific number configured, use all questions from the set
                $totalQuestions += $questionSet->questions()->count();
            }
        }
        
        return $totalQuestions;
    }

    /**
     * Scope to add questions count from question sets
     */
    public function scopeWithQuestionsCount($query)
    {
        return $query->withCount(['questions', 'questionSets']);
    }

    /**
     * Get dynamic questions for an exam session based on question set configuration.
     */
    public function generateSessionQuestions($shuffle = true)
    {
        $sessionQuestions = collect();
        
        // First, get questions directly assigned to the exam (backward compatibility)
        $directQuestions = $this->questions()->with('options')->get();
        if ($directQuestions->isNotEmpty()) {
            $sessionQuestions = $sessionQuestions->merge($directQuestions);
        }
        
        // Then, get questions from question sets
        foreach ($this->questionSets as $questionSet) {
            $setQuestions = $questionSet->questions()->with('options')->get();
            
            // Check if we need to pick a specific number of questions
            $questionsToPick = $questionSet->pivot->questions_to_pick;
            
            if ($questionsToPick && $questionsToPick < $setQuestions->count()) {
                // Pick random questions from this set
                $setQuestions = $setQuestions->random($questionsToPick);
            }
            
            // Shuffle if configured
            if ($questionSet->pivot->shuffle_questions) {
                $setQuestions = $setQuestions->shuffle();
            }
            
            $sessionQuestions = $sessionQuestions->merge($setQuestions);
        }
        
        // Final shuffle if requested
        if ($shuffle) {
            $sessionQuestions = $sessionQuestions->shuffle();
        }
        
        return $sessionQuestions->values(); // Reset array keys
    }

    /**
     * Sessions for this exam.
     */
    public function sessions()
    {
        return $this->hasMany(ExamSession::class);
    }

    public function proctoringSessions()
    {
        return $this->hasMany(ProctoringSession::class);
    }
    // Exam User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the route key name for Laravel route model binding.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }
}
