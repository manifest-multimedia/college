<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable = ['course_code', 'name', 'semester_id', 'year_id', 'college_class_id'];

    // Define relationships
    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function year()
    {
        return $this->belongsTo(Year::class);
    }

    public function collegeClass()
    {
        return $this->belongsTo(CollegeClass::class);
    }
}
