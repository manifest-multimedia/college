<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Year extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'start_date',
        'end_date',
        'status',
        'description'
    ];
    
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];
    
    /**
     * Get all semesters for this academic year
     */
    public function semesters(): HasMany
    {
        return $this->hasMany(Semester::class, 'academic_year_id');
    }
    
    /**
     * Get active semesters for this academic year
     */
    public function activeSemesters()
    {
        return $this->semesters()->where('status', 'active');
    }
    
    /**
     * Get current semester (if any)
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
     * Check if this academic year is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }
    
    /**
     * Check if this academic year is current
     */
    public function isCurrent(): bool
    {
        $now = now();
        return $now->between($this->start_date, $this->end_date) && $this->isActive();
    }
    
    /**
     * Scope for active academic years
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
    
    /**
     * Scope for current academic year
     */
    public function scopeCurrent($query)
    {
        $now = now();
        return $query->where('status', 'active')
            ->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now);
    }
}
