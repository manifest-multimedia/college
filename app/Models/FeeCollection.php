<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeeCollection extends Model
{
    protected $fillable = [
        'student_id',
        'student_name',
        'is_eligible',
    ];
}
