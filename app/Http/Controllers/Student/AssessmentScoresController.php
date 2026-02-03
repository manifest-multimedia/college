<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AssessmentScore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssessmentScoresController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:Student']);
    }

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

        $query = AssessmentScore::with(['course', 'semester', 'cohort'])
            ->where('student_id', $user->student->id)
            ->where('is_published', true);

        if ($request->filled('semester_id')) {
            $query->where('semester_id', $request->semester_id);
        }

        if ($request->filled('academic_year')) {
            $query->where('academic_year_id', $request->academic_year);
        }

        $perPage = $request->input('per_page', 15);
        $scores = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'scores' => $scores->map(function ($score) {
                return [
                    'course_name' => $score->course->name,
                    'course_code' => $score->course->code,
                    'semester' => $score->semester->name,
                    'academic_year' => $score->academic_year_id,
                    'cohort' => $score->cohort->name,
                    'assignment_1' => $score->assignment_1_score,
                    'assignment_2' => $score->assignment_2_score,
                    'assignment_3' => $score->assignment_3_score,
                    'assignment_4' => $score->assignment_4_score,
                    'assignment_5' => $score->assignment_5_score,
                    'mid_semester' => $score->mid_semester_score,
                    'end_semester' => $score->end_semester_score,
                    'total_score' => $score->total_score,
                    'grade_letter' => $score->grade_letter,
                    'grade_points' => $score->grade_points,
                ];
            }),
            'pagination' => [
                'current_page' => $scores->currentPage(),
                'last_page' => $scores->lastPage(),
                'per_page' => $scores->perPage(),
                'total' => $scores->total(),
            ],
        ]);
    }
}
