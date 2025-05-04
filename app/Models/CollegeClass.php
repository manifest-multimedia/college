<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CollegeClass extends Model
{
    protected $fillable = [
        'name',
        'description',
        'course_id',
        'instructor_id',
        'semester_id',
        'room',
        'schedule',
        'status',
        'max_students',
    ];

    /**
     * Get the course that this class belongs to
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the instructor for this class
     */
    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    /**
     * Get the semester this class belongs to
     */
    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    /**
     * Get all students enrolled in this class
     */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'class_enrollments', 'class_id', 'student_id')
            ->withTimestamps()
            ->withPivot(['enrollment_status', 'grade', 'comments']);
    }

    /**
     * Get all grades for this class
     */
    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class, 'class_id');
    }

    /**
     * Scope for active classes
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for archived classes
     */
    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }
}
