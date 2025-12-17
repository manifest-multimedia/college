<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ExamResultsExport;
use App\Http\Controllers\Controller;
use App\Models\Cohort;
use App\Models\CollegeClass;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\Student;
use App\Services\ResultsService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ExamResultsController extends Controller
{
    protected $resultsService;

    public function __construct(ResultsService $resultsService)
    {
        $this->resultsService = $resultsService;
    }

    /**
     * Display the exam results page
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Check authorization
        $isLecturer = $user->hasRole('Lecturer') &&
                      ! $user->hasRole(['Super Admin', 'Administrator', 'admin', 'System']);

        // Get lecturer access mode
        $lecturerAccessMode = config('branding.theme_settings.lecturer_access_mode', 'exam_creator');

        // Query builder for exams
        $examsQuery = Exam::with('course');

        // If user is a lecturer, filter based on access mode
        if ($isLecturer) {
            if ($lecturerAccessMode === 'course_assignment') {
                $assignedCourseIds = $user->assignedCourses()->pluck('subjects.id')->toArray();

                if (empty($assignedCourseIds)) {
                    $examsQuery->whereRaw('1 = 0');
                } else {
                    $examsQuery->whereIn('course_id', $assignedCourseIds);
                }
            } else {
                $examsQuery->where('user_id', $user->id);
            }
        }

        $exams = $examsQuery->orderBy('created_at', 'desc')->get();
        $collegeClasses = CollegeClass::orderBy('name')->get();
        $cohorts = Cohort::where('is_active', true)->orderBy('name')->get();

        return view('admin.exam-results', compact('exams', 'collegeClasses', 'cohorts'));
    }

    /**
     * Get exam results via AJAX
     */
    public function getResults(Request $request)
    {
        // Increase execution time and memory limit for large exams
        set_time_limit(300); // 5 minutes
        ini_set('memory_limit', '512M'); // Increase memory limit

        try {
            $user = auth()->user();
            $examId = $request->input('exam_id');
            $search = $request->input('search', '');
            $collegeClassId = $request->input('college_class_id');
            $cohortId = $request->input('cohort_id');
            $perPage = $request->input('per_page', 15);
            $page = $request->input('page', 1);
            $sortField = $request->input('sort_field', 'score_percentage');
            $sortDirection = $request->input('sort_direction', 'desc');

            if (! $examId) {
                return response()->json(['error' => 'Exam ID is required'], 400);
            }

            $exam = Exam::with('course')->find($examId);
            if (! $exam) {
                return response()->json(['error' => 'Exam not found'], 404);
            }

            // Security check for lecturers
            $isLecturer = $user->hasRole('Lecturer') &&
                          ! $user->hasRole(['Super Admin', 'Administrator', 'admin', 'System']);

            if ($isLecturer) {
                $lecturerAccessMode = config('branding.theme_settings.lecturer_access_mode', 'exam_creator');
                $hasAccess = false;

                if ($lecturerAccessMode === 'course_assignment') {
                    $hasAccess = $user->isAssignedToCourse($exam->course_id);
                } else {
                    $hasAccess = ($exam->user_id === $user->id);
                }

                if (! $hasAccess) {
                    return response()->json(['error' => 'You do not have permission to view these results'], 403);
                }
            }

            // Questions per session
            $questionsPerSession = $exam->questions_per_session ?? $exam->questions()->count();

            // Base query for exam sessions - optimized to prevent memory exhaustion
            $query = ExamSession::where('exam_id', $examId)
                ->where(function ($q) {
                    $q->whereNotNull('completed_at')
                        ->orWhere('auto_submitted', true);
                })
                ->select('exam_sessions.*') // Only load exam_sessions columns
                ->with([
                    'student:id,name,email', // Only load necessary user fields
                    'exam:id,course_id,questions_per_session',
                    'exam.course:id,name',
                    'student.student:id,student_id,first_name,last_name,other_name,email,college_class_id', // Only necessary student fields
                    'student.student.collegeClass:id,name',
                ]);

            // Apply search filter
            if ($search) {
                $query->where(function ($query) use ($search) {
                    $query->whereHas('student', function ($q) use ($search) {
                        $q->where('name', 'like', '%'.$search.'%')
                            ->orWhere('email', 'like', '%'.$search.'%');
                    });

                    $query->orWhereHas('student.student', function ($q) use ($search) {
                        $q->where('student_id', 'like', '%'.$search.'%');
                    });
                });
            }

            // Apply class filter
            if ($collegeClassId) {
                $studentIds = Student::where('college_class_id', $collegeClassId)
                    ->join('users', 'students.email', '=', 'users.email')
                    ->pluck('users.id');

                $query->whereIn('student_id', $studentIds);
            }

            // Apply cohort filter
            if ($cohortId) {
                $studentIds = Student::where('cohort_id', $cohortId)
                    ->join('users', 'students.email', '=', 'users.email')
                    ->pluck('users.id');

                $query->whereIn('student_id', $studentIds);
            }

            // Get paginated results FIRST
            $examSessions = $query->paginate($perPage);

            // Calculate stats from paginated results (faster for filtered views)
            $totalStudents = $examSessions->total();
            $totalScorePercentage = 0;
            $passCount = 0;
            $highestScore = 0;
            $lowestScore = $totalStudents > 0 ? 100 : 0;

            // If we have filters, calculate stats from current page only (fast)
            // If no filters, use cache for full exam stats
            $useQuickStats = $search || $collegeClassId || $cohortId;

            if ($useQuickStats) {
                // Quick stats from current page results
                foreach ($examSessions as $session) {
                    $scoreData = $this->resultsService->calculateOnlineExamScore(
                        $session->load('responses.question.options'),
                        $questionsPerSession
                    );
                    $scorePercentage = $scoreData['percentage'];

                    $totalScorePercentage += $scorePercentage;
                    if ($scorePercentage >= 50) {
                        $passCount++;
                    }
                    $highestScore = max($highestScore, $scorePercentage);
                    $lowestScore = min($lowestScore, $scorePercentage);
                }

                $stats = [
                    'totalStudents' => $totalStudents,
                    'averageScore' => count($examSessions) > 0 ? round($totalScorePercentage / count($examSessions), 2) : 0,
                    'passRate' => count($examSessions) > 0 ? round(($passCount / count($examSessions)) * 100, 2) : 0,
                    'highestScore' => $highestScore,
                    'lowestScore' => $lowestScore,
                ];
            } else {
                // Full exam stats with caching
                $cacheKey = "exam_stats_{$examId}";
                $stats = cache()->remember($cacheKey, 300, function () use ($query, $questionsPerSession, $totalStudents) {
                    $totalScorePercentage = 0;
                    $passCount = 0;
                    $highestScore = 0;
                    $lowestScore = $totalStudents > 0 ? 100 : 0;

                    $statsQuery = clone $query;
                    $statsQuery->chunk(100, function ($sessions) use (&$totalScorePercentage, &$passCount, &$highestScore, &$lowestScore, $questionsPerSession) {
                        foreach ($sessions as $session) {
                            $scoreData = $this->resultsService->calculateOnlineExamScore(
                                $session->load('responses.question.options'),
                                $questionsPerSession
                            );
                            $scorePercentage = $scoreData['percentage'];

                            $totalScorePercentage += $scorePercentage;
                            if ($scorePercentage >= 50) {
                                $passCount++;
                            }
                            $highestScore = max($highestScore, $scorePercentage);
                            $lowestScore = min($lowestScore, $scorePercentage);
                        }
                    });

                    return [
                        'totalStudents' => $totalStudents,
                        'averageScore' => $totalStudents > 0 ? round($totalScorePercentage / $totalStudents, 2) : 0,
                        'passRate' => $totalStudents > 0 ? round(($passCount / $totalStudents) * 100, 2) : 0,
                        'highestScore' => $highestScore,
                        'lowestScore' => $lowestScore,
                    ];
                });

                $stats['totalStudents'] = $totalStudents; // Update with current total
            }

            // Process results for current page
            $results = [];
            foreach ($examSessions as $session) {
                // Use the already-loaded relationship instead of a separate query
                $user = $session->student; // This is the User model
                $student = $user ? $user->student : null; // This is the Student model via the relationship

                // Load responses only for this specific session to save memory
                $scoreData = $this->resultsService->calculateOnlineExamScore(
                    $session->load('responses.question.options'),
                    $questionsPerSession
                );

                $results[] = [
                    'session_id' => $session->id,
                    'student_id' => $student ? $student->student_id : 'N/A',
                    'name' => $student ? $student->name : ($user->name ?? 'N/A'), // Use Student's name (includes first, other, last)
                    'email' => $user->email ?? 'N/A',
                    'completed_at' => $session->completed_at ? $session->completed_at->format('Y-m-d H:i') : 'N/A',
                    'class' => $student && $student->collegeClass ? $student->collegeClass->name : 'N/A',
                    'course' => $session->exam->course->name ?? 'N/A',
                    'score' => $scoreData['correct_answers'].'/'.$questionsPerSession,
                    'total_marks' => $scoreData['total_marks'],
                    'obtained_marks' => $scoreData['obtained_marks'],
                    'answered' => $scoreData['total_answered'].'/'.$questionsPerSession,
                    'score_percentage' => $scoreData['percentage'],
                ];
            }

            // Sort results
            $results = $this->sortResults($results, $sortField, $sortDirection);

            return response()->json([
                'success' => true,
                'results' => $results,
                'stats' => $stats,
                'pagination' => [
                    'current_page' => $examSessions->currentPage(),
                    'last_page' => $examSessions->lastPage(),
                    'per_page' => $examSessions->perPage(),
                    'total' => $examSessions->total(),
                    'from' => $examSessions->firstItem(),
                    'to' => $examSessions->lastItem(),
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching exam results', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'exam_id' => $request->input('exam_id'),
            ]);

            return response()->json([
                'error' => 'Failed to fetch exam results: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Sort results array
     */
    protected function sortResults($results, $sortField, $sortDirection)
    {
        usort($results, function ($a, $b) use ($sortField, $sortDirection) {
            $fieldA = $a[$sortField] ?? '';
            $fieldB = $b[$sortField] ?? '';

            if (is_numeric($fieldA) && is_numeric($fieldB)) {
                $comparison = $fieldA <=> $fieldB;
            } else {
                $comparison = strcmp($fieldA, $fieldB);
            }

            return $sortDirection === 'asc' ? $comparison : -$comparison;
        });

        return $results;
    }

    /**
     * Export results to Excel
     */
    public function exportExcel(Request $request)
    {
        try {
            set_time_limit(300); // 5 minutes
            ini_set('memory_limit', '256M'); // Increase memory limit

            $examId = $request->input('exam_id');
            $collegeClassId = $request->input('college_class_id');

            $exam = Exam::with('course')->find($examId);
            if (! $exam) {
                return response()->json(['error' => 'Exam not found'], 404);
            }

            // Security check for lecturers
            $user = auth()->user();
            $isLecturer = $user->hasRole('Lecturer') &&
                          ! $user->hasRole(['Super Admin', 'Administrator', 'admin', 'System']);

            if ($isLecturer) {
                $lecturerAccessMode = config('branding.theme_settings.lecturer_access_mode', 'exam_creator');
                $hasAccess = false;

                if ($lecturerAccessMode === 'course_assignment') {
                    $hasAccess = $user->isAssignedToCourse($exam->course_id);
                } else {
                    $hasAccess = ($exam->user_id === $user->id);
                }

                if (! $hasAccess) {
                    return response()->json(['error' => 'You do not have permission to export these results'], 403);
                }
            }

            $sanitizedName = preg_replace('/[\/\\\\:*?"<>|]/', '-', $exam->course->name ?? 'unknown');
            $sanitizedName = str_replace(' ', '_', $sanitizedName);
            $fileName = 'exam_results_'.$sanitizedName.'_'.now()->format('Y-m-d').'.xlsx';

            return Excel::download(new ExamResultsExport($examId, $collegeClassId), $fileName);

        } catch (\Exception $e) {
            Log::error('Error exporting exam results to Excel', [
                'error' => $e->getMessage(),
                'exam_id' => $request->input('exam_id'),
            ]);

            return response()->json(['error' => 'Failed to export results'], 500);
        }
    }

    /**
     * Export results to PDF
     */
    public function exportPDF(Request $request)
    {
        try {
            set_time_limit(300); // 5 minutes
            ini_set('memory_limit', '256M'); // Increase memory limit

            $examId = $request->input('exam_id');
            $exam = Exam::with('course')->find($examId);

            if (! $exam) {
                return response()->json(['error' => 'Exam not found'], 404);
            }

            // Security check for lecturers
            $user = auth()->user();
            $isLecturer = $user->hasRole('Lecturer') &&
                          ! $user->hasRole(['Super Admin', 'Administrator', 'admin', 'System']);

            if ($isLecturer) {
                $lecturerAccessMode = config('branding.theme_settings.lecturer_access_mode', 'exam_creator');
                $hasAccess = false;

                if ($lecturerAccessMode === 'course_assignment') {
                    $hasAccess = $user->isAssignedToCourse($exam->course_id);
                } else {
                    $hasAccess = ($exam->user_id === $user->id);
                }

                if (! $hasAccess) {
                    return response()->json(['error' => 'You do not have permission to export these results'], 403);
                }
            }

            // Get all results
            $allResults = $this->getAllResults($request);
            $stats = $this->calculateStats($allResults);

            $pdf = PDF::loadView('exports.exam-results-pdf', [
                'results' => $allResults,
                'exam' => $exam,
                'stats' => $stats,
            ]);

            $sanitizedName = preg_replace('/[\/\\\\:*?"<>|]/', '-', $exam->course->name ?? 'unknown');
            $sanitizedName = str_replace(' ', '_', $sanitizedName);
            $fileName = 'exam_results_'.$sanitizedName.'_'.now()->format('Y-m-d').'.pdf';

            return response()->streamDownload(
                fn () => print ($pdf->output()),
                $fileName,
                [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
                ]
            );

        } catch (\Exception $e) {
            Log::error('Error exporting exam results to PDF', [
                'error' => $e->getMessage(),
                'exam_id' => $request->input('exam_id'),
            ]);

            return response()->json(['error' => 'Failed to export PDF'], 500);
        }
    }

    /**
     * Get all results without pagination for exports
     */
    protected function getAllResults(Request $request)
    {
        $examId = $request->input('exam_id');
        $search = $request->input('search', '');
        $collegeClassId = $request->input('college_class_id');
        $cohortId = $request->input('cohort_id');

        $exam = Exam::find($examId);
        if (! $exam) {
            return [];
        }

        $questionsPerSession = $exam->questions_per_session ?? $exam->questions()->count();

        $query = ExamSession::where('exam_id', $examId)
            ->where(function ($q) {
                $q->whereNotNull('completed_at')
                    ->orWhere('auto_submitted', true);
            })
            ->select('exam_sessions.*') // Only load exam_sessions columns
            ->with([
                'student:id,name,email', // Only load necessary user fields
                'exam:id,course_id,questions_per_session',
                'exam.course:id,name',
                'student.student:id,student_id,first_name,last_name,other_name,email,college_class_id',
                'student.student.collegeClass:id,name',
            ]);

        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->whereHas('student', function ($q) use ($search) {
                    $q->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                });

                $query->orWhereHas('student.student', function ($q) use ($search) {
                    $q->where('student_id', 'like', '%'.$search.'%');
                });
            });
        }

        if ($collegeClassId) {
            $studentIds = Student::where('college_class_id', $collegeClassId)
                ->join('users', 'students.email', '=', 'users.email')
                ->pluck('users.id');

            $query->whereIn('student_id', $studentIds);
        }

        if ($cohortId) {
            $studentIds = Student::where('cohort_id', $cohortId)
                ->join('users', 'students.email', '=', 'users.email')
                ->pluck('users.id');

            $query->whereIn('student_id', $studentIds);
        }

        $examSessions = $query->get();

        $results = [];
        foreach ($examSessions as $session) {
            // Use the already-loaded relationship instead of a separate query
            $user = $session->student; // This is the User model
            $student = $user ? $user->student : null; // This is the Student model via the relationship

            // Load responses only for this specific session to save memory
            $scoreData = $this->resultsService->calculateOnlineExamScore(
                $session->load('responses.question.options'),
                $questionsPerSession
            );

            $results[] = [
                'session_id' => $session->id,
                'student_id' => $student ? $student->student_id : 'N/A',
                'name' => $student ? $student->name : ($user->name ?? 'N/A'), // Use Student's name (includes first, other, last)
                'email' => $user->email ?? 'N/A',
                'completed_at' => $session->completed_at ? $session->completed_at->format('Y-m-d H:i') : 'N/A',
                'class' => $student && $student->collegeClass ? $student->collegeClass->name : 'N/A',
                'course' => $session->exam->course->name ?? 'N/A',
                'score' => $scoreData['correct_answers'].'/'.$questionsPerSession,
                'total_marks' => $scoreData['total_marks'],
                'obtained_marks' => $scoreData['obtained_marks'],
                'answered' => $scoreData['total_answered'].'/'.$questionsPerSession,
                'score_percentage' => $scoreData['percentage'],
            ];
        }

        return $results;
    }

    /**
     * Calculate statistics from results
     */
    protected function calculateStats($results)
    {
        $totalStudents = count($results);
        $totalScorePercentage = 0;
        $passCount = 0;
        $highestScore = 0;
        $lowestScore = $totalStudents > 0 ? 100 : 0;

        foreach ($results as $result) {
            $scorePercentage = $result['score_percentage'];

            $totalScorePercentage += $scorePercentage;
            if ($scorePercentage >= 50) {
                $passCount++;
            }

            if ($totalStudents > 0) {
                $highestScore = max($highestScore, $scorePercentage);
                $lowestScore = min($lowestScore, $scorePercentage);
            }
        }

        return [
            'totalStudents' => $totalStudents,
            'averageScore' => $totalStudents > 0 ? round($totalScorePercentage / $totalStudents, 2) : 0,
            'passRate' => $totalStudents > 0 ? round(($passCount / $totalStudents) * 100, 2) : 0,
            'highestScore' => $highestScore,
            'lowestScore' => $lowestScore,
        ];
    }
}
