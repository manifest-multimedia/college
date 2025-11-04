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
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($office) {
            // Convert empty strings to null for nullable fields
            $office->code = $office->code ?: null;
            $office->location = $office->location ?: null;
            $office->phone = $office->phone ?: null;
            $office->email = $office->email ?: null;
            $office->description = $office->description ?: null;
        });
    }

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