<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'payment_threshold',
        'description',
        'is_active'
    ];

    protected $casts = [
        'payment_threshold' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the exam clearances associated with this exam type
     */
    public function examClearances()
    {
        return $this->hasMany(ExamClearance::class);
    }

    /**
     * Get exams associated with this exam type
     */
    public function exams()
    {
        return $this->hasMany(Exam::class);
    }
}