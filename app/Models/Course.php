<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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


    public function exams()
    {
        return $this->hasMany(Exam::class);
    }
}
