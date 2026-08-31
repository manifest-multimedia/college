<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Represents a student's level of study, for example Year 1, Year 2 or Year 3.
 *
 * This model is deliberately separate from AcademicYear. AcademicYear records
 * an institutional calendar (for example 2026/2027); Year describes a stage in
 * a programme of training and is used by curriculum and subject assignments.
 */
class Year extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'slug',
        'start_date',
        'end_date',
        'status',
        'description',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Legacy relationship retained for compatibility only.
     *
     * A Year of Study is not an Academic Year and must not be used to control
     * the institutional calendar or the current semester.
     */
    public function semesters(): HasMany
    {
        return $this->hasMany(Semester::class, 'academic_year_id');
    }

    /**
     * Legacy helper retained for compatibility only.
     */
    public function activeSemesters()
    {
        return $this->semesters()->where('status', 'active');
    }

    /**
     * Legacy helper retained for compatibility only.
     */
    public function currentSemester()
    {
        $now = now();

        return $this->semesters()
            ->where('status', 'active')
            ->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now)
            ->first();
    }

    /**
     * Check whether this study level is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Determine whether this study level is currently active by its configured
     * dates. This does not represent the current Academic Year.
     */
    public function isCurrent(): bool
    {
        $now = now();

        return $now->between($this->start_date, $this->end_date) && $this->isActive();
    }

    /**
     * Scope for active study levels.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for currently active study levels by date.
     */
    public function scopeCurrent($query)
    {
        $now = now();

        return $query->where('status', 'active')
            ->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now);
    }
}
