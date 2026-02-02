<?php

namespace App\Imports;

use App\Models\AssessmentScore;
use App\Models\Student;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithStartRow;

class AssessmentScoresImport implements ToCollection, WithChunkReading, WithStartRow
{
    protected $courseId;

    protected $cohortId;

    protected $semesterId;

    protected $recordedBy;

    protected $errors = [];

    protected $validatedData = [];

    protected $processedRows = 0;

    protected $summary = [
        'total_records' => 0,
        'valid' => 0,
        'updated_records' => 0,
        'new_records' => 0,
        'errors' => 0,
    ];

    public function __construct($courseId, $cohortId, $semesterId, $recordedBy)
    {
        $this->courseId = $courseId;
        $this->cohortId = $cohortId;
        $this->semesterId = $semesterId;
        $this->recordedBy = $recordedBy;
    }

    /**
     * Process Excel in chunks for memory efficiency with large files
     */
    public function chunkSize(): int
    {
        return 100; // Process 100 rows at a time
    }

    /**
     * Specify which row to start reading data from (skip header rows and placeholder)
     */
    public function startRow(): int
    {
        return 7; // Start reading data from row 7 (skip header rows 1-4, heading row 5, and placeholder row 6)
    }

    public function collection(Collection $rows)
    {
        $currentChunkRows = $rows->count();
        $this->summary['total_records'] += $currentChunkRows;

        \Log::info('Excel Import Chunk Started', [
            'chunk_rows' => $currentChunkRows,
            'total_processed' => $this->processedRows,
            'starting_row' => 7,
        ]);

        foreach ($rows as $index => $row) {
            $actualRowNumber = $this->processedRows + $index + 7; // Adjust for chunk processing

            try {
                $this->processRow($row, $index, $actualRowNumber);
            } catch (\Exception $e) {
                $this->errors[] = "Row {$actualRowNumber}: Processing failed - ".$e->getMessage();
                $this->summary['errors']++;

                \Log::error('Excel Import - Row processing failed', [
                    'row_number' => $actualRowNumber,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->processedRows += $currentChunkRows;

        \Log::info('Excel Import Chunk Completed', [
            'chunk_rows' => $currentChunkRows,
            'total_processed' => $this->processedRows,
            'chunk_valid' => $this->summary['valid'],
            'chunk_errors' => $this->summary['errors'],
        ]);
    }

    private function processRow($row, $index, $actualRowNumber)
    {
        // Debug: Log the first few rows to see what we're getting
        if ($this->processedRows + $index < 3) {
            \Log::info('Excel Import - Row details', [
                'row_number' => $actualRowNumber,
                'row_data' => $row instanceof Collection ? $row->toArray() : (array) $row,
            ]);
        }

        // Map columns by position (0-based index):
        // Column A (0) = S/N
        // Column B (1) = INDEX NO
        // Column C (2) = STUDENT NAME
        // Column D (3) = ASSIGNMENT 1
        // Column E (4) = ASSIGNMENT 2
        // Column F (5) = ASSIGNMENT 3
        // Column G (6) = MID-SEM
        // Column H (7) = END-SEM

        // Convert row to array if it's a Collection
        $rowData = $row instanceof Collection ? $row->toArray() : (array) $row;

        $indexNo = isset($rowData[1]) ? trim($rowData[1]) : null;
        $assignment1 = $rowData[3] ?? null;
        $assignment2 = $rowData[4] ?? null;
        $assignment3 = $rowData[5] ?? null;
        $midSem = $rowData[6] ?? null;
        $endSem = $rowData[7] ?? null;

        // Skip empty rows (no INDEX NO)
        if (empty($indexNo)) {
            \Log::info('Excel Import - Skipped empty row', ['row_number' => $actualRowNumber]);

            return;
        }

        // Skip rows with no scores at all
        $hasScores = ($assignment1 !== null && $assignment1 !== '') ||
                    ($assignment2 !== null && $assignment2 !== '') ||
                    ($assignment3 !== null && $assignment3 !== '') ||
                    ($midSem !== null && $midSem !== '') ||
                    ($endSem !== null && $endSem !== '');

        if (! $hasScores) {
            \Log::info('Excel Import - Skipped row with no scores', [
                'row_number' => $actualRowNumber,
                'index_no' => $indexNo,
            ]);

            return; // Skip this row - student has no scores
        }

        // Find student with caching for better performance
        $student = Student::where('student_id', $indexNo)->first();
        if (! $student) {
            $this->errors[] = "Row {$actualRowNumber}: Student with INDEX NO '{$indexNo}' not found";
            $this->summary['errors']++;
            \Log::warning('Excel Import - Student not found', [
                'row_number' => $actualRowNumber,
                'index_no' => $indexNo,
            ]);

            return;
        }

        // Validate scores
        $validator = Validator::make([
            'assignment_1' => $assignment1,
            'assignment_2' => $assignment2,
            'assignment_3' => $assignment3,
            'mid_sem' => $midSem,
            'end_sem' => $endSem,
        ], [
            'assignment_1' => 'nullable|numeric|min:0|max:100',
            'assignment_2' => 'nullable|numeric|min:0|max:100',
            'assignment_3' => 'nullable|numeric|min:0|max:100',
            'mid_sem' => 'nullable|numeric|min:0|max:100',
            'end_sem' => 'nullable|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->errors[] = "Row {$actualRowNumber}: {$error}";
            }
            $this->summary['errors']++;

            return;
        }

        // Check if record exists
        $existing = AssessmentScore::where([
            'course_id' => $this->courseId,
            'student_id' => $student->id,
            'cohort_id' => $this->cohortId,
            'semester_id' => $this->semesterId,
        ])->first();

        $this->validatedData[] = [
            'row_number' => $actualRowNumber,
            'student_id' => $student->id,
            'student_index_number' => $student->student_id,
            'student_name' => $student->name,
            'assignment_1' => $assignment1 !== '' && $assignment1 !== null ? $assignment1 : null,
            'assignment_2' => $assignment2 !== '' && $assignment2 !== null ? $assignment2 : null,
            'assignment_3' => $assignment3 !== '' && $assignment3 !== null ? $assignment3 : null,
            'assignment_4' => null,  // Optional - template doesn't include this column
            'assignment_5' => null,  // Optional - template doesn't include this column
            'mid_semester' => $midSem !== '' && $midSem !== null ? $midSem : null,
            'end_semester' => $endSem !== '' && $endSem !== null ? $endSem : null,
            'action' => $existing !== null ? 'update' : 'create',
            'existing_id' => $existing?->id,
        ];

        if ($existing) {
            $this->summary['updated_records']++;
        } else {
            $this->summary['new_records']++;
        }

        $this->summary['valid']++;
    }

    public function getValidatedData()
    {
        return $this->validatedData;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getSummary()
    {
        return $this->summary;
    }

    public function hasErrors()
    {
        return count($this->errors) > 0;
    }
}
