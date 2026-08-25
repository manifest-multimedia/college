<?php

namespace App\Imports;

use App\Models\CollegeClass;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\Year;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;

class CoursesImport implements ToCollection, WithStartRow
{
    protected $importedCount = 0;

    protected $updatedCount = 0;

    protected $errors = [];

    public function startRow(): int
    {
        return 2; // Skip header row
    }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $rowNum = $index + 2;

            $courseCode = isset($row[0]) ? trim((string) $row[0]) : '';
            $courseName = isset($row[1]) ? trim((string) $row[1]) : '';

            if (empty($courseCode) || empty($courseName)) {
                continue; // Skip blank rows or rows missing code/name
            }

            // Determine if column 2 is credit hours or semester (legacy format support)
            $col2 = isset($row[2]) ? trim((string) $row[2]) : '';
            $col3 = isset($row[3]) ? trim((string) $row[3]) : '';
            $col4 = isset($row[4]) ? trim((string) $row[4]) : '';
            $col5 = isset($row[5]) ? trim((string) $row[5]) : '';
            $col6 = isset($row[6]) ? trim((string) $row[6]) : '';

            if (is_numeric($col2)) {
                $creditHours = (float) $col2;
                $programName = $col3;
                $yearName = $col4;
                $semesterName = $col5;
                $description = $col6;
            } else {
                $creditHours = 3.0;
                $semesterName = $col2;
                $yearName = $col3;
                $programName = $col4;
                $description = $col5;
            }

            try {
                // Resolve or create Semester
                $semesterId = null;
                if (! empty($semesterName)) {
                    $semester = Semester::firstOrCreate(['name' => $semesterName]);
                    $semesterId = $semester->id;
                }

                // Resolve or create Year
                $yearId = null;
                if (! empty($yearName)) {
                    $year = Year::firstOrCreate(['name' => $yearName]);
                    $yearId = $year->id;
                }

                // Resolve or create Program / CollegeClass
                $collegeClassId = null;
                if (! empty($programName)) {
                    $program = CollegeClass::firstOrCreate(
                        ['name' => $programName],
                        ['slug' => Str::slug($programName)]
                    );
                    $collegeClassId = $program->id;
                }

                // Find existing course by course_code (and college_class_id if available)
                $existing = Subject::where('course_code', $courseCode)
                    ->when($collegeClassId, function ($q) use ($collegeClassId) {
                        return $q->where('college_class_id', $collegeClassId);
                    })
                    ->first();

                if (! $existing) {
                    $existing = Subject::where('course_code', $courseCode)->first();
                }

                if ($existing) {
                    $existing->update([
                        'name' => $courseName,
                        'credit_hours' => $creditHours,
                        'description' => $description ?: $existing->description,
                        'slug' => Str::slug($courseName),
                        'semester_id' => $semesterId ?: $existing->semester_id,
                        'year_id' => $yearId ?: $existing->year_id,
                        'college_class_id' => $collegeClassId ?: $existing->college_class_id,
                    ]);
                    $this->updatedCount++;
                } else {
                    // Fallback to first available college class if none supplied
                    if (! $collegeClassId) {
                        $defaultClass = CollegeClass::first();
                        $collegeClassId = $defaultClass ? $defaultClass->id : 1;
                    }
                    if (! $yearId) {
                        $defaultYear = Year::first();
                        $yearId = $defaultYear ? $defaultYear->id : 1;
                    }
                    if (! $semesterId) {
                        $defaultSemester = Semester::first();
                        $semesterId = $defaultSemester ? $defaultSemester->id : 1;
                    }

                    Subject::create([
                        'course_code' => $courseCode,
                        'name' => $courseName,
                        'credit_hours' => $creditHours,
                        'description' => $description,
                        'slug' => Str::slug($courseName),
                        'semester_id' => $semesterId,
                        'year_id' => $yearId,
                        'college_class_id' => $collegeClassId,
                    ]);
                    $this->importedCount++;
                }
            } catch (\Exception $e) {
                Log::error("Error importing course at row {$rowNum}: ".$e->getMessage());
                $this->errors[] = "Row {$rowNum} ({$courseCode}): ".$e->getMessage();
            }
        }
    }

    public function getImportedCount(): int
    {
        return $this->importedCount;
    }

    public function getUpdatedCount(): int
    {
        return $this->updatedCount;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
