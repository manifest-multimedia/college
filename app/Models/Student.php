<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class Student extends Model
{
    // Get attribute name
    public function getNameAttribute()
    {
        return $this->first_name ?? 'N/A' . ' ' . $this->other_name ?? 'N/A' . ' ' . $this->last_name ?? 'N/A';
    }

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

    public function user()
    {
        return $this->hasOne(User::class, 'email', 'email');
    }


    public function createUser()
    {
        // Only create a user if a valid email exists and there is no existing user with the same email
        if ($this->email && !User::where('email', $this->email)->exists()) {
            $user = User::create([
                'name' => ($this->first_name ?? 'N/A') . ' ' . ($this->other_name ?? 'N/A') . ' ' . ($this->last_name ?? 'N/A'),
                'email' => $this->email,
                'password' => Hash::make('password'),
            ]);
        }
    }
    public function examSessions()
    {
        return $this->hasManyThrough(
            ExamSession::class, // Related model
            User::class,        // Intermediate model
            'email',            // Foreign key on the intermediate model (users.email)
            'student_id',       // Foreign key on the related model (exam_sessions.student_id)
            'email',            // Local key on the parent model (students.email)
            'id'                // Local key on the intermediate model (users.id)
        );
    }
}
