<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StudentIdReassignmentService
{
    protected StudentIdGenerationService $idGenerationService;

    public function __construct()
    {
        $this->idGenerationService = new StudentIdGenerationService;
    }

    /**
     * Parse existing student ID to extract components
     * Supports multiple formats: simple (MHIAFRGN220003) and structured (MHIAF/RGN/22/23/003)
     */
    public function parseStudentId(string $studentId): array
    {
        // Try structured format first: PREFIX/PROGRAM/YY/YY/SEQ or PREFIX/PROGRAM/YY/SEQ
        if (str_contains($studentId, '/')) {
            return $this->parseStructuredId($studentId);
        }

        // Try simple format: PREFIXPROGRAMYYSEQ (e.g., MHIAFRGN220003)
        return $this->parseSimpleId($studentId);
    }

    /**
     * Parse structured format: MHIAF/RGN/22/23/003
     */
    private function parseStructuredId(string $studentId): array
    {
        $parts = explode('/', $studentId);
        $count = count($parts);

        if ($count === 5) {
            // Format: INSTITUTION/PROGRAM/START_YY/END_YY/SEQUENCE
            return [
                'format' => 'structured',
                'institution' => $parts[0],
                'program' => $parts[1],
                'year_start' => $parts[2],
                'year_end' => $parts[3],
                'sequence' => $parts[4],
                'original' => $studentId,
            ];
        } elseif ($count === 4) {
            // Format: INSTITUTION/PROGRAM/YY/SEQUENCE
            return [
                'format' => 'structured_simple',
                'institution' => $parts[0],
                'program' => $parts[1],
                'year' => $parts[2],
                'sequence' => $parts[3],
                'original' => $studentId,
            ];
        }

        return [
            'format' => 'unknown',
            'original' => $studentId,
        ];
    }

    /**
     * Parse simple format: MHIAFRGN220003
     * Pattern: [INSTITUTION 4-6 chars][PROGRAM 2-3 chars][YEAR 2 chars][SEQUENCE 4 chars]
     */
    private function parseSimpleId(string $studentId): array
    {
        // Common patterns for nursing colleges
        $programCodes = ['RGN', 'RM', 'CHN', 'PN', 'GEN'];

        foreach ($programCodes as $code) {
            $pos = strpos($studentId, $code);
            if ($pos !== false) {
                $institution = substr($studentId, 0, $pos);
                $programStart = $pos;
                $programLength = strlen($code);

                // After program code, expect 2-digit year + 4-digit sequence
                $afterProgram = substr($studentId, $programStart + $programLength);

                if (strlen($afterProgram) >= 6) {
                    $year = substr($afterProgram, 0, 2);
                    $sequence = substr($afterProgram, 2, 4);

                    return [
                        'format' => 'simple',
                        'institution' => $institution,
                        'program' => $code,
                        'year' => $year,
                        'sequence' => $sequence,
                        'original' => $studentId,
                    ];
                }
            }
        }

        // Fallback: Try generic pattern (last 6 chars are year + sequence)
        if (strlen($studentId) >= 10) {
            $mainPart = substr($studentId, 0, -6);
            $yearAndSeq = substr($studentId, -6);
            $year = substr($yearAndSeq, 0, 2);
            $sequence = substr($yearAndSeq, 2);

            // Try to determine program code from main part
            $programGuess = 'GEN';
            foreach ($programCodes as $code) {
                if (str_contains($mainPart, $code)) {
                    $programGuess = $code;
                    break;
                }
            }

            return [
                'format' => 'simple_generic',
                'institution' => str_replace($programGuess, '', $mainPart),
                'program' => $programGuess,
                'year' => $year,
                'sequence' => $sequence,
                'original' => $studentId,
            ];
        }

        return [
            'format' => 'unknown',
            'original' => $studentId,
        ];
    }

    /**
     * Convert old format ID to new format based on configuration
     */
    public function reassignStudentId(Student $student, ?string $targetFormat = null, ?string $customPattern = null): array
    {
        $oldId = $student->student_id;
        $parsedId = $this->parseStudentId($oldId);

        // Determine target format
        $format = $targetFormat ?? config('branding.student_id.format', 'structured');
        $pattern = $customPattern ?? config('branding.student_id.custom_pattern');

        try {
            DB::beginTransaction();

            // Generate new ID using existing service
            $newId = $this->idGenerationService->generateStudentId(
                $student->first_name,
                $student->last_name,
                $student->college_class_id,
                $this->getStudentAcademicYearId($student, $parsedId)
            );

            // Store backup in case we need to revert
            $this->storeIdBackup($student->id, $oldId, $newId);

            // Update student ID
            $student->student_id = $newId;
            $student->save();

            DB::commit();

            Log::info('Student ID reassigned successfully', [
                'student_id' => $student->id,
                'student_name' => "{$student->first_name} {$student->last_name}",
                'old_id' => $oldId,
                'new_id' => $newId,
                'parsed_old_format' => $parsedId['format'],
                'target_format' => $format,
            ]);

            return [
                'success' => true,
                'student' => $student,
                'old_id' => $oldId,
                'new_id' => $newId,
                'parsed_data' => $parsedId,
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error reassigning student ID', [
                'student_id' => $student->id,
                'old_id' => $oldId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'old_id' => $oldId,
            ];
        }
    }

    /**
     * Batch reassign student IDs with filtering options
     */
    public function batchReassignStudentIds(array $options = []): array
    {
        $filters = $options['filters'] ?? [];
        $targetFormat = $options['target_format'] ?? null;
        $customPattern = $options['custom_pattern'] ?? null;
        $dryRun = $options['dry_run'] ?? false;

        $query = Student::query();

        // Apply filters
        if (isset($filters['program_id'])) {
            $query->where('college_class_id', $filters['program_id']);
        }

        if (isset($filters['cohort_id'])) {
            $query->where('cohort_id', $filters['cohort_id']);
        }

        if (isset($filters['year'])) {
            // Filter by academic year in student ID
            $query->where('student_id', 'like', '%'.$filters['year'].'%');
        }

        if (isset($filters['format'])) {
            // Filter by current ID format
            switch ($filters['format']) {
                case 'simple':
                    $query->whereRaw("student_id NOT LIKE '%/%'");
                    break;
                case 'structured':
                    $query->whereRaw("student_id LIKE '%/%'");
                    break;
            }
        }

        // Order alphabetically for proper sequence assignment
        $students = $query->orderBy('last_name', 'asc')
            ->orderBy('first_name', 'asc')
            ->get();

        $results = [
            'total' => $students->count(),
            'processed' => 0,
            'successful' => 0,
            'failed' => 0,
            'skipped' => 0,
            'dry_run' => $dryRun,
            'updates' => [],
            'errors' => [],
        ];

        foreach ($students as $student) {
            $results['processed']++;

            if ($dryRun) {
                // Preview mode: show what would be changed
                $parsedId = $this->parseStudentId($student->student_id);
                $results['updates'][] = [
                    'id' => $student->id,
                    'name' => "{$student->first_name} {$student->last_name}",
                    'current_id' => $student->student_id,
                    'parsed_format' => $parsedId['format'],
                    'would_change' => true,
                ];
                $results['successful']++;
            } else {
                // Actual reassignment
                $result = $this->reassignStudentId($student, $targetFormat, $customPattern);

                if ($result['success']) {
                    $results['successful']++;
                    $results['updates'][] = [
                        'id' => $student->id,
                        'name' => "{$student->first_name} {$student->last_name}",
                        'old_id' => $result['old_id'],
                        'new_id' => $result['new_id'],
                    ];
                } else {
                    $results['failed']++;
                    $results['errors'][] = [
                        'id' => $student->id,
                        'name' => "{$student->first_name} {$student->last_name}",
                        'old_id' => $student->student_id,
                        'error' => $result['error'],
                    ];
                }
            }
        }

        return $results;
    }

    /**
     * Revert student ID changes back to original
     */
    public function revertStudentId(Student $student): array
    {
        try {
            $backup = $this->getIdBackup($student->id);

            if (! $backup) {
                return [
                    'success' => false,
                    'error' => 'No backup found for this student',
                ];
            }

            DB::beginTransaction();

            $currentId = $student->student_id;
            $student->student_id = $backup['old_id'];
            $student->save();

            // Keep the backup record for audit trail
            $this->updateBackupStatus($student->id, 'reverted');

            DB::commit();

            Log::info('Student ID reverted successfully', [
                'student_id' => $student->id,
                'student_name' => "{$student->first_name} {$student->last_name}",
                'reverted_from' => $currentId,
                'reverted_to' => $backup['old_id'],
            ]);

            return [
                'success' => true,
                'student' => $student,
                'reverted_from' => $currentId,
                'reverted_to' => $backup['old_id'],
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error reverting student ID', [
                'student_id' => $student->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Batch revert student ID changes
     */
    public function batchRevertStudentIds(array $studentIds = []): array
    {
        $results = [
            'total' => count($studentIds),
            'successful' => 0,
            'failed' => 0,
            'reverts' => [],
            'errors' => [],
        ];

        foreach ($studentIds as $studentId) {
            $student = Student::find($studentId);

            if (! $student) {
                $results['failed']++;
                $results['errors'][] = [
                    'student_id' => $studentId,
                    'error' => 'Student not found',
                ];

                continue;
            }

            $result = $this->revertStudentId($student);

            if ($result['success']) {
                $results['successful']++;
                $results['reverts'][] = [
                    'id' => $student->id,
                    'name' => "{$student->first_name} {$student->last_name}",
                    'reverted_from' => $result['reverted_from'],
                    'reverted_to' => $result['reverted_to'],
                ];
            } else {
                $results['failed']++;
                $results['errors'][] = [
                    'id' => $student->id,
                    'name' => "{$student->first_name} {$student->last_name}",
                    'error' => $result['error'],
                ];
            }
        }

        return $results;
    }

    /**
     * Preview what changes would be made without actually making them
     */
    public function previewReassignment(array $filters = []): array
    {
        return $this->batchReassignStudentIds([
            'filters' => $filters,
            'dry_run' => true,
        ]);
    }

    /**
     * Store backup of student ID change
     */
    private function storeIdBackup(int $studentId, string $oldId, string $newId): void
    {
        DB::table('student_id_changes')->insert([
            'student_id' => $studentId,
            'old_student_id' => $oldId,
            'new_student_id' => $newId,
            'changed_by' => auth()->id(),
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Get backup record for student
     */
    private function getIdBackup(int $studentId): ?array
    {
        $backup = DB::table('student_id_changes')
            ->where('student_id', $studentId)
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->first();

        return $backup ? (array) $backup : null;
    }

    /**
     * Update backup status
     */
    private function updateBackupStatus(int $studentId, string $status): void
    {
        DB::table('student_id_changes')
            ->where('student_id', $studentId)
            ->where('status', 'active')
            ->update([
                'status' => $status,
                'updated_at' => now(),
            ]);
    }

    /**
     * Get academic year ID for student based on their current ID
     */
    private function getStudentAcademicYearId(Student $student, array $parsedId): ?int
    {
        // Try to find academic year from parsed ID
        if (isset($parsedId['year'])) {
            $year = $parsedId['year'];

            // Try to find matching academic year
            $academicYear = AcademicYear::where(function ($query) use ($year) {
                $query->whereRaw("DATE_FORMAT(start_date, '%y') = ?", [$year])
                    ->orWhereRaw("DATE_FORMAT(end_date, '%y') = ?", [$year]);
            })->first();

            if ($academicYear) {
                return $academicYear->id;
            }
        }

        // Fallback to student's existing academic year or current one
        return $student->academic_year_id ?? AcademicYear::where('is_current', true)->first()?->id;
    }

    /**
     * Get statistics about student ID formats in the system
     */
    public function getIdFormatStatistics(): array
    {
        $students = Student::all();

        $stats = [
            'total_students' => $students->count(),
            'by_format' => [
                'simple' => 0,
                'structured' => 0,
                'unknown' => 0,
            ],
            'sample_ids' => [
                'simple' => [],
                'structured' => [],
                'unknown' => [],
            ],
        ];

        foreach ($students as $student) {
            $parsed = $this->parseStudentId($student->student_id);
            $format = str_contains($parsed['format'], 'simple') ? 'simple' :
                     (str_contains($parsed['format'], 'structured') ? 'structured' : 'unknown');

            $stats['by_format'][$format]++;

            if (count($stats['sample_ids'][$format]) < 5) {
                $stats['sample_ids'][$format][] = $student->student_id;
            }
        }

        return $stats;
    }

    /**
     * Validate reassignment is safe before executing
     */
    public function validateReassignment(array $filters = []): array
    {
        $validation = [
            'is_safe' => true,
            'warnings' => [],
            'errors' => [],
            'affected_count' => 0,
        ];

        $query = Student::query();

        // Apply same filters as batch reassignment
        if (isset($filters['program_id'])) {
            $query->where('college_class_id', $filters['program_id']);
        }

        if (isset($filters['cohort_id'])) {
            $query->where('cohort_id', $filters['cohort_id']);
        }

        $validation['affected_count'] = $query->count();

        // Check if students have dependent records
        $studentsWithDependencies = $query->whereHas('courseRegistrations')
            ->orWhereHas('examClearances')
            ->orWhereHas('feePayments')
            ->count();

        if ($studentsWithDependencies > 0) {
            $validation['warnings'][] = "{$studentsWithDependencies} students have dependent records (course registrations, exam clearances, payments). IDs will be updated but please verify related systems.";
        }

        return $validation;
    }
}
