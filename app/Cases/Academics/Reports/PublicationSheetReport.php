<?php

namespace App\Cases\Academics\Reports;

use App\Models\Student;
use App\Models\CollegeClass;
use App\Models\Cohort;
use App\Reports\BaseReport;
use App\Services\StudentPerformanceService;
use Illuminate\Support\Collection;
use App\Models\AssessmentScore;

class PublicationSheetReport extends BaseReport
{
    protected $currentFilters = [];
    public $reportProgram = null;
    public $reportSemesters = [];
    public $dbSemesterToKeyMapping = [];

    public function setFilters(array $filters)
    {
        $this->currentFilters = $filters;

        if (!empty($filters['college_class_id'])) {
            $cohort = null;
            if (!empty($filters['cohort_id'])) {
                $cohort = Cohort::find($filters['cohort_id']);
            }
            
            $programName = CollegeClass::find($filters['college_class_id'])->name ?? 'N/A';
            $cohortName = $cohort ? $cohort->name : '';
            $this->reportProgram = $programName . ($cohortName ? " ({$cohortName})" : "");
            
            $query = Student::where('college_class_id', $filters['college_class_id']);
            if (!empty($filters['cohort_id'])) {
                $query->where('cohort_id', $filters['cohort_id']);
            }
            $studentIds = $query->pluck('id');
            
            $scores = AssessmentScore::with(['semester', 'academicYear'])
                ->whereIn('student_id', $studentIds)
                ->where('is_published', true)
                ->get();
                
            $uniqueSemesters = $scores->pluck('semester')->unique('id')->sortBy('start_date');
            
            // Fetch all global chronological timelines
            $allAcademicYears = AcademicYear::orderBy('start_date')->get();
            $allSemesters = Semester::orderBy('start_date')->get()->groupBy('academic_year_id');
            
            // Determine Starting Academic Year for the Cohort
            $startingAyId = null;
            if ($cohort && $cohort->start_date) {
                $cohortStart = \Carbon\Carbon::parse($cohort->start_date);
                // Find Academic Year that encapsulates the cohort start date, or is closest to it
                $closestAy = $allAcademicYears->sortBy(function($ay) use ($cohortStart) {
                    if (!$ay->start_date) return 9999999999;
                    return abs(\Carbon\Carbon::parse($ay->start_date)->diffInDays($cohortStart));
                })->first();
                if ($closestAy) {
                    $startingAyId = $closestAy->id;
                }
            }
            
            if (!$startingAyId) {
                // Fallback to earliest grade's academic year
                $academicYears = $uniqueSemesters->pluck('academicYear')->unique('id')->sortBy('start_date')->values();
                $startingAyId = $academicYears->first()->id ?? null;
            }
            
            $startingAyIndex = $allAcademicYears->search(fn($ay) => $ay->id == $startingAyId);
            if ($startingAyIndex === false) $startingAyIndex = 0;
            
            $cohortYears = [
                $allAcademicYears[$startingAyIndex]->id ?? -1,
                $allAcademicYears[$startingAyIndex + 1]->id ?? -1,
                $allAcademicYears[$startingAyIndex + 2]->id ?? -1,
            ];
            
            // Fixed 3-Year structure for UI/Excel
            $this->reportSemesters = [
                '1ST YEAR' => [
                    'y1_s1' => 'FIRST SEM',
                    'y1_s2' => 'SECOND SEM',
                ],
                '2ND YEAR' => [
                    'y2_s1' => 'FIRST SEM',
                    'y2_s2' => 'SECOND SEM',
                ],
                '3RD YEAR' => [
                    'y3_s1' => 'FIRST SEM',
                    'y3_s2' => 'SECOND SEM',
                ],
            ];
            
            // Map the actual DB semesters to the fixed structure keys (y1_s1, etc.)
            $this->dbSemesterToKeyMapping = [];
            foreach ($uniqueSemesters as $sem) {
                $yearIndex = array_search($sem->academic_year_id, $cohortYears);
                if ($yearIndex !== false) {
                    $yearNum = $yearIndex + 1;
                    $aySemesters = $allSemesters->get($sem->academic_year_id) ?? collect();
                    $semIndex = $aySemesters->search(fn($s) => $s->id == $sem->id);
                    if ($semIndex !== false && $semIndex < 2) {
                        $semNum = $semIndex + 1;
                        $this->dbSemesterToKeyMapping[$sem->id] = "y{$yearNum}_s{$semNum}";
                    }
                }
            }
        }
    }

    public function getPdfTemplate(): string
    {
        return 'reports.export.publication_sheet_pdf';
    }

    public function getPdfOrientation(): string
    {
        return 'landscape';
    }

    public function getUiTemplate(): string
    {
        return 'reports.ui.publication_sheet_ui';
    }
    public function getName(): string
    {
        return 'Publication Sheet For Promotion';
    }

    public function getDescription(): string
    {
        return 'Generates a semester-by-semester GPA breakdown, CGPA, class designation, and remarks for a selected program.';
    }

    public function getModule(): string
    {
        return 'Academics';
    }

