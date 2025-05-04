<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Semester extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'academic_year_id',
        'is_current',
        'description'
    ];

    protected $casts = [
        'is_current' => 'boolean',
    ];

    /**
     * Get the academic year this semester belongs to
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(Year::class, 'academic_year_id');
    }

    /**
     * Get all classes offered in this semester
     */
    public function classes(): HasMany
    {
        return $this->hasMany(CollegeClass::class);
    }

    /**
     * Check if this semester is active
     */
    public function isActive(): bool
    {
        return $this->is_current;
    }

    /**
     * Check if this semester is current
     */
    public function isCurrent(): bool
    {
        return $this->is_current;
    }

    /**
     * Scope for active semesters
     */
    public function scopeActive($query)
    {
        return $query->where('is_current', true);
    }

    /**
     * Scope for current semester
     */
    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }
}
