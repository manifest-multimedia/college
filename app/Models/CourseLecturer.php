<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseLecturer extends Model
{
    protected $table = 'course_lecturer';

    protected $fillable = [
        'user_id',
        'subject_id',
    ];

    /**
     * Get the lecturer/user for this assignment
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the subject/course for this assignment
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }
}
