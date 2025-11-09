<?php

namespace App\Services;

use App\Models\Student;
use App\Models\AcademicYear;
use App\Models\CollegeClass;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StudentIdGenerationService
{
    /**
     * Generate a student ID based on the new PNMTC format
     * Format: PREFIX/PROGRAM_CODE/ACADEMIC_YEAR/SEQUENCE_NUMBER
     * Example: PNMTC/DA/RM/22/23/001
     * 
     * @param string $firstName
     * @param string $lastName  
     * @param int|null $collegeClassId
     * @param int|null $academicYearId
     * @return string
     */
    public function generateStudentId(
        string $firstName, 
        string $lastName, 
        ?int $collegeClassId = null,
        ?int $academicYearId = null
    ): string {
        try {
            // 1. Get Institution Prefix (e.g., "PNMTC/DA")
            $institutionPrefix = $this->getInstitutionPrefix();
            
            // 2. Get Program Code (e.g., "RM")
            $programCode = $this->getProgramCode($collegeClassId);
            
            // 3. Get Academic Year (e.g., "22/23")
            $academicYear = $this->getAcademicYearCode($academicYearId);
            
            // 4. Generate Sequential Number (alphabetically sorted)
            $sequenceNumber = $this->generateSequenceNumber(
                $firstName, 
                $lastName, 
                $institutionPrefix, 
                $programCode, 
                $academicYear
            );
            
            // 5. Combine all parts
            $studentId = "{$institutionPrefix}/{$programCode}/{$academicYear}/{$sequenceNumber}";
            
            Log::info('Generated Student ID', [
                'student_id' => $studentId,
                'prefix' => $institutionPrefix,
                'program' => $programCode,
                'academic_year' => $academicYear,
                'sequence' => $sequenceNumber,
                'student_name' => "{$firstName} {$lastName}"
            ]);
            
            return $studentId;
            
        } catch (\Exception $e) {
            Log::error('Error generating Student ID', [
                'error' => $e->getMessage(),
                'student_name' => "{$firstName} {$lastName}",
                'class_id' => $collegeClassId,
                'academic_year_id' => $academicYearId
            ]);
            
            // Fallback to simple generation
            return $this->generateFallbackStudentId();
        }
    }
    
    /**
     * Get the institution prefix from settings
     * Default format: PNMTC/DA
     */
    private function getInstitutionPrefix(): string
    {
        $prefix = DB::table('settings')
            ->where('key', 'school_name_prefix')
            ->value('value');
            
        // Default to PNMTC/DA if not set
        return $prefix ?: 'PNMTC/DA';
    }
    
    /**
     * Get program code from college class
     * Maps college class to program codes
     * Now uses short_name field if available, falls back to auto-generation
     */
    private function getProgramCode(?int $collegeClassId): string
    {
        if (!$collegeClassId) {
            return 'GEN'; // General program
        }
        
        try {
            $collegeClass = CollegeClass::find($collegeClassId);
            
            if (!$collegeClass) {
                return 'GEN';
            }
            
            // First priority: Use the program's getProgramCode() method
            // This will use short_name if set, or auto-generate from name
            if (method_exists($collegeClass, 'getProgramCode')) {
                $programCode = $collegeClass->getProgramCode();
                if (!empty($programCode) && $programCode !== 'PROG') {
                    return $programCode;
                }
            }
            
            // Second priority: Use short_name field directly if available
            if (!empty($collegeClass->short_name)) {
                return strtoupper($collegeClass->short_name);
            }
            
            // Fallback: Use existing extraction logic for backward compatibility
            return $this->extractProgramCode($collegeClass->name);
            
        } catch (\Exception $e) {
            Log::warning('Could not determine program code', [
                'class_id' => $collegeClassId,
                'error' => $e->getMessage()
            ]);
            return 'GEN';
        }
    }
    
    /**
     * Extract program code from class name or use predefined mapping
     */
    private function extractProgramCode(string $className): string
    {
        // Common program mappings for nursing colleges
        $programMappings = [
            // Registered Midwifery
            'midwifery' => 'RM',
            'registered midwifery' => 'RM',
            'rm' => 'RM',
            
            // Registered General Nursing
            'nursing' => 'RGN',
            'general nursing' => 'RGN',
            'registered general nursing' => 'RGN',
            'rgn' => 'RGN',
            
            // Community Health Nursing
            'community health' => 'CHN',
            'community health nursing' => 'CHN',
            'chn' => 'CHN',
            
            // Psychiatric Nursing
            'psychiatric' => 'PN',
            'psychiatric nursing' => 'PN',
            'mental health' => 'PN',
            'pn' => 'PN',
            
            // Other programs
            'diploma' => 'DIP',
            'certificate' => 'CERT',
            'degree' => 'DEG',
        ];
        
        $classNameLower = strtolower($className);
        
        // Check for exact matches first
        if (isset($programMappings[$classNameLower])) {
            return $programMappings[$classNameLower];
        }
        
        // Check for partial matches
        foreach ($programMappings as $keyword => $code) {
            if (strpos($classNameLower, $keyword) !== false) {
                return $code;
            }
        }
        
        // If no match found, generate a code from the class name
        $words = explode(' ', $classNameLower);
        $code = '';
        
        foreach ($words as $word) {
            if (strlen($word) > 2) { // Skip short words
                $code .= strtoupper($word[0]);
                if (strlen($code) >= 3) break; // Max 3 characters
            }
        }
        
        return $code ?: 'GEN';
    }
    
    /**
     * Get academic year code in YY/YY format
     */
    private function getAcademicYearCode(?int $academicYearId): string
    {
        try {
            $academicYear = null;
            
            if ($academicYearId) {
                $academicYear = AcademicYear::find($academicYearId);
            } else {
                // Get current academic year
                $academicYear = AcademicYear::where('is_current', true)->first();
            }
            
            if ($academicYear && $academicYear->start_date && $academicYear->end_date) {
                $startYear = $academicYear->start_date->format('y'); // 2-digit year
                $endYear = $academicYear->end_date->format('y');
                return "{$startYear}/{$endYear}";
            }
            
            // Fallback to current year
            $currentYear = now()->format('y');
            $nextYear = now()->addYear()->format('y');
            return "{$currentYear}/{$nextYear}";
            
        } catch (\Exception $e) {
            Log::warning('Could not determine academic year', [
                'academic_year_id' => $academicYearId,
                'error' => $e->getMessage()
            ]);
            
            // Fallback
            $currentYear = now()->format('y');
            $nextYear = now()->addYear()->format('y');
            return "{$currentYear}/{$nextYear}";
        }
    }
    
    /**
     * Generate sequence number based on alphabetical order of surnames
     */
    private function generateSequenceNumber(
        string $firstName, 
        string $lastName, 
        string $institutionPrefix, 
        string $programCode, 
        string $academicYear
    ): string {
        try {
            // Build the pattern for this cohort
            $pattern = "{$institutionPrefix}/{$programCode}/{$academicYear}/%";
            
            // Get all existing students in this cohort, ordered alphabetically by last name
            $existingStudents = Student::where('student_id', 'like', $pattern)
                ->orderBy('last_name', 'asc')
                ->orderBy('first_name', 'asc')
                ->get(['student_id', 'first_name', 'last_name']);
            
            // Find the correct position for this student alphabetically
            $position = 1;
            $fullName = "{$lastName}, {$firstName}";
            
            foreach ($existingStudents as $student) {
                $existingName = "{$student->last_name}, {$student->first_name}";
                
                // If current student comes alphabetically before existing student
                if (strcasecmp($fullName, $existingName) < 0) {
                    break;
                }
                $position++;
            }
            
            // Format as 3-digit number
            return str_pad($position, 3, '0', STR_PAD_LEFT);
            
        } catch (\Exception $e) {
            Log::error('Error generating sequence number', [
                'error' => $e->getMessage(),
                'pattern' => $pattern ?? 'unknown'
            ]);
            
            // Fallback: just count existing students + 1
            $count = Student::where('student_id', 'like', $pattern)->count();
            return str_pad($count + 1, 3, '0', STR_PAD_LEFT);
        }
    }
    
    /**
     * Fallback student ID generation (original method)
     */
    private function generateFallbackStudentId(): string
    {
        $year = now()->year;
        
        $prefix = DB::table('settings')
            ->where('key', 'school_name_prefix')
            ->value('value') ?? 'STU';
        
        $lastStudent = Student::where('student_id', 'like', $prefix . $year . '%')
            ->orderBy('student_id', 'desc')
            ->first();
        
        if ($lastStudent) {
            $lastNumber = (int) substr($lastStudent->student_id, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . $year . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
    
    /**
     * Validate if a student ID format is correct
     */
    public function isValidStudentIdFormat(string $studentId): bool
    {
        // Check if it matches the new format: PREFIX/PROGRAM/YY/YY/NNN
        $pattern = '/^[A-Z\/]+\/[A-Z]+\/\d{2}\/\d{2}\/\d{3}$/';
        
        if (preg_match($pattern, $studentId)) {
            return true;
        }
        
        // Check if it matches the old format: PREFIXYYYYNNNN
        $oldPattern = '/^[A-Z]+\d{8}$/';
        return preg_match($oldPattern, $studentId);
    }
    
    /**
     * Re-generate student IDs for existing students in a specific cohort
     * This is useful for batch updates
     */
    public function regenerateStudentIds(?int $collegeClassId = null, ?int $academicYearId = null): array
    {
        $results = [
            'success' => 0,
            'errors' => 0,
            'updated_students' => []
        ];
        
        try {
            $query = Student::query();
            
            if ($collegeClassId) {
                $query->where('college_class_id', $collegeClassId);
            }
            
            // Order alphabetically for proper sequence assignment
            $students = $query->orderBy('last_name', 'asc')
                ->orderBy('first_name', 'asc')
                ->get();
            
            foreach ($students as $student) {
                try {
                    $oldId = $student->student_id;
                    $newId = $this->generateStudentId(
                        $student->first_name,
                        $student->last_name,
                        $student->college_class_id,
                        $academicYearId
                    );
                    
                    $student->student_id = $newId;
                    $student->save();
                    
                    $results['updated_students'][] = [
                        'id' => $student->id,
                        'name' => "{$student->first_name} {$student->last_name}",
                        'old_id' => $oldId,
                        'new_id' => $newId
                    ];
                    
                    $results['success']++;
                    
                } catch (\Exception $e) {
                    $results['errors']++;
                    Log::error('Error updating student ID', [
                        'student_id' => $student->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
        } catch (\Exception $e) {
            Log::error('Error in batch student ID regeneration', [
                'error' => $e->getMessage()
            ]);
        }
        
        return $results;
    }
}