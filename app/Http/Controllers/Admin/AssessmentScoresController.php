<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AssessmentScoresExport;
use App\Exports\AssessmentScoresTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\AssessmentScoresImport;
use App\Models\AcademicYear;
use App\Models\AssessmentScore;
use App\Models\Cohort;
use App\Models\CollegeClass;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class AssessmentScoresController extends Controller
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

        // Get current defaults
        $currentCohort = Cohort::where('is_active', true)->first();
        $currentSemester = Semester::where('is_current', true)->first();

        // Load default weights from system settings
        $settings = DB::table('system_settings')
            ->whereIn('key', ['default_assignment_weight', 'default_mid_semester_weight', 'default_end_semester_weight'])
            ->get()
            ->keyBy('key');

        $defaultWeights = [
            'assignment' => $settings->get('default_assignment_weight')->value ?? 20,
            'mid_semester' => $settings->get('default_mid_semester_weight')->value ?? 20,
            'end_semester' => $settings->get('default_end_semester_weight')->value ?? 60,
        ];

        return view('admin.assessment-scores-ajax', compact(
            'collegeClasses',
            'cohorts',
            'semesters',
            'academicYears',
            'currentCohort',
            'currentSemester',
            'defaultWeights'
        ));
    }

    public function getCourses(Request $request)
    {
        $classId = $request->input('class_id');
        $semesterId = $request->input('semester_id');

        $courses = Subject::query()
            ->when($classId, fn ($query) => $query->where('college_class_id', $classId))
            ->when($semesterId, fn ($query) => $query->where('semester_id', $semesterId))
            ->orderBy('name')
            ->get(['id', 'name', 'course_code']);

        return response()->json([
            'success' => true,
            'courses' => $courses,
        ]);
    }

    public function loadScoresheet(Request $request)
    {
        $validated = $request->validate([
            'class_id' => 'required|exists:college_classes,id',
            'course_id' => [
                'required',
                Rule::exists('subjects', 'id')->where(fn ($query) => $query->where('college_class_id', $request->class_id)),
            ],
            'cohort_id' => 'required|exists:cohorts,id',
            'semester_id' => 'required|exists:semesters,id',
        ]);

        // Load students for the selected program and cohort
        $students = Student::query()
            ->where('college_class_id', $validated['class_id'])
            ->where('cohort_id', $validated['cohort_id'])
            ->orderBy('student_id')
            ->get();

        if ($students->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No students found for the selected program and cohort. Please verify that students are assigned to this program and cohort.',
            ], 404);
        }

        $studentScores = [];
        $maxAssignmentCount = 3;

        foreach ($students as $student) {
            $existingScore = AssessmentScore::where([
                'course_id' => $validated['course_id'],
                'student_id' => $student->id,
                'cohort_id' => $validated['cohort_id'],
                'semester_id' => $validated['semester_id'],
            ])->first();

            if ($existingScore && $existingScore->assignment_count) {
                $maxAssignmentCount = max($maxAssignmentCount, $existingScore->assignment_count);
            }

            $studentData = [
                'student_id' => $student->id,
                'student_number' => $student->student_id,
                'student_name' => $student->name,
                'assignment_1' => $existingScore?->assignment_1_score,
                'assignment_2' => $existingScore?->assignment_2_score,
                'assignment_3' => $existingScore?->assignment_3_score,
                'assignment_4' => $existingScore?->assignment_4_score,
                'assignment_5' => $existingScore?->assignment_5_score,
                'mid_semester' => $existingScore?->mid_semester_score,
                'end_semester' => $existingScore?->end_semester_score,
                'total' => $existingScore?->total_score ?? 0,
                'grade' => $existingScore?->grade_letter ?? '',
                'existing_id' => $existingScore?->id,
            ];

            $studentScores[] = $studentData;
        }

        // Get weights from the first existing score or defaults
        $firstScore = AssessmentScore::where([
            'course_id' => $validated['course_id'],
            'cohort_id' => $validated['cohort_id'],
            'semester_id' => $validated['semester_id'],
        ])->first();

        $weights = [
            'assignment' => $firstScore?->assignment_weight ?? 20,
            'mid_semester' => $firstScore?->mid_semester_weight ?? 20,
            'end_semester' => $firstScore?->end_semester_weight ?? 60,
        ];

        return response()->json([
            'success' => true,
            'students' => $studentScores,
            'assignment_count' => $maxAssignmentCount,
            'weights' => $weights,
            'message' => count($studentScores).' students loaded successfully',
        ]);
    }

    public function saveScores(Request $request)
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:subjects,id',
            'cohort_id' => 'required|exists:cohorts,id',
            'semester_id' => 'required|exists:semesters,id',
            'assignment_weight' => 'required|numeric|min:0|max:100',
            'mid_semester_weight' => 'required|numeric|min:0|max:100',
            'end_semester_weight' => 'required|numeric|min:0|max:100',
            'assignment_count' => 'required|integer|min:3|max:5',
            'scores' => 'required|array',
            'scores.*.student_id' => 'required|exists:students,id',
            'scores.*.assignment_1' => 'nullable|numeric|min:0|max:100',
            'scores.*.assignment_2' => 'nullable|numeric|min:0|max:100',
            'scores.*.assignment_3' => 'nullable|numeric|min:0|max:100',
            'scores.*.assignment_4' => 'nullable|numeric|min:0|max:100',
            'scores.*.assignment_5' => 'nullable|numeric|min:0|max:100',
            'scores.*.mid_semester' => 'nullable|numeric|min:0|max:100',
            'scores.*.end_semester' => 'nullable|numeric|min:0|max:100',
            'scores.*.existing_id' => 'nullable|exists:assessment_scores,id',
        ]);

        // Validate total weight
        $totalWeight = $validated['assignment_weight'] + $validated['mid_semester_weight'] + $validated['end_semester_weight'];
        if ($totalWeight != 100) {
            return response()->json([
                'success' => false,
                'message' => "Weights must sum to 100%. Current total: {$totalWeight}%",
            ], 422);
        }

        $savedCount = 0;
        $updatedCount = 0;

        foreach ($validated['scores'] as $studentScore) {
            // Skip if no scores entered
            $hasScores = false;
            for ($i = 1; $i <= $validated['assignment_count']; $i++) {
                if (! empty($studentScore["assignment_{$i}"])) {
                    $hasScores = true;
                    break;
                }
            }
            if (! $hasScores && empty($studentScore['mid_semester']) && empty($studentScore['end_semester'])) {
                continue;
            }

            $data = [
                'course_id' => $validated['course_id'],
                'student_id' => $studentScore['student_id'],
                'cohort_id' => $validated['cohort_id'],
                'semester_id' => $validated['semester_id'],
                'assignment_1_score' => $studentScore['assignment_1'] ?? null,
                'assignment_2_score' => $studentScore['assignment_2'] ?? null,
                'assignment_3_score' => $studentScore['assignment_3'] ?? null,
                'assignment_4_score' => $studentScore['assignment_4'] ?? null,
                'assignment_5_score' => $studentScore['assignment_5'] ?? null,
                'mid_semester_score' => $studentScore['mid_semester'] ?? null,
                'end_semester_score' => $studentScore['end_semester'] ?? null,
                'assignment_weight' => $validated['assignment_weight'],
                'mid_semester_weight' => $validated['mid_semester_weight'],
                'end_semester_weight' => $validated['end_semester_weight'],
                'assignment_count' => $validated['assignment_count'],
                'recorded_by' => Auth::id(),
            ];

            if (! empty($studentScore['existing_id'])) {
                AssessmentScore::find($studentScore['existing_id'])->update($data);
                $updatedCount++;
            } else {
                AssessmentScore::create($data);
                $savedCount++;
            }
        }

        $message = 'Scores saved successfully! ';
        if ($savedCount > 0) {
            $message .= "$savedCount new records created. ";
        }
        if ($updatedCount > 0) {
            $message .= "$updatedCount records updated.";
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'saved_count' => $savedCount,
            'updated_count' => $updatedCount,
        ]);
    }

    public function downloadTemplate(Request $request)
    {
        $validated = $request->validate([
            'class_id' => 'required|exists:college_classes,id',
            'course_id' => [
                'required',
                Rule::exists('subjects', 'id')->where(fn ($query) => $query->where('college_class_id', $request->class_id)),
            ],
            'cohort_id' => 'required|exists:cohorts,id',
            'semester_id' => 'required|exists:semesters,id',
            'academic_year' => 'nullable|string',
        ]);

        $students = Student::where('college_class_id', $validated['class_id'])
            ->orderBy('student_id')
            ->get();

        $course = Subject::find($validated['course_id']);
        $class = CollegeClass::find($validated['class_id']);
        $cohort = Cohort::find($validated['cohort_id']);
        $semester = Semester::find($validated['semester_id']);

        $courseInfo = [
            'course' => $course->name,
            'programme' => $class->name,
            'cohort' => $cohort->name,
            'semester' => $semester->name,
            'academic_year' => $validated['academic_year'] ?? $cohort->academic_year,
        ];

        $weights = [
            'assignment' => 20,
            'mid_semester' => 20,
            'end_semester' => 60,
        ];

        $filename = 'assessment_scores_template_'.str_replace(' ', '_', $course->name).'_'.now()->format('Y-m-d').'.xlsx';

        return Excel::download(new AssessmentScoresTemplateExport($students, $courseInfo, $weights), $filename);
    }

    public function exportExcel(Request $request)
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:subjects,id',
            'class_id' => 'required|exists:college_classes,id',
            'cohort_id' => 'required|exists:cohorts,id',
            'semester_id' => 'required|exists:semesters,id',
            'scores' => 'required|array',
            'weights' => 'required|array',
        ]);

        $course = Subject::find($validated['course_id']);
        $class = CollegeClass::find($validated['class_id']);
        $cohort = Cohort::find($validated['cohort_id']);
        $semester = Semester::find($validated['semester_id']);

        $courseInfo = [
            'course' => $course->name,
            'programme' => $class->name,
            'cohort' => $cohort->name,
            'semester' => $semester->name,
        ];

        $filename = 'assessment_scores_'.str_replace(' ', '_', $course->name).'_'.now()->format('Y-m-d').'.xlsx';

        return Excel::download(new AssessmentScoresExport(collect($validated['scores']), $courseInfo, $validated['weights']), $filename);
    }

    public function importExcel(Request $request)
    {
        $validated = $request->validate([
            'import_file' => 'required|file|mimes:xlsx,xls|max:10240',
            'class_id' => 'required|exists:college_classes,id',
            'course_id' => [
                'required',
                Rule::exists('subjects', 'id')->where(fn ($query) => $query->where('college_class_id', $request->class_id)),
            ],
            'cohort_id' => 'required|exists:cohorts,id',
            'semester_id' => 'required|exists:semesters,id',
        ]);

        try {
            $import = new AssessmentScoresImport(
                $validated['course_id'],
                $validated['cohort_id'],
                $validated['semester_id'],
                Auth::id()
            );

            Excel::import($import, $request->file('import_file'));

            $previewData = $import->getValidatedData();
            $summary = $import->getSummary();
            $errors = $import->getErrors();

            if ($import->hasErrors()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Import validation failed. Please review errors.',
                    'errors' => $errors,
                    'summary' => $summary,
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'Import preview ready. Please review and confirm.',
                'preview_data' => $previewData,
                'summary' => $summary,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Import failed: '.$e->getMessage(),
            ], 500);
        }
    }

    public function confirmImport(Request $request)
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:subjects,id',
            'cohort_id' => 'required|exists:cohorts,id',
            'semester_id' => 'required|exists:semesters,id',
            'preview_data' => 'required|array',
            'assignment_weight' => 'required|numeric|min:0|max:100',
            'mid_semester_weight' => 'required|numeric|min:0|max:100',
            'end_semester_weight' => 'required|numeric|min:0|max:100',
            'assignment_count' => 'required|integer|min:3|max:5',
        ]);

        $savedCount = 0;
        $updatedCount = 0;

        foreach ($validated['preview_data'] as $data) {
            $scoreData = [
                'course_id' => $validated['course_id'],
                'student_id' => $data['student_id'],
                'cohort_id' => $validated['cohort_id'],
                'semester_id' => $validated['semester_id'],
                'assignment_1_score' => $data['assignment_1'] ?? null,
                'assignment_2_score' => $data['assignment_2'] ?? null,
                'assignment_3_score' => $data['assignment_3'] ?? null,
                'assignment_4_score' => $data['assignment_4'] ?? null,
                'assignment_5_score' => $data['assignment_5'] ?? null,
                'mid_semester_score' => $data['mid_semester'] ?? null,
                'end_semester_score' => $data['end_semester'] ?? null,
                'assignment_weight' => $validated['assignment_weight'],
                'mid_semester_weight' => $validated['mid_semester_weight'],
                'end_semester_weight' => $validated['end_semester_weight'],
                'assignment_count' => $validated['assignment_count'],
                'recorded_by' => Auth::id(),
            ];

            if (! empty($data['existing_id'])) {
                AssessmentScore::find($data['existing_id'])->update($scoreData);
                $updatedCount++;
            } else {
                AssessmentScore::create($scoreData);
                $savedCount++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Import completed: {$savedCount} new records, {$updatedCount} updated records",
            'saved_count' => $savedCount,
            'updated_count' => $updatedCount,
        ]);
    }
}
