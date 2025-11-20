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
        'is_active',
    ];

    protected $casts = [
        'payment_threshold' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the exam clearances associated with this exam type
     *
     * @deprecated This relationship is maintained for backward compatibility
     */
    public function examClearances()
    {
        return $this->hasMany(ExamClearance::class);
    }

    /**
     * Get online exams associated with this exam type
     */
    public function exams()
    {
        return $this->hasMany(Exam::class, 'type_id');
    }

    /**
     * Get offline exams associated with this exam type
     */
    public function offlineExams()
    {
        return $this->hasMany(OfflineExam::class, 'type_id');
    }

    /**
     * Get all exams (both online and offline) associated with this exam type
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAllExamsAttribute()
    {
        return $this->exams->merge($this->offlineExams);
    }
}
