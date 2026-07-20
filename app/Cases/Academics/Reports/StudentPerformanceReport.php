<?php

namespace App\Cases\Academics\Reports;

use App\Models\Student;
use App\Models\CollegeClass;
use App\Models\Cohort;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Reports\BaseReport;
use App\Services\StudentPerformanceService;
use Illuminate\Support\Collection;

class StudentPerformanceReport extends BaseReport
{
    public function getName(): string
    {
        return 'Student Performance Report';
    }

    public function getDescription(): string
    {
        return 'Summary of students’ performance, classification, and academic progression.';
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
                'required' => false,
            ],
            [
                'key' => 'cohort_id',
                'label' => 'Cohort',
                'type' => 'select',
                'options' => Cohort::pluck('name', 'id')->toArray(),
                'required' => false,
            ],
            [
                'key' => 'academic_year_id',
                'label' => 'Academic Year',
                'type' => 'select',
                'options' => AcademicYear::pluck('name', 'id')->toArray(),
                'required' => false,
            ],
            [
                'key' => 'semester_id',
                'label' => 'Semester (Up to)',
                'type' => 'select',
                'options' => Semester::pluck('name', 'id')->toArray(),
                'required' => false,
            ],
        ];
    }

    public function getColumns(): array
    {
        return [
            'metric' => 'Metric',
            'count' => 'Count',
        ];
    }

    public function generateData(array $filters = []): Collection
    {
        $query = Student::query();

        if (!empty($filters['college_class_id'])) {
            $query->where('college_class_id', $filters['college_class_id']);
        }
        if (!empty($filters['cohort_id'])) {
            $query->where('cohort_id', $filters['cohort_id']);
        }

        // We fetch the students
        $students = $query->get();
        $totalStudents = $students->count();

        $performanceService = app(StudentPerformanceService::class);
        $maxSemesterId = !empty($filters['semester_id']) ? $filters['semester_id'] : null;

        $classifications = [
            'First Class' => 0,
            'Second Class Upper' => 0,
            'Second Class Lower' => 0,
            'Third Class' => 0,
            'Pass' => 0,
            'Fail' => 0,
        ];

        $progressions = [
            'Promoted' => 0,
            'Repeat' => 0,
            'Dismissed' => 0,
            'Probation' => 0,
            'Pass' => 0, // Note: First semester pass
            'N/A' => 0,
        ];

        $withResits = 0;

        foreach ($students as $student) {
            $performance = $performanceService->calculateStudentPerformance($student->id, $maxSemesterId);
            
            // Only count classifications if they have credits
            if ($performance['total_credits'] > 0) {
                $remark = $performance['overall_remark'];
                if (isset($classifications[$remark])) {
                    $classifications[$remark]++;
                }
                
                $progRemark = $performance['progress_remark'];
                if (isset($progressions[$progRemark])) {
                    $progressions[$progRemark]++;
                }

                if ($performance['failed_courses'] > 0) {
                    $withResits++;
                }
            } else {
                $progressions['N/A']++;
            }
        }

        // Consolidate data into a report collection
        $data = [
            ['metric' => 'Total Students', 'count' => $totalStudents],
            // Classifications
            ['metric' => 'First Class', 'count' => $classifications['First Class']],
            ['metric' => 'Second Class Upper', 'count' => $classifications['Second Class Upper']],
            ['metric' => 'Second Class Lower', 'count' => $classifications['Second Class Lower']],
            ['metric' => 'Third Class', 'count' => $classifications['Third Class']],
            ['metric' => 'Pass (Classification)', 'count' => $classifications['Pass']],
            ['metric' => 'Fail (Classification)', 'count' => $classifications['Fail']],
            // Progressions
            ['metric' => 'Promoted (or 1st Sem Pass)', 'count' => $progressions['Promoted'] + $progressions['Pass']],
            ['metric' => 'Repeated', 'count' => $progressions['Repeat']],
            ['metric' => 'Dismissed / Withdrawn', 'count' => $progressions['Dismissed']],
            ['metric' => 'Probation', 'count' => $progressions['Probation']],
            ['metric' => 'With Resit / Referral', 'count' => $withResits],
            ['metric' => 'No published scores (N/A)', 'count' => $progressions['N/A']],
        ];

        return collect($data);
    }
}
