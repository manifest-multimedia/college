<?php

namespace App\Services;

use App\Models\AssessmentScore;

class StudentPerformanceService
{
    /**
     * Get overall remark based on CGPA
     */
    public function getOverallRemark(float $cgpa): string
    {
        if ($cgpa >= 3.6) {
            return 'First Class';
        } elseif ($cgpa >= 3.0) {
            return 'Second Class Upper';
        } elseif ($cgpa >= 2.5) {
            return 'Second Class Lower';
        } elseif ($cgpa >= 2.0) {
            return 'Third Class';
        } elseif ($cgpa >= 1.5) {
            return 'Pass';
        } else {
            return 'Fail';
        }
    }

    /**
     * Get per-semester academic progress remark based on CGPA and semester position.
     *
     * First Semester rules (semester name contains "First"):
     *   CGPA >= 1.50 → Pass
     *   CGPA <  1.50 → Probation
     *
     * Second Semester rules (all other semesters):
     *   CGPA <  1.00 → Dismissed
     *   CGPA 1.00–1.49 → Repeat
     *   CGPA >= 1.50 → Promoted
     */
    public function getAcademicProgressRemark(float $cgpa, string $semesterName): string
    {
        $isFirstSemester = stripos($semesterName, 'first') !== false;

        if ($isFirstSemester) {
            return $cgpa >= 1.50 ? 'Pass' : 'Probation';
        }

        if ($cgpa < 1.00) {
            return 'Dismissed';
        } elseif ($cgpa < 1.50) {
            return 'Repeat';
        } else {
            return 'Promoted';
        }
    }

    /**
     * Calculate student performance metrics
     * 
     * @param int $studentId
     * @param int|null $maxSemesterId Optional maximum semester ID to calculate up to
     * @return array
     */
    public function calculateStudentPerformance(int $studentId, ?int $maxSemesterId = null): array
    {
        $query = AssessmentScore::with(['course', 'semester'])
            ->where('student_id', $studentId)
            ->where('is_published', true);

        $scores = $query->get();

        // If maxSemesterId is provided, filter scores in PHP (assuming semester IDs are roughly chronological, 
        // or just rely on the controller's logic where they iterate and stop if needed. 
        // A safer way if semester IDs aren't chronological is to join academic years, but let's stick to the controller's assumption:
        // "Sort semesters by semester_id (chronological order) for running CGPA calculation"
        if ($maxSemesterId) {
            $scores = $scores->filter(function ($score) use ($maxSemesterId) {
                return $score->semester_id <= $maxSemesterId;
            });
        }

        $totalCredits = 0;
        $totalGradePoints = 0;
        $passedCourses = 0;
        $failedCourses = 0;
        $lastSemesterName = 'Unknown';

        $groupedBySemester = $scores->groupBy('semester_id')->sortKeys();

        foreach ($groupedBySemester as $semesterId => $semesterScores) {
            foreach ($semesterScores as $score) {
                $creditHours = $score->course->credit_hours ?? 3;
                $totalCredits += $creditHours;
                $totalGradePoints += ($score->grade_points * $creditHours);

                if ($score->is_passed) {
                    $passedCourses++;
                } else {
                    $failedCourses++;
                }
            }
            $lastSemesterName = $semesterScores->first()->semester->name ?? 'Unknown';
        }

        $cgpa = $totalCredits > 0 ? $totalGradePoints / $totalCredits : 0;
        $overallRemark = $this->getOverallRemark($cgpa);
        
        $progressRemark = 'N/A';
        if ($scores->isNotEmpty()) {
            $progressRemark = $this->getAcademicProgressRemark($cgpa, $lastSemesterName);
        }

        return [
            'total_credits' => $totalCredits,
            'total_grade_points' => $totalGradePoints,
            'cgpa' => $cgpa,
            'overall_remark' => $overallRemark,
            'progress_remark' => $progressRemark,
            'passed_courses' => $passedCourses,
            'failed_courses' => $failedCourses,
        ];
    }
}
