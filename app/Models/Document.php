<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
