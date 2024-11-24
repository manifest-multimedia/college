<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CollegeClass extends Model
{
    protected $fillable = [
        'name',
        'description',
        'slug'
    ];
    public function students()
    {
        return $this->hasMany(Student::class);
    }
}
