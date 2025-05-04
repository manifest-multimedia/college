<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cohort extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name',
        'description',
        'slug',
        'academic_year',
        'is_active',
        'is_deleted'
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
        'is_deleted' => 'boolean',
    ];

    /**
     * Get all students in this cohort
     */
    public function students()
    {
        return $this->hasMany(Student::class);
    }
}
