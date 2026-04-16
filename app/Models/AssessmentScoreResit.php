<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentScoreResit extends Model
{
    use HasFactory;

    protected $fillable = [
        'assessment_score_id',
        'course_id',
        'student_id',
        'cohort_id',
        'semester_id',
        'academic_year_id',
        'attempt_number',
        'previous_exam_score',
        'resit_score',
        'updated_average_score',
        'remarks',
        'recorded_by',
    ];

    protected $casts = [
        'previous_exam_score' => 'decimal:2',
        'resit_score' => 'decimal:2',
        'updated_average_score' => 'decimal:2',
    ];

    public function assessmentScore(): BelongsTo
    {
        return $this->belongsTo(AssessmentScore::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'course_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
