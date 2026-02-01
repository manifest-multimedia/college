<?php

namespace App\Imports;

use App\Models\AssessmentScore;
use App\Models\Student;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;

class AssessmentScoresImport implements ToCollection, WithHeadingRow, WithStartRow
{
    protected $courseId;

    protected $cohortId;

    protected $semesterId;

    protected $recordedBy;

    protected $errors = [];

    protected $validatedData = [];

    protected $summary = [
        'total' => 0,
        'valid' => 0,
        'updates' => 0,
        'new' => 0,
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
     * Specify which row contains the headings
     */
    public function headingRow(): int
    {
        return 5; // The actual column headers are on row 5
    }

    /**
     * Specify which row to start reading data from (skip placeholder row 6)
     */
    public function startRow(): int
    {
        return 7; // Start reading data from row 7 (skip header rows 1-4, heading row 5, and placeholder row 6)
    }

    public function collection(Collection $rows)
    {
        $this->summary['total'] = $rows->count();

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 7; // +7 because data starts at row 7

            // Debug: Check what keys are available in the first row
            if ($index === 0) {
                $availableKeys = array_keys($row->toArray());
                \Log::info('Excel Import - Available column keys:', $availableKeys);
            }

            // Try multiple possible key variations for INDEX NO
            $indexNo = $row['index_no'] ?? $row['indexno'] ?? $row['index_number'] ?? $row['sn'] ?? null;

            // Validate required fields
            if (empty($indexNo)) {
                $availableKeys = implode(', ', array_keys($row->toArray()));
                $this->errors[] = "Row {$rowNumber}: INDEX NO is required. Available columns: {$availableKeys}";
                $this->summary['errors']++;

                continue;
            }

            // Find student
            $student = Student::where('student_id', $indexNo)->first();
            if (! $student) {
                $this->errors[] = "Row {$rowNumber}: Student with INDEX NO '{$indexNo}' not found";
                $this->summary['errors']++;

                continue;
            }

            // Validate scores
            $validator = Validator::make($row->toArray(), [
                'assignment_1' => 'nullable|numeric|min:0|max:100',
                'assignment_2' => 'nullable|numeric|min:0|max:100',
                'assignment_3' => 'nullable|numeric|min:0|max:100',
                'mid_sem' => 'nullable|numeric|min:0|max:100',
                'end_sem' => 'nullable|numeric|min:0|max:100',
            ]);

            if ($validator->fails()) {
                foreach ($validator->errors()->all() as $error) {
                    $this->errors[] = "Row {$rowNumber}: {$error}";
                }
                $this->summary['errors']++;

                continue;
            }

            // Check if record exists
            $existing = AssessmentScore::where([
                'course_id' => $this->courseId,
                'student_id' => $student->id,
                'cohort_id' => $this->cohortId,
                'semester_id' => $this->semesterId,
            ])->first();

            $this->validatedData[] = [
                'row_number' => $rowNumber,
                'student_id' => $student->id,
                'student_number' => $student->student_id,
                'student_name' => $student->name,
                'assignment_1' => $row['assignment_1'] ?? null,
                'assignment_2' => $row['assignment_2'] ?? null,
                'assignment_3' => $row['assignment_3'] ?? null,
                'mid_semester' => $row['mid_sem'] ?? null,
                'end_semester' => $row['end_sem'] ?? null,
                'is_update' => $existing !== null,
                'existing_id' => $existing?->id,
            ];

            if ($existing) {
                $this->summary['updates']++;
            } else {
                $this->summary['new']++;
            }

            $this->summary['valid']++;
        }
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
