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

    public function isEligibleForExam()
    {
        // Check FeeCollections if student_id is present and if is_eligble is true
        $feeCollection = FeeCollection::where('student_id', $this->student_id)->first();
        // dd($feeCollection->is_eligble);
        if (!$feeCollection || !$feeCollection->is_eligble) {
            return false;
        } else {

            return true;
        }



        // Add your eligibility criteria here
    }

    public function createUser()
    {
        $user = User::create([
            'name' => $this->first_name . ' ' . $this->last_name,
            'email' => $this->email,
            'password' => bcrypt('password'),
        ]);
    }
}
