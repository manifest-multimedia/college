<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AssessmentScore;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssessmentScoresController extends Controller
{
    public function index()
    {
        return view('student.assessment-scores');
    }

    public function getScores(Request $request)
    {
        $user = Auth::user();

        if (! $user->student) {
            return response()->json([
                'success' => false,
                'message' => 'No student record found for this user',
            ], 404);
        }

        $request->validate([
            'semester_id' => 'nullable|exists:semesters,id',
            'academic_year' => 'nullable|string',
            'per_page' => 'nullable|integer|min:15|max:100',
        ]);

        $query = AssessmentScore::with(['course', 'semester', 'cohort', 'academicYear'])
            ->where('student_id', $user->student->id)
            ->where('is_published', true);

        if ($request->filled('semester_id')) {
            $query->where('semester_id', $request->semester_id);
        }

        if ($request->filled('academic_year')) {
            // Filter by academic year name through the relationship
            $query->whereHas('academicYear', function ($q) use ($request) {
                $q->where('name', $request->academic_year);
            });
        }

        $perPage = $request->input('per_page', 15);
        $scores = $query->orderBy('semester_id', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        // Calculate summary statistics per semester
        $summary = [];
        $allScores = AssessmentScore::with(['course', 'semester'])
            ->where('student_id', $user->student->id)
            ->where('is_published', true)
            ->get();

        // Sort semesters by semester_id (chronological order) for running CGPA calculation
        $groupedBySemester = $allScores->groupBy('semester_id')->sortKeys();

        // Running totals for cumulative GPA up to each semester
        $runningTotalCredits = 0;
        $runningTotalGradePoints = 0;

        foreach ($groupedBySemester as $semesterId => $semesterScores) {
            $totalCredits = 0;
            $totalGradePoints = 0;
            $passedCourses = 0;
            $failedCourses = 0;

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

            $semesterGPA = $totalCredits > 0 ? $totalGradePoints / $totalCredits : 0;

            // Update running totals for cumulative GPA up to this semester
            $runningTotalCredits += $totalCredits;
            $runningTotalGradePoints += $totalGradePoints;
            $cumulativeGpaUpToSemester = $runningTotalCredits > 0
                ? $runningTotalGradePoints / $runningTotalCredits
                : 0;

            $semesterName = $semesterScores->first()->semester->name ?? 'N/A';
            $summary[$semesterId] = [
                'semester_name' => $semesterName,
                'total_credits' => $totalCredits,
                'gpa' => $semesterGPA,
                'passed_courses' => $passedCourses,
                'failed_courses' => $failedCourses,
                'progress_remark' => $this->getAcademicProgressRemark($cumulativeGpaUpToSemester, $semesterName),
            ];
        }

        // Calculate overall CGPA (same as running total after all semesters)
        $overallTotalCredits = $runningTotalCredits;
        $overallTotalGradePoints = $runningTotalGradePoints;

        $cgpa = $overallTotalCredits > 0 ? $overallTotalGradePoints / $overallTotalCredits : 0;
        $overallRemark = $this->getOverallRemark($cgpa);

        return response()->json([
            'success' => true,
            'scores' => $scores->map(function ($score) {
                return [
                    'course_name' => $score->course->name,
                    'course_code' => $score->course->course_code ?? $score->course->code ?? 'N/A',
                    'credit_hours' => $score->course->credit_hours ?? 3,
                    'semester' => $score->semester->name,
                    'academic_year' => $score->academicYear->name ?? $score->academic_year_id,
                    'cohort' => $score->cohort->name,
                    'total_score' => $score->total_score,
                    'grade_letter' => $score->grade_letter,
                    'grade_points' => $score->grade_points,
                    'status' => $this->getStatus($score),
                ];
            }),
            'pagination' => [
                'current_page' => $scores->currentPage(),
                'last_page' => $scores->lastPage(),
                'per_page' => $scores->perPage(),
                'total' => $scores->total(),
            ],
            'summary' => [
                'total_credits' => $overallTotalCredits,
                'cgpa' => $cgpa,
                'overall_remark' => $overallRemark,
                'semesters' => $summary,
            ],
        ]);
    }

    private function getStatus($score): string
    {
        $totalScore = $score->total_score;

        if ($totalScore >= 50) {
            return 'Pass';
        } elseif ($totalScore >= 40) {
            return 'Resit';
        } else {
            return 'Carryover';
        }
    }

    private function getOverallRemark($cgpa): string
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
    private function getAcademicProgressRemark(float $cgpa, string $semesterName): string
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

    public function exportPdf(Request $request)
    {
        $user = Auth::user();

        if (! $user->student) {
            abort(404, 'No student record found for this user');
        }

        $request->validate([
            'semester_id' => 'nullable|exists:semesters,id',
            'academic_year' => 'nullable|string',
        ]);

        $query = AssessmentScore::with(['course', 'semester', 'cohort', 'academicYear'])
            ->where('student_id', $user->student->id)
            ->where('is_published', true);

        if ($request->filled('semester_id')) {
            $query->where('semester_id', $request->semester_id);
        }

        if ($request->filled('academic_year')) {
            // Filter by academic year name through the relationship
            $query->whereHas('academicYear', function ($q) use ($request) {
                $q->where('name', $request->academic_year);
            });
        }

        $scores = $query->orderBy('semester_id', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        if ($scores->isEmpty()) {
            abort(404, 'No published results found');
        }

        // Calculate summary
        $totalCredits = 0;
        $totalGradePoints = 0;
        $passedCourses = 0;
        $failedCourses = 0;

        $scoresData = $scores->map(function ($score) use (&$totalCredits, &$totalGradePoints, &$passedCourses, &$failedCourses) {
            $creditHours = $score->course->credit_hours ?? 3;
            $totalCredits += $creditHours;
            $totalGradePoints += ($score->grade_points * $creditHours);

            if ($score->is_passed) {
                $passedCourses++;
            } else {
                $failedCourses++;
            }

            return [
                'course_code' => $score->course->course_code ?? $score->course->code ?? 'N/A',
                'course_name' => $score->course->name,
                'credit_hours' => $creditHours,
                'grade_letter' => $score->grade_letter,
                'grade_points' => $score->grade_points,
                'status' => $this->getStatus($score),
                'semester' => $score->semester->name ?? 'N/A',
            ];
        });

        $cgpa = $totalCredits > 0 ? round($totalGradePoints / $totalCredits, 2) : 0;
        $overallRemark = $this->getOverallRemark($cgpa);

        // Build per-semester progress remarks using running cumulative GPA
        $semesterRemarks = [];
        $runningCredits = 0;
        $runningGradePoints = 0;
        $allStudentScores = AssessmentScore::with(['course', 'semester'])
            ->where('student_id', $user->student->id)
            ->where('is_published', true)
            ->get()
            ->groupBy('semester_id')
            ->sortKeys();

        foreach ($allStudentScores as $semesterId => $semScores) {
            foreach ($semScores as $score) {
                $ch = $score->course->credit_hours ?? 3;
                $runningCredits += $ch;
                $runningGradePoints += ($score->grade_points * $ch);
            }
            $cumulativeGpa = $runningCredits > 0 ? $runningGradePoints / $runningCredits : 0;
            $semName = $semScores->first()->semester->name ?? 'N/A';
            $semesterRemarks[$semName] = $this->getAcademicProgressRemark($cumulativeGpa, $semName);
        }

        $summary = [
            'total_credits' => $totalCredits,
            'cgpa' => $cgpa,
            'overall_remark' => $overallRemark,
            'passed_courses' => $passedCourses,
            'failed_courses' => $failedCourses,
            'semester_remarks' => $semesterRemarks,
        ];

        $student = $user->student;

        $pdf = Pdf::loadView('student.assessment-scores-pdf', [
            'student' => $student,
            'scores' => $scoresData,
            'summary' => $summary,
            'generated_date' => now()->format('F d, Y h:i A'),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('my-results-'.now()->format('Y-m-d').'.pdf');
    }
}
