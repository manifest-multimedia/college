<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'course_id',
        'academic_year_id',
        'semester_id',
        'status',
        'date',
        'time',
        'duration',
        'password',
        'user_id',
        'questions_per_session',
        'slug',
        'exam_type',
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
     * Questions associated with this exam.
     */
    public function questions()
    {
        return $this->hasMany(Question::class);
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
}
