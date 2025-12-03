<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfflineExamScore extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'offline_exam_id',
        'student_id',
        'score',
        'total_marks',
        'percentage',
        'remarks',
        'recorded_by',
        'exam_date',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'score' => 'decimal:2',
        'total_marks' => 'decimal:2',
        'percentage' => 'decimal:2',
        'exam_date' => 'datetime',
    ];

    /**
     * Get the offline exam associated with this score.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function offlineExam()
    {
        return $this->belongsTo(OfflineExam::class);
    }

    /**
     * Get the student associated with this score.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the user who recorded this score.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * Automatically calculate percentage when score or total_marks changes.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if ($model->total_marks > 0) {
                $resultsService = app(\App\Services\ResultsService::class);
                $model->percentage = $resultsService->calculatePercentage($model->score, $model->total_marks);
            }
        });
    }

    /**
     * Get the grade letter based on percentage.
     *
     * @return string
     */
    public function getGradeLetterAttribute()
    {
        $resultsService = app(\App\Services\ResultsService::class);

        return $resultsService->getLetterGrade($this->percentage);
    }

    /**
     * Check if the student passed the exam.
     *
     * @return bool
     */
    public function isPassed()
    {
        $passingPercentage = $this->offlineExam->passing_percentage ?? 50;
        $resultsService = app(\App\Services\ResultsService::class);
        $status = $resultsService->getPassStatus($this->percentage, $passingPercentage);

        return $status === 'PASS';
    }
}
