<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    protected $fillable = ['course_id', 'user_id', 'type', 'duration', 'password', 'status'];

    /**
     * Course associated with this exam.
     */
    public function course()
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Lecturer who created this exam.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
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
}
