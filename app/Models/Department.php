<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * The users that belong to the department.
     */
    public function users()
    {
        return $this->belongsToMany(User::class)->withPivot('is_head')->withTimestamps();
    }

    /**
     * Get the department head if assigned.
     */
    public function departmentHead()
    {
        return $this->belongsToMany(User::class)
            ->withPivot('is_head')
            ->wherePivot('is_head', true);
    }

    /**
     * Get the assets that belong to this department.
     */
    public function assets()
    {
        return $this->hasMany(Asset::class);
    }
}
