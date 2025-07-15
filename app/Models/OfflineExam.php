<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfflineExam extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'description',
        'date',
        'duration',
        'status',
        'course_id', // Foreign key to subjects table (renamed from 'course' for consistency)
        'user_id', // Created by
        'type_id', // Foreign key to exam_types table
        'proctor_id', // User who supervises the exam
        'venue',
        'clearance_threshold', // Percentage of fees required for clearance
        'passing_percentage'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'date' => 'datetime',
        'duration' => 'integer',
        'clearance_threshold' => 'integer',
        'passing_percentage' => 'integer',
    ];

    /**
     * Get the course (subject) associated with this offline exam.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function course()
    {
        return $this->belongsTo(Subject::class, 'course_id');
    }

    /**
     * Get the creator of this offline exam.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the exam type of this offline exam.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function type()
    {
        return $this->belongsTo(ExamType::class, 'type_id');
    }

    /**
     * Get the proctor (supervisor) of this offline exam.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function proctor()
    {
        return $this->belongsTo(User::class, 'proctor_id');
    }

    /**
     * Get the exam clearances for this offline exam.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function clearances()
    {
        return $this->morphMany(ExamClearance::class, 'clearable');
    }

    /**
     * Get the exam entry tickets for this offline exam.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function examEntryTickets()
    {
        return $this->morphMany(ExamEntryTicket::class, 'ticketable');
    }

    /**
     * Get all scores recorded for this offline exam.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function scores()
    {
        return $this->hasMany(OfflineExamScore::class);
    }

    /**
     * Scope a query to only include published exams.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Get the full title of the offline exam including course and type.
     * 
     * @return string
     */
    public function getFullTitleAttribute()
    {
        $courseTitle = $this->course ? $this->course->title : 'No Course';
        $typeName = $this->type ? $this->type->name : 'General';
        
        return "{$this->title} - {$courseTitle} ({$typeName})";
    }
}
