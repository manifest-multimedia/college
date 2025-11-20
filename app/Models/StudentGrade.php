<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentGrade extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'college_class_id',
        'grade_id',
        'comments',
        'graded_by',
    ];

    /**
     * Get the student associated with this grade
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the college class for this grade
     */
    public function collegeClass()
    {
        return $this->belongsTo(CollegeClass::class);
    }

    /**
     * Get the grade type for this student grade
     */
    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }

    /**
     * Get the user who graded this student
     */
    public function gradedBy()
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    /**
     * Get the semester for this grade through the college class
     */
    public function semester()
    {
        return $this->collegeClass->semester;
    }

    /**
     * Get the academic year for this grade through the semester
     */
    public function academicYear()
    {
        return $this->semester()->academicYear;
    }
}
