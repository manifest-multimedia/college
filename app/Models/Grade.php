<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Grade extends Model
{
    protected $fillable = [
        'name',
        'type',
        'value',
        'description',
        'slug',
        'is_deleted',
        'created_by',
    ];

    protected $casts = [
        'is_deleted' => 'boolean',
    ];

    /**
     * Get the user who created this grade definition
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all student grades using this grade definition
     */
    public function studentGrades()
    {
        return $this->hasMany(StudentGrade::class, 'grade_id');
    }

    /**
     * Scope for active (non-deleted) grades
     */
    public function scopeActive($query)
    {
        return $query->where('is_deleted', false);
    }

    /**
     * Scope to filter by grade type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }
}
