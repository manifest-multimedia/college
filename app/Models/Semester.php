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
        'start_date',
        'end_date',
        'registration_start',
        'registration_end',
        'status',
        'description'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'registration_start' => 'date',
        'registration_end' => 'date',
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
     * Check if registration is currently open
     */
    public function isRegistrationOpen(): bool
    {
        $now = now();
        return $now->between($this->registration_start, $this->registration_end) && $this->status === 'active';
    }

    /**
     * Check if this semester is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if this semester is current
     */
    public function isCurrent(): bool
    {
        $now = now();
        return $now->between($this->start_date, $this->end_date) && $this->isActive();
    }

    /**
     * Scope for active semesters
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for current semester
     */
    public function scopeCurrent($query)
    {
        $now = now();
        return $query->where('status', 'active')
            ->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now);
    }

    /**
     * Scope for semesters with open registration
     */
    public function scopeOpenRegistration($query)
    {
        $now = now();
        return $query->where('status', 'active')
            ->where('registration_start', '<=', $now)
            ->where('registration_end', '>=', $now);
    }
}
