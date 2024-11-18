<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Guardian extends Model
{
    public function students()
    {
        return $this->belongsToMany(Student::class);
    }

    public function address()
    {
        return $this->morphOne(Address::class, 'addressable');
    }

    public function contacts()
    {
        return $this->morphMany(Contact::class, 'contactable');
    }
}
