<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamClearance extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'academic_year_id',
        'semester_id',
        'exam_type_id',
        'is_cleared',
        'is_manual_override',
        'override_reason',
        'cleared_by',
        'cleared_at',
        'clearance_code',
    ];

    protected $casts = [
        'is_cleared' => 'boolean',
        'is_manual_override' => 'boolean',
        'cleared_at' => 'datetime',
    ];

    /**
     * Get the student associated with this exam clearance
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the academic year associated with this exam clearance
     */
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Get the semester associated with this exam clearance
     */
    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    /**
     * Get the exam type associated with this exam clearance
     */
    public function examType()
    {
        return $this->belongsTo(ExamType::class);
    }

    /**
     * Get the user who cleared this student
     */
    public function clearedBy()
    {
        return $this->belongsTo(User::class, 'cleared_by');
    }

    /**
     * Get the exam entry tickets associated with this exam clearance
     */
    public function examEntryTickets()
    {
        return $this->hasMany(ExamEntryTicket::class);
    }
}