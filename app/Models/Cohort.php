<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cohort extends Model
{
    public function students()
    {
        return $this->hasMany(Student::class);
    }
}
