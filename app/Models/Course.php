<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    protected $fillable = [
        'name',
        'description',
        'slug',
        'is_deleted',
        'created_by',
        'course_code',
    ];

    protected $casts = [
        'is_deleted' => 'boolean',
    ];

    /**
     * Validation rules for course creation/updating
     */
    public static $rules = [
        'name' => 'required|string|min:3|max:255',
        'course_code' => [
            'required',
            'string',
            'min:2',
            'max:10',
            'regex:/^[A-Z]{2,4}\d{3,4}$/', // Matches format like "CS101", "MAT201", "COMP3301"
            'unique:courses,course_code',
        ],
        'description' => 'nullable|string|max:1000',
    ];

    /**
     * Custom error messages for validation rules
     */
    public static $messages = [
        'course_code.regex' => 'Course code must be in the format of 2-4 capital letters followed by 3-4 numbers (e.g., CS101, MAT201, COMP3301)',
    ];

    /**
     * Get the users associated with the course.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withTimestamps();
    }

    /**
     * Get the exams for this course
     */
    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class);
    }

    /**
     * Get the registrations for this course
     */
    public function registrations(): HasMany
    {
        return $this->hasMany(CourseRegistration::class);
    }

    /**
     * Get the students registered for this course
     */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'course_registrations')
            ->withPivot(['academic_year_id', 'semester_id', 'is_approved', 'registered_at'])
            ->withTimestamps();
    }

    /**
     * Get all classes for this course
     */
    public function collegeClasses(): HasMany
    {
        return $this->hasMany(CollegeClass::class);
    }

    /**
     * Get active classes for this course
     */
    public function activeClasses()
    {
        return $this->collegeClasses()->where('status', 'active');
    }

    /**
     * Get the user who created this course
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if course has any active classes
     */
    public function hasActiveClasses()
    {
        return $this->collegeClasses()->where('status', 'active')->exists();
    }
}
