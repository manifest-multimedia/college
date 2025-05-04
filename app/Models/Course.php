<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Course extends Model
{
    // protected $table = 'courses';

    protected $fillable = [
        'name',
        'description',
        'slug',
        'is_deleted',
        'created_by',
        'course_code',
    ];

    /*************  ✨ Codeium Command ⭐  *************/
    /**
     * Get the users associated with the course.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    /******  41bd788f-d860-4739-8b4d-73cb92a36659  *******/
    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * Get the exams for this course
     */
    public function exams()
    {
        return $this->hasMany(Exam::class);
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
