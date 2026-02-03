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
            'academic_year_id' => 'nullable|exists:academic_years,id', // For storage context only, not query filtering
            'per_page' => 'nullable|integer|min:5|max:200',
            'page' => 'nullable|integer|min:1',
        ]);

        $perPage = $validated['per_page'] ?? 15;
        $page = $validated['page'] ?? 1;

        // Load students for the selected program and cohort with pagination
        $studentsQuery = Student::query()
            ->where('college_class_id', $validated['class_id'])
            ->where('cohort_id', $validated['cohort_id'])
            ->orderBy('student_id');

        $totalStudents = $studentsQuery->count();
        $students = $studentsQuery->paginate($perPage, ['*'], 'page', $page);

        if ($students->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No students found for the selected program and cohort. Please verify that students are assigned to this program and cohort.',
            ], 404);
        }

        // Get academic year for score storage context
        // Note: This academic year is used ONLY for storing/updating scores, NOT for filtering the scoresheet query
        // The scoresheet always shows all students regardless of academic year to allow comprehensive score entry
        $selectedAcademicYear = null;
        if ($validated['academic_year_id']) {
            $selectedAcademicYear = AcademicYear::find($validated['academic_year_id']);
        }

        if (! $selectedAcademicYear) {
            $selectedAcademicYear = AcademicYear::getCurrent();
        }

        if (! $selectedAcademicYear) {
            return response()->json([
                'success' => false,
                'message' => 'No academic year selected or available. Please select an academic year or set one as current.',
            ], 422);
        }

        $studentScores = [];
        $maxAssignmentCount = 3;

        foreach ($students as $student) {
            // Query for existing scores WITHOUT academic year filter to show comprehensive view
            // This allows users to see scores from any academic year for the same course/semester combination
            $existingScore = AssessmentScore::where([
                'course_id' => $validated['course_id'],
                'student_id' => $student->id,
                'cohort_id' => $validated['cohort_id'],
                'semester_id' => $validated['semester_id'],
                // Note: academic_year_id is NOT included in this query per user requirements
            ])->latest('updated_at')->first(); // Get most recent if multiple exist

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
            'academic_year' => [
                'id' => $selectedAcademicYear->id,
                'name' => $selectedAcademicYear->name,
            ],
            'pagination' => [
                'current_page' => $students->currentPage(),
                'last_page' => $students->lastPage(),
                'per_page' => $students->perPage(),
                'total' => $totalStudents,
                'from' => $students->firstItem(),
                'to' => $students->lastItem(),
            ],
            'message' => count($studentScores).' students loaded successfully (Page '.$students->currentPage().' of '.$students->lastPage().')',
        ]);
    }

    public function saveScores(Request $request)
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:subjects,id',
            'cohort_id' => 'required|exists:cohorts,id',
            'semester_id' => 'required|exists:semesters,id',
            'academic_year_id' => 'nullable|exists:academic_years,id', // Allow user-selected academic year
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

        // Get academic year for score storage (user-selected or current)
        $selectedAcademicYear = null;
        if ($validated['academic_year_id']) {
            $selectedAcademicYear = AcademicYear::find($validated['academic_year_id']);
        }

        if (! $selectedAcademicYear) {
            $selectedAcademicYear = AcademicYear::getCurrent();
        }

        if (! $selectedAcademicYear) {
            return response()->json([
                'success' => false,
                'message' => 'No academic year selected or available. Please select an academic year or set one as current.',
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

            // Use updateOrCreate to handle both insert and update cases
            $score = AssessmentScore::updateOrCreate(
                [
                    'course_id' => $validated['course_id'],
                    'student_id' => $studentScore['student_id'],
                    'cohort_id' => $validated['cohort_id'],
                    'semester_id' => $validated['semester_id'],
                    'academic_year_id' => $selectedAcademicYear->id, // Use selected academic year
                ],
                $data
            );

            if ($score->wasRecentlyCreated) {
                $savedCount++;
            } else {
                $updatedCount++;
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
            ->where('cohort_id', $validated['cohort_id'])
            ->orderBy('student_id')
            ->get();

        $course = Subject::find($validated['course_id']);
        $class = CollegeClass::find($validated['class_id']);
        $cohort = Cohort::find($validated['cohort_id']);
        $semester = Semester::find($validated['semester_id']);

        $courseInfo = [
            'course' => $course->name,
            'programme' => $class->name,
            'class' => $class->name, // For backwards compatibility with the export template
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
        // Handle JSON request payload
        if ($request->isJson()) {
            $data = $request->json()->all();
            $validated = validator($data, [
                'course_id' => 'required|exists:subjects,id',
                'class_id' => 'required|exists:college_classes,id',
                'cohort_id' => 'required|exists:cohorts,id',
                'semester_id' => 'required|exists:semesters,id',
                'academic_year_id' => 'nullable|exists:academic_years,id',
            ])->validate();
        } else {
            // Handle traditional form request
            $validated = $request->validate([
                'course_id' => 'required|exists:subjects,id',
                'class_id' => 'required|exists:college_classes,id',
                'cohort_id' => 'required|exists:cohorts,id',
                'semester_id' => 'required|exists:semesters,id',
                'academic_year_id' => 'nullable|exists:academic_years,id',
            ]);
        }

        // Fetch ALL students for the export (not paginated)
        $students = Student::where('college_class_id', $validated['class_id'])
            ->where('cohort_id', $validated['cohort_id'])
            ->orderBy('student_id')
            ->get();

        // Get weights from first existing score or defaults
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

        // Build scores array with ALL students
        $studentScores = [];
        foreach ($students as $student) {
            $existingScore = AssessmentScore::where([
                'course_id' => $validated['course_id'],
                'student_id' => $student->id,
                'cohort_id' => $validated['cohort_id'],
                'semester_id' => $validated['semester_id'],
            ])->latest('updated_at')->first();

            $studentScores[] = [
                'student_number' => $student->student_id,
                'student_name' => $student->name,
                'assignment_1' => $existingScore?->assignment_1_score,
                'assignment_2' => $existingScore?->assignment_2_score,
                'assignment_3' => $existingScore?->assignment_3_score,
                'assignment_average' => $existingScore ? round(collect([
                    $existingScore->assignment_1_score,
                    $existingScore->assignment_2_score,
                    $existingScore->assignment_3_score,
                ])->filter()->avg(), 2) : null,
                'assignment_weighted' => $existingScore ? round(collect([
                    $existingScore->assignment_1_score,
                    $existingScore->assignment_2_score,
                    $existingScore->assignment_3_score,
                ])->filter()->avg() * ($weights['assignment'] / 100), 2) : null,
                'mid_semester' => $existingScore?->mid_semester_score,
                'mid_weighted' => $existingScore?->mid_semester_score ? round($existingScore->mid_semester_score * ($weights['mid_semester'] / 100), 2) : null,
                'end_semester' => $existingScore?->end_semester_score,
                'end_weighted' => $existingScore?->end_semester_score ? round($existingScore->end_semester_score * ($weights['end_semester'] / 100), 2) : null,
                'total' => $existingScore?->total_score ?? 0,
                'grade' => $existingScore?->grade_letter ?? '',
            ];
        }

        $course = Subject::find($validated['course_id']);
        $class = CollegeClass::find($validated['class_id']);
        $cohort = Cohort::find($validated['cohort_id']);
        $semester = Semester::find($validated['semester_id']);

        $courseInfo = [
            'course' => $course->name,
            'programme' => $class->name,
            'class' => $class->name,
            'cohort' => $cohort->name,
            'semester' => $semester->name,
            'academic_year' => $cohort->academic_year ?? now()->year,
        ];

        $filename = 'assessment_scores_'.str_replace(' ', '_', $course->name).'_'.now()->format('Y-m-d').'.xlsx';

        return Excel::download(new AssessmentScoresExport(collect($studentScores), $courseInfo, $weights), $filename);
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
            'academic_year_id' => 'nullable|exists:academic_years,id', // Allow user-selected academic year
            'preview_data' => 'required|array',
            'assignment_weight' => 'required|numeric|min:0|max:100',
            'mid_semester_weight' => 'required|numeric|min:0|max:100',
            'end_semester_weight' => 'required|numeric|min:0|max:100',
            'assignment_count' => 'required|integer|min:3|max:5',
        ]);

        // Get academic year for score storage (user-selected or current)
        $selectedAcademicYear = null;
        if ($validated['academic_year_id']) {
            $selectedAcademicYear = AcademicYear::find($validated['academic_year_id']);
        }

        if (! $selectedAcademicYear) {
            $selectedAcademicYear = AcademicYear::getCurrent();
        }

        if (! $selectedAcademicYear) {
            return response()->json([
                'success' => false,
                'message' => 'No academic year selected or available. Please select an academic year or set one as current.',
            ], 422);
        }

        // Process in batches with database transaction for data integrity
        $batchSize = 50; // Process 50 records at a time
        $totalRecords = count($validated['preview_data']);
        $savedCount = 0;
        $updatedCount = 0;
        $skippedCount = 0;
        $errorCount = 0;

        try {
            // Process data in batches
            $batches = array_chunk($validated['preview_data'], $batchSize);

            foreach ($batches as $batchIndex => $batch) {
                DB::transaction(function () use ($batch, $validated, $selectedAcademicYear, &$savedCount, &$updatedCount, &$skippedCount, &$errorCount) {
                    foreach ($batch as $data) {
                        try {
                            // Separate unique keys from updateable data
                            $uniqueKeys = [
                                'course_id' => (int) $validated['course_id'],
                                'student_id' => (int) $data['student_id'],
                                'cohort_id' => (int) $validated['cohort_id'],
                                'semester_id' => (int) $validated['semester_id'],
                                'academic_year_id' => $selectedAcademicYear->id, // Use selected academic year
                            ];

                            $scoreData = [
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
                                'updated_at' => now(), // Force timestamp update for re-uploads
                            ];

                            $assessmentScore = AssessmentScore::updateOrCreate(
                                $uniqueKeys,
                                $scoreData
                            );

                            if ($assessmentScore->wasRecentlyCreated) {
                                $savedCount++;
                            } else {
                                $updatedCount++;
                            }

                        } catch (\Exception $e) {
                            $errorCount++;
                            \Log::error('Import record failed in batch', [
                                'student_id' => $data['student_id'] ?? 'unknown',
                                'error' => $e->getMessage(),
                                'batch_index' => $batchIndex ?? 0,
                                'academic_year_id' => $selectedAcademicYear->id,
                            ]);
                            // Don't throw here - let batch complete, just count errors
                        }
                    }
                });

                // Optional: Add small delay between batches for very large imports
                if ($totalRecords > 1000 && $batchIndex < count($batches) - 1) {
                    usleep(100000); // 0.1 second pause
                }
            }

        } catch (\Exception $e) {
            \Log::error('Import batch transaction failed', [
                'error' => $e->getMessage(),
                'course_id' => $validated['course_id'],
                'total_records' => $totalRecords,
                'academic_year_id' => $selectedAcademicYear->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Import failed due to database error. Please try again.',
                'error' => $e->getMessage(),
            ], 500);
        }

        // Calculate final counts
        $processedCount = $savedCount + $updatedCount;
        $skippedCount = $totalRecords - $processedCount - $errorCount;

        $message = 'Import completed successfully!';
        $summaryDetails = [];

        if ($savedCount > 0) {
            $summaryDetails[] = "{$savedCount} new records created";
        }
        if ($updatedCount > 0) {
            $summaryDetails[] = "{$updatedCount} records updated";
        }
        if ($errorCount > 0) {
            $summaryDetails[] = "{$errorCount} records failed";
        }
        if ($skippedCount > 0) {
            $summaryDetails[] = "{$skippedCount} records skipped";
        }

        if (! empty($summaryDetails)) {
            $message .= ' '.implode(', ', $summaryDetails).'.';
        }

        // Log comprehensive summary for admin reference
        \Log::info('Assessment Scores Import Summary', [
            'total_records' => $totalRecords,
            'processed_records' => $processedCount,
            'new_records' => $savedCount,
            'updated_records' => $updatedCount,
            'error_records' => $errorCount,
            'skipped_records' => $skippedCount,
            'batch_size' => $batchSize,
            'total_batches' => count($batches ?? []),
            'course_id' => $validated['course_id'],
            'cohort_id' => $validated['cohort_id'],
            'semester_id' => $validated['semester_id'],
            'academic_year_id' => $selectedAcademicYear->id,
            'import_time' => now()->toDateTimeString(),
        ]);

        return response()->json([
            'success' => true,
            'message' => $message,
            'saved_count' => $savedCount,
            'updated_count' => $updatedCount,
            'error_count' => $errorCount,
            'total_processed' => $processedCount,
            'total_records' => $totalRecords,
        ]);
    }
}
