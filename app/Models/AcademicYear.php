<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AcademicYear extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name', 
        'slug', 
        'start_date', 
        'end_date', 
        'year',
        'is_current'
    ];
    
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_current' => 'boolean',
        'is_deleted' => 'boolean',
    ];
    
    /**
     * Get all semesters belonging to this academic year
     */
    public function semesters()
    {
        return $this->hasMany(Semester::class);
    }
    
    /**
     * Get the current academic year
     */
    public static function getCurrent()
    {
        return self::where('is_current', true)->first();
    }
    
    /**
     * Set this academic year as current and unset others
     */
    public function setAsCurrent()
    {
        // Begin transaction
        \DB::beginTransaction();
        
        try {
            // Unset all current academic years
            self::where('is_current', true)->update(['is_current' => false]);
            
            // Set this academic year as current
            $this->is_current = true;
            $this->save();
            
            \DB::commit();
            return true;
        } catch (\Exception $e) {
            \DB::rollback();
            \Log::error('Error setting current academic year: ' . $e->getMessage());
            return false;
        }
    }
}
