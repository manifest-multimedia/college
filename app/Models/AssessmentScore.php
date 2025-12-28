<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentScore extends Model
{
    protected $fillable = [
        'course_id',
        'student_id',
        'academic_year_id',
        'semester_id',
        'assignment_1_score',
        'assignment_2_score',
        'assignment_3_score',
        'assignment_4_score',
        'assignment_5_score',
        'assignment_count',
        'mid_semester_score',
        'end_semester_score',
        'assignment_weight',
        'mid_semester_weight',
        'end_semester_weight',
        'recorded_by',
        'remarks',
    ];

    protected $appends = [
        'assignment_average',
        'assignment_weighted',
        'mid_semester_weighted',
        'end_semester_weighted',
        'total_score',
        'grade_letter',
        'grade_points',
        'is_passed',
    ];

    /**
     * Relationships
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'course_id');
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * Computed Properties (Accessors)
     */
    public function getAssignmentAverageAttribute(): ?float
    {
        $scores = array_filter([
            $this->assignment_1_score,
            $this->assignment_2_score,
            $this->assignment_3_score,
            $this->assignment_4_score,
            $this->assignment_5_score,
        ], fn ($score) => $score !== null);

        return count($scores) > 0 ? round(array_sum($scores) / count($scores), 2) : null;
    }

    public function getAssignmentWeightedAttribute(): float
    {
        return $this->assignment_average
            ? round(($this->assignment_average * $this->assignment_weight / 100), 2)
            : 0;
    }

    public function getMidSemesterWeightedAttribute(): float
    {
        return $this->mid_semester_score
            ? round(($this->mid_semester_score * $this->mid_semester_weight / 100), 2)
            : 0;
    }

    public function getEndSemesterWeightedAttribute(): float
    {
        return $this->end_semester_score
            ? round(($this->end_semester_score * $this->end_semester_weight / 100), 2)
            : 0;
    }

    public function getTotalScoreAttribute(): float
    {
        return round(
            $this->assignment_weighted +
            $this->mid_semester_weighted +
            $this->end_semester_weighted,
            2
        );
    }

    public function getGradeLetterAttribute(): string
    {
        $total = $this->total_score;

        if ($total >= 80) {
            return 'A';
        }
        if ($total >= 75) {
            return 'B+';
        }
        if ($total >= 70) {
            return 'B';
        }
        if ($total >= 65) {
            return 'C+';
        }
        if ($total >= 60) {
            return 'C';
        }
        if ($total >= 55) {
            return 'D+';
        }
        if ($total >= 50) {
            return 'D';
        }

        return 'E';
    }

    public function getGradePointsAttribute(): float
    {
        $gradeLetter = $this->grade_letter;

        return match ($gradeLetter) {
            'A' => 4.0,
            'B+' => 3.5,
            'B' => 3.0,
            'C+' => 2.5,
            'C' => 2.0,
            'D+' => 1.5,
            'D' => 1.0,
            default => 0.0,
        };
    }

    public function getIsPassedAttribute(): bool
    {
        return $this->total_score >= 50;
    }
}
