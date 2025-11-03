<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Office extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id',
        'name',
        'code',
        'location',
        'phone',
        'email',
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the department that owns the office.
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get all assets for this office.
     */
    public function assets()
    {
        return $this->hasMany(Asset::class);
    }

    /**
     * Scope a query to only include active offices.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}