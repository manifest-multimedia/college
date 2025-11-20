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
        'approved_at',
        'approved_by',
        'rejected_at',
        'rejected_by',
        'rejection_reason',
    ];

    protected $casts = [
        'registered_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
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

    /**
     * Get the user who approved this registration
     */
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the user who rejected this registration
     */
    public function rejectedBy()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    /**
     * Scope for pending approvals
     */
    public function scopePending($query)
    {
        return $query->where('is_approved', false)->whereNull('rejected_at');
    }

    /**
     * Scope for approved registrations
     */
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    /**
     * Scope for rejected registrations
     */
    public function scopeRejected($query)
    {
        return $query->where('is_approved', false)->whereNotNull('rejected_at');
    }
}
