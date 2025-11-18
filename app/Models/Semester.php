<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Semester extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'academic_year_id',
        'start_date',
        'end_date',
        'is_current',
        'description',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_current' => 'boolean',
    ];

    /**
     * Get the academic year this semester belongs to
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    /**
     * Get all classes offered in this semester
     */
    public function classes(): HasMany
    {
        return $this->hasMany(CollegeClass::class);
    }

    /**
     * Subjects linked to this semester
     */
    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class);
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

    /**
     * Set this semester as current and unset others
     */
    public function setAsCurrent()
    {
        // Begin transaction
        DB::beginTransaction();

        try {
            // Unset all current semesters
            self::where('is_current', true)->update(['is_current' => false]);

            // Set this semester as current
            $this->is_current = true;
            $this->save();

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error setting current semester: '.$e->getMessage());

            return false;
        }
    }
}
