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
        ?int $academicYearId = null,
        bool $allowFallback = true
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

        if (! $allowFallback) {
            throw new \RuntimeException('Unable to generate a unique ID using the configured format.');
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
        ?int $academicYearId = null,
        ?int $preservedSequence = null
    ): string {
        $customPattern = config('branding.student_id.custom_pattern');

        if (empty($customPattern)) {
            // Fallback to structured if no custom pattern defined
            return $this->generateStructuredStudentId($firstName, $lastName, $collegeClassId, $academicYearId);
        }

        // Resolve common parts once so sequence generation can be correctly scoped
        $institutionPrefix = $this->getInstitutionPrefix();
        $programCode = $this->getProgramCode($collegeClassId);
        $academicYear = $this->getAcademicYearCode($academicYearId);

        // CRITICAL: Ensure program code is never empty to avoid double-slash IDs like "PNMTC//25/001"
        if (empty($programCode)) {
            Log::warning('Empty program code detected during custom ID generation', [
                'college_class_id' => $collegeClassId,
                'student_name' => "{$firstName} {$lastName}",
            ]);
            $programCode = 'GEN'; // Fallback to GEN if program code resolution fails
        }

        // Replace placeholders in custom pattern
        $replacements = [
            '{INSTITUTION}' => $institutionPrefix,
            '{INSTITUTION_SIMPLE}' => $this->getSimpleInstitutionPrefix(),
            '{PROGRAM}' => $programCode,
            '{PROGRAM_SIMPLE}' => $this->getSimpleProgramCode($collegeClassId),
            '{YEAR_FULL}' => $academicYear,
            '{YEAR_SIMPLE}' => $this->getSimpleAcademicYear($academicYearId),
            '{FIRST_NAME}' => strtoupper(substr($firstName, 0, 2)),
            '{LAST_NAME}' => strtoupper(substr($lastName, 0, 2)),
        ];

        // Resolve fixed parts first, then allocate against the actual custom
        // pattern. Structured/simple patterns may not match these identifiers.
        $template = str_replace(array_keys($replacements), array_values($replacements), $customPattern);
        $studentId = $preservedSequence === null
            ? $this->fillCustomSequence($template, $firstName, $lastName)
            : str_replace(['{SEQUENCE_3}', '{SEQUENCE_4}'], [str_pad($preservedSequence, 3, '0', STR_PAD_LEFT), str_pad($preservedSequence, 4, '0', STR_PAD_LEFT)], $template);

        Log::info('Generated Custom Student ID', [
            'student_id' => $studentId,
            'format' => 'custom',
            'pattern' => $customPattern,
            'replacements' => $replacements, // Log actual replacement values for debugging
            'student_name' => "{$firstName} {$lastName}",
        ]);

        return $studentId;
    }

    private function fillCustomSequence(string $template, string $firstName, string $lastName): string
    {
        if (! preg_match('/\{SEQUENCE_[34]\}/', $template, $token, PREG_OFFSET_CAPTURE)) {
            return $template;
        }

        $prefix = substr($template, 0, $token[0][1]);
        $regex = '/^'.str_replace(
            [preg_quote('{SEQUENCE_3}', '/'), preg_quote('{SEQUENCE_4}', '/')],
            ['([0-9]{3,})', '([0-9]{4,})'],
            preg_quote($template, '/')
        ).'$/D';
        $existing = Student::where('student_id', 'like', $prefix.'%')
            ->lockForUpdate()->orderBy('last_name')->orderBy('first_name')
            ->get(['student_id', 'first_name', 'last_name'])
            ->filter(fn ($student) => preg_match($regex, $student->student_id));

        $start = max(1, (int) config('branding.student_id.sequence_start', 1));
        if (config('branding.student_id.enable_alphabetical_ordering', true)) {
            $position = $start;
            foreach ($existing as $student) {
                $last = strcasecmp($lastName, (string) $student->last_name);
                if ($last < 0 || ($last === 0 && strcasecmp($firstName, (string) $student->first_name) < 0)) {
                    break;
                }
                $position++;
            }
        } else {
            $position = $start;
            foreach ($existing as $student) {
                preg_match($regex, $student->student_id, $parts);
                $position = max($position, (int) $parts[1] + 1);
            }
        }

        do {
            $candidate = str_replace(
                ['{SEQUENCE_3}', '{SEQUENCE_4}'],
                [str_pad($position, 3, '0', STR_PAD_LEFT), str_pad($position, 4, '0', STR_PAD_LEFT)],
                $template
            );
            $position++;
        } while (Student::where('student_id', $candidate)->exists());

        return $candidate;
    }

    /**
     * Get the institution prefix from student ID configuration
     * Uses new dedicated student ID settings instead of general settings
     */
    public function configuredInstitutionPrefix(): ?string
    {
        // Primary source: Student ID specific configuration
        $prefix = trim((string) config('branding.student_id.institution_prefix', ''));
        if ($prefix !== '' && strtoupper($prefix) !== 'COLLEGE/DEPT') {
            return $prefix;
        }

        // Fallback: Try old settings table for backward compatibility
        try {
            $legacyPrefix = DB::table('settings')
                ->where('key', 'school_name_prefix')
                ->value('value');

            $legacyPrefix = trim((string) $legacyPrefix);
            if ($legacyPrefix !== '' && strtoupper($legacyPrefix) !== 'COLLEGE/DEPT') {
                return $legacyPrefix;
            }
        } catch (\Exception $e) {
            Log::info('Settings table not available, using configuration fallback');
        }

        return null;
    }

    /** Resolve the configured prefix, preserving the generator's setup fallback. */
    public function getInstitutionPrefix(): string
    {
        $prefix = $this->configuredInstitutionPrefix();
        if ($prefix !== null) {
            return $prefix;
        }

        // Final fallback for ID generation only, never an import authorization.
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
                Log::warning('College class not found', ['college_class_id' => $collegeClassId]);

                return 'GEN';
            }

            // First priority: Use the program's getProgramCode() method
            // This will use short_name if set, or auto-generate from name
            if (method_exists($collegeClass, 'getProgramCode')) {
                $programCode = $collegeClass->getProgramCode();
                if (! empty($programCode) && $programCode !== 'PROG') {
                    Log::debug('Program code from model method', [
                        'college_class_id' => $collegeClassId,
                        'program_code' => $programCode,
                        'source' => 'getProgramCode()',
                    ]);

                    return $programCode;
                }
            }

            // Second priority: Use short_name field directly if available
            if (! empty($collegeClass->short_name)) {
                $code = strtoupper($collegeClass->short_name);
                Log::debug('Program code from short_name', [
                    'college_class_id' => $collegeClassId,
                    'program_code' => $code,
                    'source' => 'short_name',
                ]);

                return $code;
            }

            // Fallback: Use existing extraction logic for backward compatibility
            $extracted = $this->extractProgramCode($collegeClass->name);

            // Final safety: never return empty string
            if (empty($extracted)) {
                Log::error('Program code extraction returned empty - using GEN fallback', [
                    'college_class_id' => $collegeClassId,
                    'class_name' => $collegeClass->name ?? 'N/A',
                    'class_short_name' => $collegeClass->short_name ?? null,
                    'has_getProgramCode_method' => method_exists($collegeClass, 'getProgramCode'),
                ]);

                return 'GEN';
            }

            Log::debug('Program code from name extraction', [
                'college_class_id' => $collegeClassId,
                'program_code' => $extracted,
                'source' => 'extractProgramCode',
            ]);

            return $extracted;

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

                foreach ($existingStudents as $student) {
                    // Compare Last Names first (to match SQL sort order)
                    $lastNameCmp = strcasecmp($lastName, $student->last_name);

                    if ($lastNameCmp < 0) {
                        // New student's last name comes before existing -> Insert here
                        break;
                    } elseif ($lastNameCmp > 0) {
                        // New student's last name comes after -> Continue
                        $position++;

                        continue;
                    }

                    // Last names are equal, compare First Names
                    $firstNameCmp = strcasecmp($firstName, $student->first_name);
                    if ($firstNameCmp < 0) {
                        // New student's first name comes before -> Insert here
                        break;
                    }

                    $position++;
                }
            } else {
                // Simple incremental numbering
                $position = $existingStudents->count() + 1;
            }

            // Intelligent Collision Resolution:
            // If the calculated position is already taken by an existing student ID,
            // increment the position until we find a free slot.
            $sequence = str_pad($position, 3, '0', STR_PAD_LEFT);
            $candidateId = "{$institutionPrefix}/{$programCode}/{$academicYear}/{$sequence}";

            // Use DB::table to bypass any model scopes and ensure raw check
            while (DB::table('students')->where('student_id', $candidateId)->exists()) {
                Log::warning('Collision detected during generation (Structured)', [
                    'candidate' => $candidateId,
                    'position_before' => $position,
                    'position_after' => $position + 1,
                ]);
                $position++;
                $sequence = str_pad($position, 3, '0', STR_PAD_LEFT);
                $candidateId = "{$institutionPrefix}/{$programCode}/{$academicYear}/{$sequence}";
            }

            return $sequence;

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

                foreach ($existingStudents as $student) {
                    // Compare Last Names first (to match SQL sort order)
                    $lastNameCmp = strcasecmp($lastName, $student->last_name);

                    if ($lastNameCmp < 0) {
                        // New student's last name comes before existing -> Insert here
                        break;
                    } elseif ($lastNameCmp > 0) {
                        // New student's last name comes after -> Continue
                        $position++;

                        continue;
                    }

                    // Last names are equal, compare First Names
                    $firstNameCmp = strcasecmp($firstName, $student->first_name);
                    if ($firstNameCmp < 0) {
                        // New student's first name comes before -> Insert here
                        break;
                    }

                    $position++;
                }
            } else {
                // Simple incremental numbering with lock
                $count = $existingStudents->count();
                $position = $count + 1;
            }

            // Intelligent Collision Resolution
            $sequence = str_pad($position, 4, '0', STR_PAD_LEFT);
            $candidateId = "{$institutionPrefix}{$programCode}{$academicYear}{$sequence}";

            // Use DB::table to bypass any model scopes and ensure raw check
            while (DB::table('students')->where('student_id', $candidateId)->exists()) {
                Log::warning('Collision detected during generation (Simple)', [
                    'candidate' => $candidateId,
                    'position_before' => $position,
                    'position_after' => $position + 1,
                ]);
                $position++;
                $sequence = str_pad($position, 4, '0', STR_PAD_LEFT);
                $candidateId = "{$institutionPrefix}{$programCode}{$academicYear}{$sequence}";
            }

            return $sequence;

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
     * NOTE: This is for informational/diagnostic purposes only.
     * Regeneration should ALWAYS process all students regardless of format validity.
     */
    public function isValidStudentIdFormat(string $studentId): bool
    {
        if (empty($studentId)) {
            return false;
        }

        // Check structured format: PREFIX/PROGRAM/YY/YY/NNN (e.g., COLLEGE/DEPT/GEN/25/26/001)
        $structuredPattern = '/^[A-Z\/]+\/[A-Z]+\/\d{2}\/\d{2}\/\d{3}$/';
        if (preg_match($structuredPattern, $studentId)) {
            return true;
        }

        // Check simple format: PREFIXPROGRAMYEARSEQUENCE (e.g., MHIARM240135)
        $simplePattern = '/^[A-Z]{4,6}[A-Z]{2,3}\d{6}$/';
        if (preg_match($simplePattern, $studentId)) {
            return true;
        }

        // Check legacy format: PREFIX/PROGRAMDIGITS (e.g., PNMTC/DA20254329)
        $legacyPattern = '/^[A-Z]+\/[A-Z]+\d{6,10}$/';
        if (preg_match($legacyPattern, $studentId)) {
            return true;
        }

        // Check old numeric format: PREFIXYYYYNNNN
        $oldPattern = '/^[A-Z]+\d{8}$/';

        return preg_match($oldPattern, $studentId);
    }

    /**
     * Re-generate student IDs for existing students in a specific cohort
     * This will re-sequence all students in the cohort
     */
    public function extractPreservedSequence(string $oldId, string $sourcePattern): int
    {
        $tokens = preg_split('/(\{[A-Z_0-9]+\})/', $sourcePattern, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        $regex = '';
        $count = 0;
        foreach ($tokens as $token) {
            if (in_array($token, ['{SEQUENCE_3}', '{SEQUENCE_4}'], true)) {
                $count++;
                $regex .= $token === '{SEQUENCE_3}' ? '([0-9]{3,})' : '([0-9]{4,})';
            } elseif (in_array($token, ['{INSTITUTION}', '{INSTITUTION_SIMPLE}', '{PROGRAM}', '{PROGRAM_SIMPLE}', '{YEAR_FULL}', '{YEAR_SIMPLE}', '{FIRST_NAME}', '{LAST_NAME}'], true)) {
                $regex .= '.+?';
            } else {
                $regex .= preg_quote($token, '~');
            }
        }
        // Require a literal separator before the sequence, or a fixed-width suffix.
        // Reject ambiguous adjacent variable-width fields rather than guessing.
        if ($count !== 1 || preg_match('/\}\{SEQUENCE_[34]\}|\{SEQUENCE_[34]\}\{/', $sourcePattern)
            || ! preg_match('~^'.$regex.'$~D', $oldId, $matches)
            || (int) $matches[1] < 1 || strlen(ltrim($matches[1], '0')) > 9) {
            throw new \InvalidArgumentException('Cannot safely extract sequence from the original ID and source pattern.');
        }
        return (int) $matches[1];
    }

    private function buildIdWithSequence(Student $student, int $sequence): string
    {
        $format = config('branding.student_id.format', 'structured');
        $class = $student->college_class_id;
        $year = $student->academic_year_id;
        if ($format === 'custom') {
            $pattern = (string) config('branding.student_id.custom_pattern');
            if (preg_match_all('/\{SEQUENCE_[34]\}/', $pattern) !== 1) {
                throw new \InvalidArgumentException('Target custom pattern must contain exactly one sequence token.');
            }
            return $this->generateCustomStudentId($student->first_name ?? '', $student->last_name ?? '', $class, $year, $sequence);
        }
        if ($format === 'simple') {
            return $this->getSimpleInstitutionPrefix().$this->getSimpleProgramCode($class).$this->getSimpleAcademicYear($year).str_pad($sequence, 4, '0', STR_PAD_LEFT);
        }
        return $this->getInstitutionPrefix().'/'.$this->getProgramCode($class).'/'.$this->getAcademicYearCode($year).'/'.str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }

    public function regenerateStudentIdsForCohort(int $cohortId, ?int $initiatedBy = null, string $mode = 'resequence', ?string $sourcePattern = null, bool $preview = false, ?string $expectedHash = null, ?string $operationId = null): array
    {
        $results = ['success' => 0, 'errors' => 0, 'updated_students' => [], 'failed_students' => []];
        $total = 0;
        $student = null;

        try {
            return DB::transaction(function () use ($cohortId, $initiatedBy, $mode, $sourcePattern, $preview, $expectedHash, $operationId, $results, &$total, &$student) {
                \App\Models\Cohort::query()->lockForUpdate()->findOrFail($cohortId);
                $students = Student::where('cohort_id', $cohortId)
                    ->orderBy('last_name')->orderBy('first_name')->orderBy('id')
                    ->lockForUpdate()->get();
                $total = $students->count();
                $originalIds = $students->pluck('student_id', 'id');

                if (! in_array($mode, ['resequence', 'preserve_sequence'], true)) {
                    throw new \InvalidArgumentException('Unknown regeneration mode.');
                }
                $sequences = [];
                if ($mode === 'preserve_sequence') {
                    foreach ($students as $student) {
                        $sequences[$student->id] = $this->extractPreservedSequence((string) $student->student_id, (string) $sourcePattern);
                    }
                }
                foreach ($students as $student) {
                    $student->student_id = null;
                    $student->saveQuietly();
                }

                foreach ($students as $student) {
                    $oldId = $originalIds[$student->id];
                    $newId = $mode === 'preserve_sequence'
                        ? $this->buildIdWithSequence($student, $sequences[$student->id])
                        : $this->generateStudentId(
                        $student->first_name ?? '', $student->last_name ?? '',
                        $student->college_class_id, $student->academic_year_id, false
                    );
                    if (trim($newId) === '' || str_contains($newId, '{') || ! $this->isStudentIdUnique($newId)) {
                        throw new \RuntimeException('Invalid or duplicate generated student ID.');
                    }
                    $student->student_id = $newId;
                    $student->saveQuietly();
                    if (! $preview && $oldId !== $newId) {
                        DB::table('student_id_changes')->where('student_id', $student->id)
                            ->where('status', 'active')->update(['status' => 'superseded', 'updated_at' => now()]);
                        DB::table('student_id_changes')->insert([
                            'student_id' => $student->id, 'old_student_id' => $oldId ?? '',
                            'new_student_id' => $newId, 'changed_by' => $initiatedBy ?? auth()->id(),
                            'status' => 'active', 'notes' => $oldId === null ? 'Original ID was null' : 'Cohort regeneration',
                            'created_at' => now(), 'updated_at' => now(),
                        ]);
                    }
                    $results['updated_students'][] = ['id' => $student->id, 'name' => trim($student->first_name.' '.$student->last_name), 'old_id' => $oldId, 'new_id' => $newId];
                    $results['success']++;
                }

                app(StudentIdReferenceUpdater::class)->apply($results['updated_students']);

                $results['hash'] = hash('sha256', json_encode([$cohortId, $mode, $sourcePattern, config('branding.student_id'), $results['updated_students']], JSON_THROW_ON_ERROR));
                if ($expectedHash !== null && ! hash_equals($expectedHash, $results['hash'])) {
                    throw new \RuntimeException('The preview is stale. Generate a new preview before confirming.');
                }
                if ($preview) {
                    throw new StudentIdPreviewRollback($results);
                }
                $results['batch_id'] = app(StudentIdRegenerationHistory::class)->record($cohortId, $initiatedBy ?? auth()->id(), $mode, $sourcePattern, $results['updated_students']);
                if ($operationId !== null) {
                    app(StudentIdRegenerationOperations::class)->complete($operationId, $results['batch_id'], $results['success']);
                }
                return $results;
            });
        } catch (StudentIdPreviewRollback $e) {
            return $e->results;
        } catch (\Throwable $e) {
            Log::error('Cohort student ID regeneration rolled back', ['cohort_id' => $cohortId, 'error' => $e->getMessage()]);
            $results['errors'] = max(1, $total);
            $results['message'] = $e->getMessage();
            if ($student) {
                $results['failed_students'][] = ['id' => $student->id, 'error' => $e->getMessage()];
            }
            return $results;
        }
    }
}
