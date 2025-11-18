<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\CollegeClass;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StudentIdGenerationService
{
    /**
     * Generate a student ID based on configured format
     * Supports multiple formats: structured, simple, custom
     * Uses database locking to prevent duplicate IDs
     */
    public function generateStudentId(
        string $firstName,
        string $lastName,
        ?int $collegeClassId = null,
        ?int $academicYearId = null
    ): string {
        $maxRetries = 5;
        $attempt = 0;

        while ($attempt < $maxRetries) {
            try {
                $format = config('branding.student_id.format', 'structured');
                $studentId = null;

                // Use database transaction with locking to ensure uniqueness
                DB::beginTransaction();

                switch ($format) {
                    case 'simple':
                        $studentId = $this->generateSimpleStudentId($firstName, $lastName, $collegeClassId, $academicYearId);
                        break;

                    case 'custom':
                        $studentId = $this->generateCustomStudentId($firstName, $lastName, $collegeClassId, $academicYearId);
                        break;

                    case 'structured':
                    default:
                        $studentId = $this->generateStructuredStudentId($firstName, $lastName, $collegeClassId, $academicYearId);
                        break;
                }

                // Check for uniqueness before committing
                if ($this->isStudentIdUnique($studentId)) {
                    DB::commit();
                    Log::info('Successfully generated unique student ID', [
                        'student_id' => $studentId,
                        'attempt' => $attempt + 1,
                        'student_name' => "{$firstName} {$lastName}",
                    ]);

                    return $studentId;
                } else {
                    DB::rollBack();
                    $attempt++;
                    Log::warning('Generated duplicate student ID, retrying', [
                        'student_id' => $studentId,
                        'attempt' => $attempt,
                        'student_name' => "{$firstName} {$lastName}",
                    ]);

                    // Short delay before retry to avoid race conditions
                    usleep(100000); // 100ms delay

                    continue;
                }

            } catch (\Exception $e) {
                DB::rollBack();
                $attempt++;

                Log::error('Error generating Student ID', [
                    'error' => $e->getMessage(),
                    'attempt' => $attempt,
                    'student_name' => "{$firstName} {$lastName}",
                    'class_id' => $collegeClassId,
                    'academic_year_id' => $academicYearId,
                ]);

                if ($attempt >= $maxRetries) {
                    break;
                }

                // Short delay before retry
                usleep(100000);
            }
        }

        // Final fallback if all retries failed
        Log::error('All attempts to generate unique student ID failed, using fallback', [
            'student_name' => "{$firstName} {$lastName}",
            'attempts' => $maxRetries,
        ]);

        return $this->generateFallbackStudentId($firstName, $lastName);
    }

    /**
     * Generate structured student ID (structured format)
     * Format: INSTITUTION_PREFIX/PROGRAM_CODE/ACADEMIC_YEAR/SEQUENCE
     * Example: COLLEGE/DEPT/RM/22/23/001
     */
    private function generateStructuredStudentId(
        string $firstName,
        string $lastName,
        ?int $collegeClassId = null,
        ?int $academicYearId = null
    ): string {
        // 1. Get Institution Prefix (e.g., "COLLEGE/DEPT")
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

        Log::info('Generated Structured Student ID', [
            'student_id' => $studentId,
            'format' => 'structured',
            'prefix' => $institutionPrefix,
            'program' => $programCode,
            'academic_year' => $academicYear,
            'sequence' => $sequenceNumber,
            'student_name' => "{$firstName} {$lastName}",
        ]);

        return $studentId;
    }

    /**
     * Generate simple student ID format
     * Format: PREFIXPROGRAMYEARSEQUENCE
     * Example: MHIAFRGN240135, MHIARM230022
     */
    private function generateSimpleStudentId(
        string $firstName,
        string $lastName,
        ?int $collegeClassId = null,
        ?int $academicYearId = null
    ): string {
        // 1. Get Institution Prefix (simplified)
        $institutionPrefix = $this->getSimpleInstitutionPrefix();

        // 2. Get Program Code (shortened)
        $programCode = $this->getSimpleProgramCode($collegeClassId);

        // 3. Get Academic Year (2-digit)
        $academicYear = $this->getSimpleAcademicYear($academicYearId);

        // 4. Generate Sequential Number (4-digit)
        $sequenceNumber = $this->generateSimpleSequenceNumber(
            $firstName,
            $lastName,
            $institutionPrefix,
            $programCode,
            $academicYear
        );

        // 5. Combine all parts
        $studentId = "{$institutionPrefix}{$programCode}{$academicYear}{$sequenceNumber}";

        Log::info('Generated Simple Student ID', [
            'student_id' => $studentId,
            'format' => 'simple',
            'prefix' => $institutionPrefix,
            'program' => $programCode,
            'academic_year' => $academicYear,
            'sequence' => $sequenceNumber,
            'student_name' => "{$firstName} {$lastName}",
        ]);

        return $studentId;
    }

    /**
     * Generate custom student ID using configurable pattern
     * Uses pattern from config with placeholders
     */
    private function generateCustomStudentId(
        string $firstName,
        string $lastName,
        ?int $collegeClassId = null,
        ?int $academicYearId = null
    ): string {
        $customPattern = config('branding.student_id.custom_pattern');

        if (empty($customPattern)) {
            // Fallback to structured if no custom pattern defined
            return $this->generateStructuredStudentId($firstName, $lastName, $collegeClassId, $academicYearId);
        }

        // Replace placeholders in custom pattern
        $replacements = [
            '{INSTITUTION}' => $this->getInstitutionPrefix(),
            '{INSTITUTION_SIMPLE}' => $this->getSimpleInstitutionPrefix(),
            '{PROGRAM}' => $this->getProgramCode($collegeClassId),
            '{PROGRAM_SIMPLE}' => $this->getSimpleProgramCode($collegeClassId),
            '{YEAR_FULL}' => $this->getAcademicYearCode($academicYearId),
            '{YEAR_SIMPLE}' => $this->getSimpleAcademicYear($academicYearId),
            '{SEQUENCE_3}' => $this->generateSequenceNumber($firstName, $lastName, '', '', ''),
            '{SEQUENCE_4}' => $this->generateSimpleSequenceNumber($firstName, $lastName, '', '', ''),
            '{FIRST_NAME}' => strtoupper(substr($firstName, 0, 2)),
            '{LAST_NAME}' => strtoupper(substr($lastName, 0, 2)),
        ];

        $studentId = str_replace(array_keys($replacements), array_values($replacements), $customPattern);

        Log::info('Generated Custom Student ID', [
            'student_id' => $studentId,
            'format' => 'custom',
            'pattern' => $customPattern,
            'student_name' => "{$firstName} {$lastName}",
        ]);

        return $studentId;
    }

    /**
     * Get the institution prefix from student ID configuration
     * Uses new dedicated student ID settings instead of general settings
     */
    private function getInstitutionPrefix(): string
    {
        // Primary source: Student ID specific configuration
        $prefix = config('branding.student_id.institution_prefix');
        if ($prefix && $prefix !== 'COLLEGE/DEPT') {
            return $prefix;
        }

        // Fallback: Try old settings table for backward compatibility
        try {
            $legacyPrefix = DB::table('settings')
                ->where('key', 'school_name_prefix')
                ->value('value');

            if ($legacyPrefix) {
                return $legacyPrefix;
            }
        } catch (\Exception $e) {
            Log::info('Settings table not available, using configuration fallback');
        }

        // Final fallback: Use institution configuration
        $institutionName = config('branding.institution.short_name', 'COLLEGE');

        return $institutionName.'/DEPT';
    }

    /**
     * Get program code from college class
     * Maps college class to program codes
     * Now uses short_name field if available, falls back to auto-generation
     */
    private function getProgramCode(?int $collegeClassId): string
    {
        if (! $collegeClassId) {
            return 'GEN'; // General program
        }

        try {
            $collegeClass = CollegeClass::find($collegeClassId);

            if (! $collegeClass) {
                return 'GEN';
            }

            // First priority: Use the program's getProgramCode() method
            // This will use short_name if set, or auto-generate from name
            if (method_exists($collegeClass, 'getProgramCode')) {
                $programCode = $collegeClass->getProgramCode();
                if (! empty($programCode) && $programCode !== 'PROG') {
                    return $programCode;
                }
            }

            // Second priority: Use short_name field directly if available
            if (! empty($collegeClass->short_name)) {
                return strtoupper($collegeClass->short_name);
            }

            // Fallback: Use existing extraction logic for backward compatibility
            return $this->extractProgramCode($collegeClass->name);

        } catch (\Exception $e) {
            Log::warning('Could not determine program code', [
                'class_id' => $collegeClassId,
                'error' => $e->getMessage(),
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
                if (strlen($code) >= 3) {
                    break;
                } // Max 3 characters
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
                'error' => $e->getMessage(),
            ]);

            // Fallback
            $currentYear = now()->format('y');
            $nextYear = now()->addYear()->format('y');

            return "{$currentYear}/{$nextYear}";
        }
    }

    /**
     * Generate sequence number based on alphabetical order of surnames
     * Uses database locking to prevent race conditions
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

            // Use FOR UPDATE lock to prevent concurrent sequence generation
            $existingStudents = Student::where('student_id', 'like', $pattern)
                ->lockForUpdate()
                ->orderBy('last_name', 'asc')
                ->orderBy('first_name', 'asc')
                ->get(['student_id', 'first_name', 'last_name']);

            if (config('branding.student_id.enable_alphabetical_ordering', true)) {
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
            } else {
                // Simple incremental numbering
                $position = $existingStudents->count() + 1;
            }

            // Format as 3-digit number
            return str_pad($position, 3, '0', STR_PAD_LEFT);

        } catch (\Exception $e) {
            Log::error('Error generating sequence number', [
                'error' => $e->getMessage(),
                'pattern' => $pattern ?? 'unknown',
            ]);

            // Fallback: use random sequence to avoid duplicates
            $timestamp = now()->format('His'); // HHMMSS
            $random = str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);

            return substr($timestamp.$random, -3); // Last 3 digits
        }
    }

    /**
     * Check if a student ID is unique in the database
     */
    private function isStudentIdUnique(string $studentId): bool
    {
        return ! Student::where('student_id', $studentId)->exists();
    }

    /**
     * Fallback student ID generation with uniqueness guarantee
     */
    private function generateFallbackStudentId(string $firstName = '', string $lastName = ''): string
    {
        $maxAttempts = 10;
        $attempt = 0;

        while ($attempt < $maxAttempts) {
            $year = now()->year;
            $timestamp = now()->format('His'); // HHMMSS for uniqueness

            $prefix = config('branding.student_id.institution_simple', 'STU');

            // Generate a unique sequence using timestamp + random
            $sequence = $timestamp.str_pad(mt_rand(1, 99), 2, '0', STR_PAD_LEFT);
            $studentId = $prefix.$year.substr($sequence, -4);

            if ($this->isStudentIdUnique($studentId)) {
                Log::info('Generated fallback student ID', [
                    'student_id' => $studentId,
                    'attempt' => $attempt + 1,
                    'student_name' => trim("{$firstName} {$lastName}") ?: 'Unknown',
                ]);

                return $studentId;
            }

            $attempt++;
            usleep(50000); // 50ms delay
        }

        // Final desperate fallback using microtime
        $microtime = str_replace('.', '', microtime(true));
        $finalId = $prefix.substr($microtime, -8);

        Log::warning('Using microtime-based fallback student ID', [
            'student_id' => $finalId,
            'student_name' => trim("{$firstName} {$lastName}") ?: 'Unknown',
        ]);

        return $finalId;
    }

    /**
     * Get simplified institution prefix for simple format
     */
    private function getSimpleInstitutionPrefix(): string
    {
        // Primary source: Student ID specific simple configuration
        $simplePrefix = config('branding.student_id.institution_simple');
        if ($simplePrefix && $simplePrefix !== 'COLLEGE') {
            return $simplePrefix;
        }

        // Fallback: Extract from full institution prefix
        $fullPrefix = $this->getInstitutionPrefix();
        if ($fullPrefix) {
            // Extract letters only and limit to 4-5 characters
            $simplified = preg_replace('/[^A-Z]/', '', strtoupper($fullPrefix));
            $extracted = substr($simplified, 0, 5);
            if ($extracted) {
                return $extracted;
            }
        }

        // Final fallback: Use institution name
        $institutionName = config('branding.institution.name', config('branding.institution.short_name', 'COLLEGE'));
        $simplified = preg_replace('/[^A-Z]/', '', strtoupper($institutionName));

        return substr($simplified, 0, 5) ?: 'COLLEGE';
    }

    /**
     * Get simplified program code for simple format
     */
    private function getSimpleProgramCode(?int $collegeClassId): string
    {
        $programCode = $this->getProgramCode($collegeClassId);

        // For simple format, we might want different mappings
        $simpleMappings = [
            'RM' => 'RM',
            'RGN' => 'RGN',
            'CHN' => 'CHN',
            'PN' => 'PN',
            'GEN' => 'GEN',
        ];

        return $simpleMappings[$programCode] ?? $programCode;
    }

    /**
     * Get simple academic year (2-digit)
     */
    private function getSimpleAcademicYear(?int $academicYearId): string
    {
        try {
            $academicYear = null;

            if ($academicYearId) {
                $academicYear = AcademicYear::find($academicYearId);
            } else {
                $academicYear = AcademicYear::where('is_current', true)->first();
            }

            if ($academicYear && $academicYear->start_date) {
                return $academicYear->start_date->format('y'); // 2-digit year
            }

            return now()->format('y'); // Fallback to current year

        } catch (\Exception $e) {
            return now()->format('y');
        }
    }

    /**
     * Generate 4-digit sequence number for simple format
     * Uses database locking to prevent race conditions
     */
    private function generateSimpleSequenceNumber(
        string $firstName,
        string $lastName,
        string $institutionPrefix,
        string $programCode,
        string $academicYear
    ): string {
        try {
            // Build pattern for simple format
            $pattern = "{$institutionPrefix}{$programCode}{$academicYear}%";

            // Use FOR UPDATE lock to prevent concurrent sequence generation
            $existingStudents = Student::where('student_id', 'like', $pattern)
                ->lockForUpdate()
                ->orderBy('last_name', 'asc')
                ->orderBy('first_name', 'asc')
                ->get(['student_id', 'first_name', 'last_name']);

            if (config('branding.student_id.enable_alphabetical_ordering', true)) {
                // Find correct alphabetical position
                $position = 1;
                $fullName = "{$lastName}, {$firstName}";

                foreach ($existingStudents as $student) {
                    $existingName = "{$student->last_name}, {$student->first_name}";
                    if (strcasecmp($fullName, $existingName) < 0) {
                        break;
                    }
                    $position++;
                }

                return str_pad($position, 4, '0', STR_PAD_LEFT);
            } else {
                // Simple incremental numbering with lock
                $count = $existingStudents->count();

                return str_pad($count + 1, 4, '0', STR_PAD_LEFT);
            }

        } catch (\Exception $e) {
            Log::error('Error generating simple sequence number', [
                'error' => $e->getMessage(),
                'pattern' => $pattern ?? 'unknown',
            ]);

            // Fallback: use timestamp + random to avoid duplicates
            $timestamp = now()->format('His'); // HHMMSS
            $random = str_pad(mt_rand(1, 99), 2, '0', STR_PAD_LEFT);

            return substr($timestamp.$random, -4); // Last 4 digits
        }
    }

    /**
     * Validate if a student ID format is correct
     */
    public function isValidStudentIdFormat(string $studentId): bool
    {
        // Check structured format: PREFIX/PROGRAM/YY/YY/NNN
        $structuredPattern = '/^[A-Z\/]+\/[A-Z]+\/\d{2}\/\d{2}\/\d{3}$/';
        if (preg_match($structuredPattern, $studentId)) {
            return true;
        }

        // Check simple format: PREFIXPROGRAMYEARSEQUENCE (e.g., MHIARM240135)
        $simplePattern = '/^[A-Z]{4,6}[A-Z]{2,3}\d{6}$/';
        if (preg_match($simplePattern, $studentId)) {
            return true;
        }

        // Check old format: PREFIXYYYYNNNN
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
            'updated_students' => [],
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
                        'new_id' => $newId,
                    ];

                    $results['success']++;

                } catch (\Exception $e) {
                    $results['errors']++;
                    Log::error('Error updating student ID', [
                        'student_id' => $student->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

        } catch (\Exception $e) {
            Log::error('Error in batch student ID regeneration', [
                'error' => $e->getMessage(),
            ]);
        }

        return $results;
    }
}
