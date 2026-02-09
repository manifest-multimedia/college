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

        $groupedBySemester = $allScores->groupBy('semester_id');

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

            $semesterGPA = $totalCredits > 0 ? round($totalGradePoints / $totalCredits, 2) : 0;

            $summary[$semesterId] = [
                'semester_name' => $semesterScores->first()->semester->name ?? 'N/A',
                'total_credits' => $totalCredits,
                'gpa' => $semesterGPA,
                'passed_courses' => $passedCourses,
                'failed_courses' => $failedCourses,
            ];
        }

        // Calculate CGPA
        $overallTotalCredits = 0;
        $overallTotalGradePoints = 0;

        foreach ($allScores as $score) {
            $creditHours = $score->course->credit_hours ?? 3;
            $overallTotalCredits += $creditHours;
            $overallTotalGradePoints += ($score->grade_points * $creditHours);
        }

        $cgpa = $overallTotalCredits > 0 ? round($overallTotalGradePoints / $overallTotalCredits, 2) : 0;
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
        if ($cgpa >= 3.5) {
            return 'First Class';
        } elseif ($cgpa >= 3.0) {
            return 'Second Class Upper';
        } elseif ($cgpa >= 2.5) {
            return 'Second Class Lower';
        } elseif ($cgpa >= 2.0) {
            return 'Pass';
        } else {
            return 'Fail';
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

        $summary = [
            'total_credits' => $totalCredits,
            'cgpa' => $cgpa,
            'overall_remark' => $overallRemark,
            'passed_courses' => $passedCourses,
            'failed_courses' => $failedCourses,
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
