<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    public function addresses()
    {
        return $this->morphMany(Address::class, 'addressable');
    }

    public function collegeClass()
    {
        return $this->belongsTo(CollegeClass::class);
    }

    public function cohort()
    {
        return $this->belongsTo(Cohort::class);
    }

    public function guardians()
    {
        return $this->belongsToMany(Guardian::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function contacts()
    {
        return $this->morphMany(Contact::class, 'contactable');
    }
}
