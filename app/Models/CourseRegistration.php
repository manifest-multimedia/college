<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'subject_id',
        'academic_year_id',
        'semester_id',
        'registered_at',
        'payment_percentage_at_registration',
        'is_approved',
    ];

    protected $casts = [
        'registered_at' => 'datetime',
        'payment_percentage_at_registration' => 'decimal:2',
        'is_approved' => 'boolean',
    ];

    /**
     * Get the student associated with this course registration
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the subject associated with this course registration
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get the academic year associated with this course registration
     */
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Get the semester associated with this course registration
     */
    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }
}