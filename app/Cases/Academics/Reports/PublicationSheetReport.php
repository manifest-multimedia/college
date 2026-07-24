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
    public $reportSemesters = [];
    public $reportProgram = 'N/A';

    public function setFilters(array $filters)
    {
        $this->currentFilters = $filters;

        if (!empty($filters['college_class_id'])) {
            $this->reportProgram = CollegeClass::find($filters['college_class_id'])->name ?? 'N/A';
            
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
            $academicYears = $uniqueSemesters->pluck('academicYear')->unique('id')->sortBy('start_date')->values();
            
            $yearMapping = [];
            $semesterStructure = [];
            
            foreach ($academicYears as $index => $ay) {
                $ordinal = ['1ST', '2ND', '3RD', '4TH', '5TH', '6TH'][$index] ?? ($index + 1) . 'TH';
                $yearLabel = $ordinal . ' YEAR';
                $yearMapping[$ay->id] = $yearLabel;
                $semesterStructure[$yearLabel] = [];
            }
            
            foreach ($uniqueSemesters as $sem) {
                $yearLabel = $yearMapping[$sem->academic_year_id] ?? 'UNKNOWN YEAR';
                $shortName = str_ireplace('semester', 'SEM', $sem->name);
                $semesterStructure[$yearLabel][$sem->id] = strtoupper($shortName);
            }
            
            $this->reportSemesters = $semesterStructure;
        }
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
        foreach ($this->reportSemesters as $yearLabel => $semesters) {
            foreach ($semesters as $semesterId => $semesterName) {
                $columns['sem_' . $semesterId] = $yearLabel . ' - ' . $semesterName . ' (GPA)';
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
                $studentRow['sem_' . $semId] = $semInfo['gpa'];
            }
        }

        return collect($data);
    }

    public function getExcelTemplate(): string
    {
        return 'reports.export.publication_sheet_excel';
    }
}