    public function getFilters(): array
    {
        return [
            [
                'key' => 'college_class_id',
                'label' => 'Program (Class)',
                'type' => 'select',
                'options' => CollegeClass::pluck('name', 'id')->toArray(),
                'required' => true,
            ],
            [
                'key' => 'cohort_id',
                'label' => 'Cohort',
                'type' => 'select',
                'options' => Cohort::pluck('name', 'id')->toArray(),
                'required' => false,
            ],
        ];
    }

    public function getColumns(): array
    {
        $columns = [
            'index_number' => 'Index Number',
            'name' => 'Name',
        ];

        // dynamically fetch semesters based on current filters structure
        foreach ($this->reportSemesters ?? [] as $yearLabel => $semesters) {
            foreach ($semesters as $semesterKey => $semesterName) {
                $columns[$semesterKey] = $yearLabel . ' - ' . $semesterName . ' (GPA)';
            }
        }

        $columns['cgpa'] = 'CGPA';
        $columns['class_designation'] = 'Class Designation';
        $columns['remarks'] = 'Remarks';

        return $columns;
    }

    public function generateData(array $filters = []): Collection
    {
        if (empty($filters['college_class_id'])) {
            return collect();
        }

        $query = Student::where('college_class_id', $filters['college_class_id']);

        if (!empty($filters['cohort_id'])) {
            $query->where('cohort_id', $filters['cohort_id']);
        }

        $students = $query->orderBy('student_id', 'asc')->get();
        $performanceService = app(StudentPerformanceService::class);
        $data = [];

        // Determine all unique semesters for the class to build dynamic columns if needed
        $allSemesters = collect();

        foreach ($students as $student) {
            $scores = AssessmentScore::with(['course', 'semester', 'academicYear'])
                ->where('student_id', $student->id)
                ->where('is_published', true)
                ->get();

            $groupedBySemester = $scores->groupBy('semester_id')->sortKeys();
            $semesterGpas = [];

            $totalCredits = 0;
            $totalGradePoints = 0;
            $lastSemesterName = 'Unknown';

            foreach ($groupedBySemester as $semesterId => $semesterScores) {
                $semCredits = 0;
                $semGradePoints = 0;

                foreach ($semesterScores as $score) {
                    $creditHours = $score->course->credit_hours ?? 3;
                    $semCredits += $creditHours;
                    $semGradePoints += ($score->grade_points * $creditHours);
                    
                    $totalCredits += $creditHours;
                    $totalGradePoints += ($score->grade_points * $creditHours);
                }

                $gpa = $semCredits > 0 ? $semGradePoints / $semCredits : 0;
                
                $semesterName = $semesterScores->first()->semester->name ?? 'Unknown';
                // Find year mapping if possible (e.g. 1st Year, 1st Sem)
                $semesterGpas[$semesterId] = [
                    'name' => $semesterName,
                    'gpa' => number_format((float)$gpa, 2, '.', '')
                ];
                $lastSemesterName = $semesterName;
                
                if (!$allSemesters->has($semesterId)) {
                    $allSemesters->put($semesterId, $semesterName);
                }
            }

            $cgpa = $totalCredits > 0 ? $totalGradePoints / $totalCredits : 0;
            $overallRemark = $performanceService->getOverallRemark($cgpa);
            
            $progressRemark = 'N/A';
            if ($scores->isNotEmpty()) {
                $progressRemark = $performanceService->getAcademicProgressRemark($cgpa, $lastSemesterName);
            }

            // Adjust overall remark (Class Designation) to match excel terms if necessary
            // E.g., Pass, Second Class Lower, etc. is already handled by getOverallRemark()

            // Remarks adjustment: Pass, Repeated, Dismissed
            if (stripos($progressRemark, 'Pass') !== false || stripos($progressRemark, 'Promoted') !== false) {
                $finalRemark = 'PASS';
            } elseif (stripos($progressRemark, 'Repeat') !== false) {
                $finalRemark = 'REPEATED';
            } elseif (stripos($progressRemark, 'Dismissed') !== false) {
                $finalRemark = 'DISMISSED';
            } else {
                $finalRemark = strtoupper($progressRemark);
            }

            $data[] = [
                'index_number' => $student->student_id,
                'name' => strtoupper($student->name),
                'semester_gpas' => $semesterGpas,
                'cgpa' => number_format((float)$cgpa, 2, '.', ''),
                'class_designation' => $overallRemark,
                'remarks' => $finalRemark,
            ];
        }
        
        // We no longer need to manually rebuild the semesters and program name here
        // as they are already built in setFilters() when the report is initialized.

        // Add dynamic keys for the UI table
        foreach ($data as &$studentRow) {
            foreach ($studentRow['semester_gpas'] as $semId => $semInfo) {
                $mappedKey = $this->dbSemesterToKeyMapping[$semId] ?? null;
                if ($mappedKey) {
                    $studentRow[$mappedKey] = $semInfo['gpa'];
                }
            }
        }

        return collect($data);
    }

    public function getExcelTemplate(): string
    {
        return 'reports.export.publication_sheet_excel';
    }
}
