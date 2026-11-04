<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentEmergencyContact extends Model
{
    protected $fillable = [
        'full_name', 'relationship', 'mobile_number', 'alternate_phone',
        'email', 'gps_address', 'residential_address',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
