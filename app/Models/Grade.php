<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Grade extends Model
{
    protected $fillable = [
        'student_id',
        'class_id',
        'assignment_id',
        'grade_value',
        'grade_type',
        'max_grade',
        'comments',
        'recorded_by',
        'grade_date',
    ];

    protected $casts = [
        'grade_date' => 'datetime',
        'grade_value' => 'float',
        'max_grade' => 'float',
    ];

    /**
     * Get the student who received this grade
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get the class this grade belongs to
     */
    public function class(): BelongsTo
    {
        return $this->belongsTo(CollegeClass::class, 'class_id');
    }

    /**
     * Get the assignment this grade is for (if any)
     */
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    /**
     * Get the user who recorded this grade
     */
    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * Calculate grade percentage
     */
    public function getPercentageAttribute()
    {
        if ($this->max_grade > 0) {
            return ($this->grade_value / $this->max_grade) * 100;
        }
        return null;
    }

    /**
     * Get letter grade based on percentage
     */
    public function getLetterGradeAttribute()
    {
        $percentage = $this->percentage;
        
        if ($percentage >= 90) {
            return 'A';
        } elseif ($percentage >= 80) {
            return 'B';
        } elseif ($percentage >= 70) {
            return 'C';
        } elseif ($percentage >= 60) {
            return 'D';
        } else {
            return 'F';
        }
    }

    /**
     * Scope for final grades
     */
    public function scopeFinal($query)
    {
        return $query->where('grade_type', 'final');
    }

    /**
     * Scope for midterm grades
     */
    public function scopeMidterm($query)
    {
        return $query->where('grade_type', 'midterm');
    }

    /**
     * Scope for assignment grades
     */
    public function scopeAssignment($query)
    {
        return $query->where('grade_type', 'assignment');
    }
}
