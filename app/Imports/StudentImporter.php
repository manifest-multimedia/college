<?php

namespace App\Imports;

use App\Models\Student;
use Illuminate\Support\Collection;
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
    protected $importStats = [
        'total' => 0,
        'created' => 0,
        'updated' => 0,
        'failed' => 0,
        'skipped' => 0,
    ];

    /**
     * Constructor with mapping configuration
     * 
     * @param int $programId The program ID to assign to imported students
     * @param int $cohortId The cohort ID to assign to imported students  
     * @param array $columnMapping Custom column mapping if provided
     */
    public function __construct($programId, $cohortId, $columnMapping = [])
    {
        $this->programId = $programId;
        $this->cohortId = $cohortId;
        
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
        
        foreach ($rows as $row) {
            try {
                // Prepare student data from mapped columns
                $studentData = $this->mapRowToStudentData($row);
                
                // Add program and cohort data
                $studentData['college_class_id'] = $this->programId;
                $studentData['cohort_id'] = $this->cohortId;
                
                // Check if student exists (by student ID or email)
                $existingStudent = null;
                if (!empty($studentData['student_id'])) {
                    $existingStudent = Student::where('student_id', $studentData['student_id'])->first();
                }
                
                if (!$existingStudent && !empty($studentData['email'])) {
                    $existingStudent = Student::where('email', $studentData['email'])->first();
                }
                
                if ($existingStudent) {
                    // Update existing student
                    $existingStudent->update($studentData);
                    $this->importStats['updated']++;
                } else {
                    // Create new student
                    Student::create($studentData);
                    $this->importStats['created']++;
                }
            } catch (\Exception $e) {
                $this->importStats['failed']++;
                \Log::error("Student import error: " . $e->getMessage(), [
                    'row' => json_encode($row)
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
                $studentData[$dbField] = $row[$normalizedExcelColumn];
            }
        }
        
        return $studentData;
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