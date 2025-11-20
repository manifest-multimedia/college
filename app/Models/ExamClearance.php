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
        'exam_type_id', // Maintained for backward compatibility
        'clearable_type', // New field for polymorphic relationship
        'clearable_id',   // New field for polymorphic relationship
        'is_cleared',
        'status',         // New field for more detailed status (Cleared, Pending, Denied)
        'is_manual_override',
        'override_reason',
        'comments',       // New field for additional comments
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
     * Get the parent clearable model (Exam or OfflineExam).
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function clearable()
    {
        return $this->morphTo();
    }

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
     *
     * @deprecated Use clearable relationship instead
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

    /**
     * Get the exam entry ticket associated with this exam clearance
     */
    public function examEntryTicket()
    {
        return $this->hasOne(ExamEntryTicket::class);
    }

    /**
     * Get the exam associated with this clearance (maps to clearable for OfflineExam)
     * This serves as a convenience method for the polymorphic relationship
     */
    public function exam()
    {
        if ($this->clearable_type === 'App\\Models\\OfflineExam') {
            return $this->belongsTo(OfflineExam::class, 'clearable_id');
        }

        return null;
    }

    /**
     * Determine if the clearance is for an online exam.
     *
     * @return bool
     */
    public function isOnlineExam()
    {
        return $this->clearable_type === 'App\\Models\\Exam';
    }

    /**
     * Determine if the clearance is for an offline exam.
     *
     * @return bool
     */
    public function isOfflineExam()
    {
        return $this->clearable_type === 'App\\Models\\OfflineExam';
    }

    /**
     * Scope a query to only include specific clearable type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('clearable_type', 'App\\Models\\'.$type);
    }
}
