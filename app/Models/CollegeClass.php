<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * CollegeClass Model - Represents Academic Programs
 *
 * IMPORTANT: Despite the model name "CollegeClass", this entity represents ACADEMIC PROGRAMS
 * offered by the college, NOT traditional classroom sessions or courses.
 *
 * PURPOSE & DEFINITION:
 * - Academic Programs are structured educational offerings (e.g., "Computer Science Program", "Nursing Program")
 * - Programs are semester-independent and can run across multiple academic periods
 * - Programs are not tied to specific instructors as they represent institutional offerings
 * - Programs define the academic pathway students follow to complete their education
 *
 * BUSINESS RULES:
 * - Programs are independent entities not bound by semester limitations
 * - Programs do not require instructor assignment (instructors teach courses within programs)
 * - Programs can have multiple courses associated with them
 * - Students enroll in programs and take courses within those programs
 *
 * TERMINOLOGY STANDARDS:
 * - Frontend displays: "Program" / "Programs"
 * - User-facing references: "Academic Program", "College Program"
 * - Internal model name remains CollegeClass for database compatibility
 *
 * @deprecated The term "Class" in this context - use "Program" in all new development
 */
class CollegeClass extends Model
{
    protected $fillable = [
        'name',
        'short_name',
        'description',
        'room',
        'schedule',
        'status',
        'max_students',
        'is_active',
        'is_deleted',
        'created_by',
        'slug',
    ];

    // Course relationship removed - classes are now independent entities
    // /**
    //  * Get the course that this class belongs to
    //  */
    // public function course(): BelongsTo
    // {
    //     return $this->belongsTo(Course::class);
    // }

    /**
     * DEPRECATED: Programs are no longer tied to specific instructors
     * Instructors teach courses within programs, not programs themselves
     *
     * @deprecated Use course-instructor relationships instead
     */
    // public function instructor(): BelongsTo
    // {
    //     return $this->belongsTo(User::class, 'instructor_id');
    // }

    /**
     * DEPRECATED: Programs are semester-independent academic offerings
     * Programs run across multiple semesters and are not limited by semester boundaries
     *
     * @deprecated Programs should not be bound to semesters
     */
    // public function semester(): BelongsTo
    // {
    //     return $this->belongsTo(Semester::class);
    // }

    /**
     * Get all students enrolled in this program
     * Direct relationship via college_class_id foreign key
     */
    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'college_class_id');
    }

    /**
     * Get all student grades for this class
     */
    public function studentGrades(): HasMany
    {
        return $this->hasMany(StudentGrade::class, 'college_class_id');
    }

    /**
     * Get all grades for this class (legacy method)
     */
    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class, 'class_id');
    }

    /**
     * Scope for active classes
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for archived classes
     */
    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }

    /**
     * Generate short name from full program name
     * Uses intelligent algorithm to create meaningful abbreviations
     *
     * @param  string  $programName  The full program name
     * @return string Generated short name (max 10 characters)
     */
    public static function generateShortName(string $programName): string
    {
        // Common program name mappings
        $mappings = [
            'registered general nursing' => 'RGN',
            'general nursing' => 'RGN',
            'nursing' => 'RGN',
            'registered midwifery' => 'RM',
            'midwifery' => 'RM',
            'community health nursing' => 'CHN',
            'community health' => 'CHN',
            'psychiatric nursing' => 'PN',
            'mental health nursing' => 'PN',
            'psychiatric' => 'PN',
            'public health' => 'PH',
            'health administration' => 'HA',
            'medical laboratory' => 'MLS',
            'laboratory science' => 'MLS',
            'radiography' => 'RAD',
            'physiotherapy' => 'PHYSIO',
            'occupational therapy' => 'OT',
            'pharmacy' => 'PHARM',
            'dentistry' => 'DENT',
            'medicine' => 'MED',
            'computer science' => 'CS',
            'information technology' => 'IT',
            'software engineering' => 'SE',
            'business administration' => 'BA',
            'accounting' => 'ACC',
            'economics' => 'ECON',
            'marketing' => 'MKT',
        ];

        $lowerName = strtolower(trim($programName));

        // Check for exact or partial matches
        foreach ($mappings as $key => $shortName) {
            if ($lowerName === $key || strpos($lowerName, $key) !== false) {
                return $shortName;
            }
        }

        // Generate from initials of significant words
        $words = explode(' ', $lowerName);
        $shortName = '';

        foreach ($words as $word) {
            $word = trim($word);
            // Skip common words and focus on significant terms
            $skipWords = ['and', 'of', 'in', 'the', 'for', 'with', 'to', 'a', 'an'];

            if (! in_array($word, $skipWords) && strlen($word) > 2) {
                $shortName .= strtoupper($word[0]);
                if (strlen($shortName) >= 6) {
                    break;
                } // Reasonable length limit
            }
        }

        // If we didn't get enough characters, use first letters of all words
        if (strlen($shortName) < 2) {
            $shortName = '';
            foreach ($words as $word) {
                if (strlen($word) > 0) {
                    $shortName .= strtoupper($word[0]);
                    if (strlen($shortName) >= 5) {
                        break;
                    }
                }
            }
        }

        return $shortName ?: 'PROG'; // Fallback
    }

    /**
     * Get the short name for this program
     * Returns the stored short_name or generates one if not set
     */
    public function getShortNameAttribute($value): string
    {
        // If short_name is set, use it
        if (! empty($value)) {
            return $value;
        }

        // If we have a name, generate short name
        if (! empty($this->name)) {
            return self::generateShortName($this->name);
        }

        return 'PROG'; // Fallback
    }

    /**
     * Get the effective short name for Student ID generation
     * This method is used by the StudentIdGenerationService
     */
    public function getProgramCode(): string
    {
        return $this->short_name ?: self::generateShortName($this->name);
    }
}
