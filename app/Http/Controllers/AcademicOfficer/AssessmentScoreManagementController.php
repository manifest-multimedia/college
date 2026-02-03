<?php

namespace App\Http\Controllers\AcademicOfficer;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\AssessmentScore;
use App\Models\Cohort;
use App\Models\CollegeClass;
use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssessmentScoreManagementController extends Controller
{
    public function index()
    {
        $collegeClasses = CollegeClass::orderBy('name')->get();
        $cohorts = Cohort::where('is_active', true)->orderBy('name', 'desc')->get();
        $semesters = Semester::orderBy('name')->get();
        $academicYears = AcademicYear::query()
            ->select('name')
            ->distinct()
            ->pluck('name');

        $currentCohort = Cohort::where('is_active', true)->first();
        $currentSemester = Semester::where('is_current', true)->first();

        return view('academic-officer.assessment-scores', compact(
            'collegeClasses',
            'cohorts',
            'semesters',
            'academicYears',
            'currentCohort',
            'currentSemester'
        ));
    }

    public function getScores(Request $request)
    {
        $request->validate([
            'academic_year' => 'required|string',
            'semester_id' => 'required|exists:semesters,id',
            'college_class_id' => 'nullable|exists:college_classes,id',
            'course_id' => 'nullable|exists:subjects,id',
            'cohort_id' => 'nullable|exists:cohorts,id',
            'per_page' => 'nullable|integer|min:15|max:100',
        ]);

        $query = AssessmentScore::with(['student', 'course', 'cohort', 'semester', 'publishedBy'])
            ->where('academic_year_id', $request->academic_year)
            ->where('semester_id', $request->semester_id);

        if ($request->filled('college_class_id')) {
            $query->whereHas('student', function ($q) use ($request) {
                $q->where('college_class_id', $request->college_class_id);
            });
        }

        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        if ($request->filled('cohort_id')) {
            $query->where('cohort_id', $request->cohort_id);
        }

        $perPage = $request->input('per_page', 15);
        $scores = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'scores' => $scores->map(function ($score) {
                return [
                    'id' => $score->id,
                    'student_name' => $score->student->full_name,
                    'student_index' => $score->student->index_no,
                    'course_name' => $score->course->name,
                    'course_code' => $score->course->code,
                    'cohort' => $score->cohort->name,
                    'total_score' => $score->total_score,
                    'grade_letter' => $score->grade_letter,
                    'is_published' => $score->is_published,
                    'published_at' => $score->published_at?->format('Y-m-d H:i'),
                    'published_by' => $score->publishedBy?->name,
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

    public function togglePublish(Request $request, $id)
    {
        $score = AssessmentScore::findOrFail($id);

        $score->is_published = ! $score->is_published;
        $score->published_at = $score->is_published ? now() : null;
        $score->published_by = $score->is_published ? Auth::id() : null;
        $score->save();

        return response()->json([
            'success' => true,
            'message' => $score->is_published ? 'Score published successfully' : 'Score unpublished successfully',
            'is_published' => $score->is_published,
            'published_at' => $score->published_at?->format('Y-m-d H:i'),
            'published_by' => $score->publishedBy?->name,
        ]);
    }

    public function bulkPublish(Request $request)
    {
        $request->validate([
            'academic_year' => 'required|string',
            'semester_id' => 'required|exists:semesters,id',
            'course_id' => 'required|exists:subjects,id',
            'action' => 'required|in:publish,unpublish',
        ]);

        $query = AssessmentScore::where('academic_year_id', $request->academic_year)
            ->where('semester_id', $request->semester_id)
            ->where('course_id', $request->course_id);

        if ($request->filled('cohort_id')) {
            $query->where('cohort_id', $request->cohort_id);
        }

        $count = $query->count();

        if ($request->action === 'publish') {
            $query->update([
                'is_published' => true,
                'published_at' => now(),
                'published_by' => Auth::id(),
            ]);
            $message = "{$count} score(s) published successfully";
        } else {
            $query->update([
                'is_published' => false,
                'published_at' => null,
                'published_by' => null,
            ]);
            $message = "{$count} score(s) unpublished successfully";
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'count' => $count,
        ]);
    }
}
