<?php

namespace App\Imports;

use App\Models\Student;
use App\Services\StudentIdGenerationService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class StudentImporter implements ToCollection, WithHeadingRow, WithValidation, WithBatchInserts, WithChunkReading
{
    protected $programId;
    protected $cohortId;
    protected $columnMapping;
    protected $academicYearId;
    protected $studentIdService;
    protected $importStats = [
        'total' => 0,
        'created' => 0,
        'updated' => 0,
        'failed' => 0,
        'skipped' => 0,
        'ids_generated' => 0,
        'validation_errors' => [],
        'processed' => 0,
    ];

    /**
     * Constructor with mapping configuration
     * 
     * @param int $programId The program ID to assign to imported students
     * @param int $cohortId The cohort ID to assign to imported students  
     * @param array $columnMapping Custom column mapping if provided
     * @param int|null $academicYearId The academic year ID for student ID generation
     */
    public function __construct($programId, $cohortId, $columnMapping = [], $academicYearId = null)
    {
        $this->programId = $programId;
        $this->cohortId = $cohortId;
        $this->academicYearId = $academicYearId;
        $this->studentIdService = new StudentIdGenerationService();
        
        // Default column mapping (Excel column => Database field)
        $defaultMapping = [
            'student_id' => 'student_id',
            'first_name' => 'first_name',
            'last_name' => 'last_name',
            'other_names' => 'other_name', 
            'gender' => 'gender',
            'date_of_birth' => 'date_of_birth',
            'nationality' => 'nationality',
            'country' => 'country_of_residence',
            'home_region' => 'home_region',
            'home_town' => 'home_town',
            'region' => null, // Not mapped by default
            'mobile_number' => 'mobile_number',
            'email' => 'email',
            'gps_address' => 'gps_address',
            'postal_address' => 'postal_address',
            'residential_address' => 'residential_address',
            'marital_status' => 'marital_status',
        ];
        
        // Merge provided mapping with default mapping
        $this->columnMapping = array_merge($defaultMapping, $columnMapping);
    }

    /**
     * @param Collection $rows
     */
    public function collection(Collection $rows)
    {
        $this->importStats['total'] = count($rows);
        
        foreach ($rows as $rowIndex => $row) {
            try {
                // Prepare student data from mapped columns
                $studentData = $this->mapRowToStudentData($row);
                
                // Validate required fields before processing
                $validationResult = $this->validateStudentData($studentData, $rowIndex + 2); // +2 for header and 0-based index
                
                if (!$validationResult['valid']) {
                    $this->importStats['skipped']++;
                    $this->importStats['validation_errors'][] = [
                        'row' => $rowIndex + 2,
                        'errors' => $validationResult['errors'],
                        'data' => array_filter($studentData) // Only show non-empty fields
                    ];
                    Log::warning('Student record skipped due to validation errors', [
                        'row' => $rowIndex + 2,
                        'errors' => $validationResult['errors'],
                        'data' => $studentData
                    ]);
                    continue; // Skip this record
                }
                
                $this->importStats['processed']++;
                
                // Add program and cohort data
                $studentData['college_class_id'] = $this->programId;
                $studentData['cohort_id'] = $this->cohortId;
                $studentData['status'] = $studentData['status'] ?? 'active'; // Default status
                
                // Generate student ID if not provided
                if (empty($studentData['student_id']) && 
                    !empty($studentData['first_name']) && 
                    !empty($studentData['last_name'])) {
                    
                    try {
                        $studentData['student_id'] = $this->studentIdService->generateStudentId(
                            $studentData['first_name'],
                            $studentData['last_name'],
                            $this->programId,
                            $this->academicYearId
                        );
                        $this->importStats['ids_generated']++;
                        
                        Log::info('Generated student ID during import', [
                            'student_name' => $studentData['first_name'] . ' ' . $studentData['last_name'],
                            'generated_id' => $studentData['student_id'],
                            'program_id' => $this->programId,
                            'academic_year_id' => $this->academicYearId
                        ]);
                        
                    } catch (\Exception $e) {
                        Log::error('Failed to generate student ID during import', [
                            'student_name' => $studentData['first_name'] . ' ' . $studentData['last_name'],
                            'program_id' => $this->programId,
                            'academic_year_id' => $this->academicYearId,
                            'error' => $e->getMessage()
                        ]);
                        // Skip if we can't generate ID and none provided
                        $this->importStats['skipped']++;
                        continue;
                    }
                }
                
                // Check if student exists (by student ID or email)
                $existingStudent = null;
                if (!empty($studentData['student_id'])) {
                    $existingStudent = Student::where('student_id', $studentData['student_id'])->first();
                }
                
                if (!$existingStudent && !empty($studentData['email'])) {
                    $existingStudent = Student::where('email', $studentData['email'])->first();
                }
                
                if ($existingStudent) {
                    // Update existing student with non-empty values only
                    $updateData = array_filter($studentData, function($value) {
                        return !is_null($value) && $value !== '';
                    });
                    $existingStudent->update($updateData);
                    $this->importStats['updated']++;
                    
                    Log::info('Updated existing student during import', [
                        'student_id' => $existingStudent->student_id,
                        'updated_fields' => array_keys($updateData)
                    ]);
                } else {
                    // Create new student
                    $newStudent = Student::create($studentData);
                    $this->importStats['created']++;
                    
                    Log::info('Created new student during import', [
                        'student_id' => $newStudent->student_id,
                        'student_name' => $newStudent->name
                    ]);
                }
            } catch (\Exception $e) {
                $this->importStats['failed']++;
                Log::error("Student import error: " . $e->getMessage(), [
                    'row' => $rowIndex + 2,
                    'row_data' => json_encode($row),
                    'exception' => $e->getTraceAsString()
                ]);
            }
        }
    }

    /**
     * Map Excel row data to student model fields
     *
     * @param Collection $row
     * @return array
     */
    protected function mapRowToStudentData($row)
    {
        $studentData = [];
        
        foreach ($this->columnMapping as $excelColumn => $dbField) {
            // Skip unmapped fields
            if (is_null($dbField) || $dbField === '') {
                continue;
            }
            
            // Convert keys to lowercase and remove spaces
            $normalizedExcelColumn = strtolower(str_replace(' ', '_', $excelColumn));
            
            // Map the column if it exists in the row
            if (isset($row[$normalizedExcelColumn])) {
                $value = $row[$normalizedExcelColumn];
                
                // Clean up the value
                if (is_string($value)) {
                    $value = trim($value);
                    // Convert empty strings to null
                    $value = $value === '' ? null : $value;
                }
                
                $studentData[$dbField] = $value;
            }
        }
        
        return $studentData;
    }
    
    /**
     * Validate student data before import
     *
     * @param array $studentData
     * @param int $rowNumber
     * @return array
     */
    protected function validateStudentData($studentData, $rowNumber)
    {
        $errors = [];
        $valid = true;
        
        // Check required fields
        $requiredFields = ['first_name', 'last_name'];
        
        foreach ($requiredFields as $field) {
            if (empty($studentData[$field])) {
                $errors[] = "Missing required field: " . str_replace('_', ' ', $field);
                $valid = false;
            }
        }
        
        // Validate email if provided
        if (!empty($studentData['email']) && !filter_var($studentData['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format: " . $studentData['email'];
            $valid = false;
        }
        
        // Validate date of birth if provided
        if (!empty($studentData['date_of_birth'])) {
            $dob = $studentData['date_of_birth'];
            if (!strtotime($dob)) {
                $errors[] = "Invalid date of birth format: " . $dob;
                $valid = false;
            }
        }
        
        // Validate gender if provided
        if (!empty($studentData['gender'])) {
            $validGenders = ['male', 'female', 'M', 'F', 'Male', 'Female'];
            if (!in_array($studentData['gender'], $validGenders)) {
                $errors[] = "Invalid gender value: " . $studentData['gender'];
                $valid = false;
            }
        }
        
        // Log validation issues
        if (!$valid) {
            Log::warning("Row $rowNumber validation failed", [
                'errors' => $errors,
                'data' => $studentData
            ]);
        }
        
        return [
            'valid' => $valid,
            'errors' => $errors
        ];
    }

    /**
     * Get import statistics
     *
     * @return array
     */
    public function getImportStats()
    {
        return $this->importStats;
    }

    /**
     * Validation rules
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            '*.email' => 'nullable|email',
            '*.student_id' => 'nullable|string',
        ];
    }

    /**
     * Import data in batches
     *
     * @return int
     */
    public function batchSize(): int
    {
        return 100;
    }

    /**
     * Read data in chunks
     *
     * @return int
     */
    public function chunkSize(): int
    {
        return 100;
    }
}