<?php

namespace App\Reports\Academics;

use App\Reports\BaseReport;
use App\Models\CollegeClass;
use App\Models\Semester;
use App\Models\Student;
use App\Services\StudentPerformanceService;
use Illuminate\Support\Collection;

class ClassPerformanceReport extends BaseReport
{
    public function getName(): string
    {
        return 'Class Performance Summary';
    }

    public function getDescription(): string
    {
        return 'Academic status summary of students in a class, including performance classifications and progression.';
    }

    public function getModule(): string
    {
        return 'Academics';
    }

    public function getIcon(): string
    {
        return 'ki-duotone ki-chart-simple';
    }

    public function getFilters(): array
    {
        $classes = CollegeClass::pluck('name', 'id')->toArray();
        $semesters = Semester::pluck('name', 'id')->toArray();

        return [
            [
                'key' => 'college_class_id',
                'label' => 'Class',
                'type' => 'select',
                'options' => $classes,
                'required' => true,
                'col' => 6
            ],
            [
                'key' => 'semester_id',
                'label' => 'Up to Semester (Optional)',
                'type' => 'select',
                'options' => $semesters,
                'required' => false,
                'col' => 6
            ]
        ];
    }

    public function getColumns(): array
    {
        return [
            'metric' => 'Metric',
            'count' => 'Number of Students'
        ];
    }

    public function generateData(array $filters = []): Collection
    {
        $classId = $filters['college_class_id'] ?? null;
        $semesterId = $filters['semester_id'] ?? null;

        if (!$classId) {
            return collect([]);
        }

        $students = Student::where('college_class_id', $classId)->get();
        $performanceService = app(StudentPerformanceService::class);

        $metrics = [
            'Total Students' => $students->count(),
            'First Class' => 0,
            'Second Class Upper' => 0,
            'Second Class Lower' => 0,
            'Third Class' => 0,
            'Pass' => 0,
            'Fail' => 0,
            'Promoted' => 0,
            'Repeat' => 0,
            'Dismissed/Probation' => 0,
            'Other/Unknown' => 0,
        ];

        foreach ($students as $student) {
            // Calculate performance up to the selected semester
            $perf = $performanceService->calculateStudentPerformance($student->id, $semesterId);
            
            // If they have no scores, we might skip classifying or put them in Unknown
            if ($perf['total_credits'] == 0 && empty($perf['overall_remark'])) {
                $metrics['Other/Unknown']++;
                continue;
            }

            // Overall Remark categorization
            if (isset($metrics[$perf['overall_remark']])) {
                $metrics[$perf['overall_remark']]++;
            }

            // Progress Remark categorization
            $progress = $perf['progress_remark'];
            if ($progress === 'Promoted') {
                $metrics['Promoted']++;
            } elseif ($progress === 'Repeat') {
                $metrics['Repeat']++;
            } elseif (in_array($progress, ['Dismissed', 'Probation'])) {
                $metrics['Dismissed/Probation']++;
            }
        }

        // Format for the table
        $data = collect();
        foreach ($metrics as $metricName => $count) {
            $data->push([
                'metric' => $metricName,
                'count' => $count
            ]);
        }

        return $data;
    }
}
