<?php

namespace App\Livewire\Admin;

use App\Exports\ExamResultsExport;
use App\Models\CollegeClass;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class ExamResultsComponent extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // Search and filter parameters
    public $exam_id = null;

    public $search = '';

    public $college_class_id = null;

    public $cohort_id = null;

    public $perPage = 15;

    public $sortField = 'score_percentage';

    public $sortDirection = 'desc';

    // Results data
    public $examResults = [];

    public $hasResults = false;

    // Stats
    public $totalStudents = 0;

    public $averageScore = 0;

    public $passRate = 0;

    public $highestScore = 0;

    public $lowestScore = 0;

    // User role tracking
    protected $currentUser;

    protected $isLecturer = false;

    protected $authorized = false;

    protected $queryString = [
        'exam_id' => ['except' => null],
        'search' => ['except' => ''],
        'college_class_id' => ['except' => null],
        'cohort_id' => ['except' => null],
        'perPage' => ['except' => 15],
    ];

    public function mount()
    {
        // Check if user is authorized to access this component
        $this->currentUser = auth()->user();

        if (! $this->currentUser) {
            return $this->redirectToLogin();
        }

        // Check if user has one of the allowed roles
        $allowedRoles = ['System', 'Administrator', 'Super Admin', 'Lecturer'];
        $authorized = false;

        foreach ($allowedRoles as $role) {
            if ($this->currentUser->hasRole($role)) {
                $authorized = true;

                if ($role === 'Lecturer') {
                    $this->isLecturer = true;
                }

                break;
            }
        }

        if (! $authorized) {
            return $this->redirectToUnauthorized();
        }

        $this->authorized = true;

        // Load results if exam_id is provided in URL
        if ($this->exam_id) {
            $this->loadExamResults();
        }
    }

    /**
     * Redirect unauthorized users to login page
     */
    protected function redirectToLogin()
    {
        // Log unauthorized access attempt
        Log::warning('Unauthorized access attempt to ExamResultsComponent', [
            'ip' => request()->ip(),
        ]);

        // Redirect to login
        return redirect()->route('login');
    }

    /**
     * Redirect unauthorized users to 403 page
     */
    protected function redirectToUnauthorized()
    {
        // Log unauthorized access attempt
        Log::warning('Forbidden access attempt to ExamResultsComponent', [
            'user_id' => $this->currentUser->id,
            'email' => $this->currentUser->email,
            'ip' => request()->ip(),
        ]);

        // Redirect to 403 forbidden page
        abort(403, 'You do not have permission to access exam results.');
    }

    public function updatedExamId()
    {
        $this->resetPage();
        $this->loadExamResults();
    }

    public function updatedSearch()
    {
        $this->resetPage();
        $this->loadExamResults();
    }

    public function updatedCollegeClassId()
    {
        $this->resetPage();
        $this->loadExamResults();
    }

    public function updatedCohortId()
    {
        $this->resetPage();
        $this->loadExamResults();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
        $this->loadExamResults();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function loadExamResults()
    {
        if (! $this->exam_id) {
            return;
        }

        try {
            $exam = Exam::find($this->exam_id);
            if (! $exam) {
                $this->hasResults = false;

                return;
            }

            // Questions per session (configured or default)
            $questionsPerSession = $exam->questions_per_session ?? $exam->questions()->count();

            // Base query for exam sessions
            $query = ExamSession::where('exam_id', $this->exam_id)
                ->whereNotNull('completed_at')
                ->with([
                    'student', // This is actually User model
                    'exam.course',
                    'responses.question.options',
                    'student.student', // Load the Student model via User -> Student relationship
                ]);

            // Apply filters
            if ($this->search) {
                $searchTerm = $this->search;
                $query->where(function ($query) use ($searchTerm) {
                    // Search in user table (name and email)
                    $query->whereHas('student', function ($q) use ($searchTerm) {
                        $q->where('name', 'like', '%'.$searchTerm.'%')
                            ->orWhere('email', 'like', '%'.$searchTerm.'%');
                    });

                    // Also search by student_id in the students table through the relationship
                    $query->orWhereHas('student.student', function ($q) use ($searchTerm) {
                        $q->where('student_id', 'like', '%'.$searchTerm.'%');
                    });
                });
            }

            if ($this->college_class_id) {
                // Get students in this college class
                $studentIds = Student::where('college_class_id', $this->college_class_id)
                    ->join('users', 'students.email', '=', 'users.email')
                    ->pluck('users.id');

                $query->whereIn('student_id', $studentIds);
            }

            if ($this->cohort_id) {
                // Get students in this cohort
                $studentIds = Student::where('cohort_id', $this->cohort_id)
                    ->join('users', 'students.email', '=', 'users.email')
                    ->pluck('users.id');

                $query->whereIn('student_id', $studentIds);
            }

            // Load results
            $examSessions = $query->paginate($this->perPage);
            $this->hasResults = $examSessions->count() > 0;

            // Reset stats
            $this->totalStudents = $examSessions->total();
            $totalScorePercentage = 0;
            $passCount = 0;
            $this->highestScore = 0;
            $this->lowestScore = $this->totalStudents > 0 ? 100 : 0;

            // Process results for display
            $this->examResults = [];

            foreach ($examSessions as $session) {
                // Find the student record using the user email
                $userEmail = $session->student->email ?? null;
                $student = $userEmail ? Student::where('email', $userEmail)->first() : null;

                // Reset counters for each session
                $totalQuestions = 0;
                $totalAttempted = 0;
                $totalCorrect = 0;
                $totalMarks = 0;
                $obtainedMarks = 0;
                $scorePercentage = 0;

                // Get all responses with their questions and options
                $responses = $session->responses;

                // Check if this session has defined session questions for proper ordering
                $sessionQuestions = $session->sessionQuestions()->with('question.options')->get();
                $hasSessionQuestions = $sessionQuestions->isNotEmpty();

                // Create a collection of processed responses so we can sort/limit them
                $processedResponses = collect();

                if ($hasSessionQuestions) {
                    // Use session question order to ensure consistent results calculation
                    foreach ($sessionQuestions as $sessionQuestion) {
                        $question = $sessionQuestion->question;
                        if (! $question) {
                            continue;
                        }

                        // Find the response for this specific session question
                        $response = $responses->where('question_id', $question->id)->first();

                        // Find the correct option
                        $correctOption = $question->options->where('is_correct', true)->first();

                        // Question mark value (default to 1 if not specified)
                        $questionMark = $question->mark ?? 1;

                        // Check if the answer is correct (only if there was a response)
                        $isCorrect = ($response && $correctOption && $response->selected_option == $correctOption->id);
                        $isAttempted = ($response && ! is_null($response->selected_option));

                        // Add to processed responses collection with relevant metrics
                        $processedResponses->push([
                            'response' => $response,
                            'question' => $question,
                            'session_question' => $sessionQuestion,
                            'is_correct' => $isCorrect,
                            'is_attempted' => $isAttempted,
                            'mark_value' => $questionMark,
                        ]);
                    }
                } else {
                    // Fallback to original approach for backward compatibility
                    foreach ($responses as $response) {
                        $question = $response->question;
                        if (! $question) {
                            continue;
                        }

                        // Find the correct option
                        $correctOption = $question->options->where('is_correct', true)->first();

                        // Question mark value (default to 1 if not specified)
                        $questionMark = $question->mark ?? 1;

                        // Check if the answer is correct
                        $isCorrect = ($correctOption && $response->selected_option == $correctOption->id);
                        $isAttempted = ! is_null($response->selected_option);

                        // Add to processed responses collection with relevant metrics
                        $processedResponses->push([
                            'response' => $response,
                            'question' => $question,
                            'session_question' => null,
                            'is_correct' => $isCorrect,
                            'is_attempted' => $isAttempted,
                            'mark_value' => $questionMark,
                        ]);
                    }
                }

                // Log the original counts for debugging
                $originalAttempted = $processedResponses->where('is_attempted', true)->count();

                // For session-based questions, we already have the correct number
                // For legacy questions, limit to questionsPerSession
                $limitedResponses = $hasSessionQuestions ? $processedResponses : $processedResponses->take($questionsPerSession);

                // Use ResultsService for consistent score calculation across all locations
                $resultsService = app(\App\Services\ResultsService::class);

                // Get responses collection from limitedResponses for service
                // Filter out null responses (unanswered questions) to ensure accurate score calculation
                $responsesForService = $limitedResponses->pluck('response')->filter()->values();

                // Debug logging for score discrepancy investigation
                Log::info('ExamResultsComponent score calculation', [
                    'session_id' => $session->id,
                    'student_name' => $session->student->name ?? 'Unknown',
                    'questions_per_session' => $questionsPerSession,
                    'limited_responses_count' => $limitedResponses->count(),
                    'responses_for_service_count' => $responsesForService->count(),
                    'has_session_questions' => $hasSessionQuestions,
                ]);

                // Calculate scores using the service
                $scoreData = $resultsService->calculateOnlineExamScore(
                    $session,
                    $questionsPerSession,
                    $responsesForService
                );

                // Extract calculated values
                $totalQuestions = $limitedResponses->count();
                $totalAttempted = $limitedResponses->where('is_attempted', true)->count();
                $totalCorrect = $scoreData['correct_answers'];
                $obtainedMarks = $scoreData['obtained_marks'];
                $totalMarks = $scoreData['total_marks'];
                $scorePercentage = $scoreData['percentage'];

                // Log the final calculated values
                Log::info('ExamResultsComponent calculated values', [
                    'session_id' => $session->id,
                    'total_correct' => $totalCorrect,
                    'obtained_marks' => $obtainedMarks,
                    'total_marks' => $totalMarks,
                    'score_percentage' => $scorePercentage,
                ]);

                // Track stats
                $totalScorePercentage += $scorePercentage;
                if ($scorePercentage >= 50) {
                    $passCount++;
                } // 50% is passing
                if ($this->totalStudents > 0) {
                    $this->highestScore = max($this->highestScore, $scorePercentage);
                    $this->lowestScore = min($this->lowestScore, $scorePercentage);
                }

                // Log if we're limiting the displayed responses for troubleshooting
                if ($originalAttempted > $questionsPerSession) {
                    Log::info('Limiting displayed responses for student', [
                        'session_id' => $session->id,
                        'student_name' => $session->student->name ?? 'Unknown',
                        'original_attempted' => $originalAttempted,
                        'limited_to' => $totalAttempted,
                        'questions_per_session' => $questionsPerSession,
                    ]);
                }

                // Add to results
                $this->examResults[] = [
                    'session_id' => $session->id,
                    'student_id' => $student ? $student->student_id : 'N/A',
                    'name' => $session->student->name ?? 'N/A',
                    'email' => $session->student->email ?? 'N/A',
                    'completed_at' => $session->completed_at->format('Y-m-d H:i'),
                    'class' => $student && $student->collegeClass ? $student->collegeClass->name : 'N/A',
                    'course' => $session->exam->course->name ?? 'N/A',
                    'score' => $totalCorrect.'/'.$questionsPerSession,
                    'total_marks' => $totalMarks,
                    'obtained_marks' => $obtainedMarks,
                    'answered' => min($totalAttempted, $questionsPerSession).'/'.$questionsPerSession,
                    'score_percentage' => $scorePercentage,
                ];
            }

            // Calculate overall stats
            if ($this->totalStudents > 0) {
                $this->averageScore = round($totalScorePercentage / $this->totalStudents, 2);
                $this->passRate = round(($passCount / $this->totalStudents) * 100, 2);
            }

            // Sort results
            $this->sortResults();

        } catch (\Exception $e) {
            Log::error('Error loading exam results', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'exam_id' => $this->exam_id,
            ]);

            $this->hasResults = false;
        }
    }

    protected function sortResults()
    {
        // Sort the results based on the chosen field and direction
        usort($this->examResults, function ($a, $b) {
            $fieldA = $a[$this->sortField] ?? '';
            $fieldB = $b[$this->sortField] ?? '';

            if (is_numeric($fieldA) && is_numeric($fieldB)) {
                $comparison = $fieldA <=> $fieldB;
            } else {
                $comparison = strcmp($fieldA, $fieldB);
            }

            return $this->sortDirection === 'asc' ? $comparison : -$comparison;
        });
    }

    public function exportToExcel()
    {
        try {
            $exam = Exam::find($this->exam_id);

            // Properly sanitize the filename to remove any invalid characters
            $sanitizedName = preg_replace('/[\/\\\\:*?"<>|]/', '-', $exam->course->name ?? 'unknown');
            $sanitizedName = str_replace(' ', '_', $sanitizedName);

            $fileName = 'exam_results_'.$sanitizedName.'_'.now()->format('Y-m-d').'.xlsx';

            return Excel::download(new ExamResultsExport(
                $this->exam_id,
                $this->college_class_id
            ), $fileName);
        } catch (\Exception $e) {
            Log::error('Error exporting exam results to Excel', [
                'error' => $e->getMessage(),
                'exam_id' => $this->exam_id,
            ]);

            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Failed to export results: '.$e->getMessage(),
            ]);
        }
    }

    public function exportToPDF()
    {
        try {
            $exam = Exam::with('course')->find($this->exam_id);
            if (! $exam) {
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'Exam not found',
                ]);

                return;
            }

            // Fetch all results directly instead of using the paginated data
            $allResults = $this->getAllResults();

            // Calculate statistics from all results
            $stats = $this->calculateStatsFromResults($allResults);

            $pdf = PDF::loadView('exports.exam-results-pdf', [
                'results' => $allResults,
                'exam' => $exam,
                'stats' => $stats,
            ]);

            // Properly sanitize the filename to remove any invalid characters
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
                'exam_id' => $this->exam_id,
            ]);

            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Failed to export results: '.$e->getMessage(),
            ]);
        }
    }

    /**
     * Get all results without pagination for exports
     */
    protected function getAllResults()
    {
        if (! $this->exam_id) {
            return [];
        }

        try {
            $exam = Exam::find($this->exam_id);
            if (! $exam) {
                return [];
            }

            // Questions per session (configured or default)
            $questionsPerSession = $exam->questions_per_session ?? $exam->questions()->count();

            // Base query for exam sessions
            $query = ExamSession::where('exam_id', $this->exam_id)
                ->whereNotNull('completed_at')
                ->with([
                    'student', // This is actually User model
                    'exam.course',
                    'responses.question.options',
                    'student.student', // Load the Student model via User -> Student relationship
                ]);

            // Apply filters
            if ($this->search) {
                $searchTerm = $this->search;
                $query->where(function ($query) use ($searchTerm) {
                    // Search in user table (name and email)
                    $query->whereHas('student', function ($q) use ($searchTerm) {
                        $q->where('name', 'like', '%'.$searchTerm.'%')
                            ->orWhere('email', 'like', '%'.$searchTerm.'%');
                    });

                    // Also search by student_id in the students table through the relationship
                    $query->orWhereHas('student.student', function ($q) use ($searchTerm) {
                        $q->where('student_id', 'like', '%'.$searchTerm.'%');
                    });
                });
            }

            if ($this->college_class_id) {
                // Get students in this college class
                $studentIds = Student::where('college_class_id', $this->college_class_id)
                    ->join('users', 'students.email', '=', 'users.email')
                    ->pluck('users.id');

                $query->whereIn('student_id', $studentIds);
            }

            if ($this->cohort_id) {
                // Get students in this cohort
                $studentIds = Student::where('cohort_id', $this->cohort_id)
                    ->join('users', 'students.email', '=', 'users.email')
                    ->pluck('users.id');

                $query->whereIn('student_id', $studentIds);
            }

            // Get all results (no pagination)
            $examSessions = $query->get();

            // Process results for display
            $results = [];

            foreach ($examSessions as $session) {
                // Find the student record using the user email
                $userEmail = $session->student->email ?? null;
                $student = $userEmail ? Student::where('email', $userEmail)->first() : null;

                // Create a collection of processed responses so we can sort/limit them
                $processedResponses = collect();

                // Get all responses with their questions and options
                $responses = $session->responses;

                // Process each response to calculate metrics
                foreach ($responses as $response) {
                    $question = $response->question;
                    if (! $question) {
                        continue;
                    }

                    // Find the correct option
                    $correctOption = $question->options->where('is_correct', true)->first();

                    // Question mark value (default to 1 if not specified)
                    $questionMark = $question->mark ?? 1;

                    // Check if the answer is correct
                    $isCorrect = ($correctOption && $response->selected_option == $correctOption->id);
                    $isAttempted = ! is_null($response->selected_option);

                    // Add to processed responses collection with relevant metrics
                    $processedResponses->push([
                        'response' => $response,
                        'is_correct' => $isCorrect,
                        'is_attempted' => $isAttempted,
                        'mark_value' => $questionMark,
                    ]);
                }

                // Log the original counts for debugging
                $originalAttempted = $processedResponses->where('is_attempted', true)->count();

                // Only take the configured number of questions per session
                // This ensures we don't count extra questions from shuffling
                $limitedResponses = $processedResponses->take($questionsPerSession);

                // Now calculate the metrics from the limited responses
                $totalQuestions = $limitedResponses->count(); // This should match questionsPerSession
                $totalAttempted = $limitedResponses->where('is_attempted', true)->count();
                $totalCorrect = $limitedResponses->where('is_correct', true)->count();
                $totalMarks = $limitedResponses->sum('mark_value');
                $obtainedMarks = $limitedResponses->where('is_correct', true)->sum('mark_value');

                // Calculate percentage (prevent division by zero)
                $scorePercentage = $totalMarks > 0 ? round(($obtainedMarks / $totalMarks) * 100, 2) : 0;

                // Log if we're limiting the displayed responses for troubleshooting
                if ($originalAttempted > $questionsPerSession) {
                    Log::info('Limiting exported responses for student', [
                        'session_id' => $session->id,
                        'student_name' => $session->student->name ?? 'Unknown',
                        'original_attempted' => $originalAttempted,
                        'limited_to' => $totalAttempted,
                        'questions_per_session' => $questionsPerSession,
                    ]);
                }

                // Add to results
                $results[] = [
                    'session_id' => $session->id,
                    'student_id' => $student ? $student->student_id : 'N/A',
                    'name' => $session->student->name ?? 'N/A',
                    'email' => $session->student->email ?? 'N/A',
                    'completed_at' => $session->completed_at->format('Y-m-d H:i'),
                    'class' => $student && $student->collegeClass ? $student->collegeClass->name : 'N/A',
                    'course' => $session->exam->course->name ?? 'N/A',
                    'score' => $totalCorrect.'/'.$questionsPerSession,
                    'total_marks' => $totalMarks,
                    'obtained_marks' => $obtainedMarks,
                    'answered' => min($totalAttempted, $questionsPerSession).'/'.$questionsPerSession,
                    'score_percentage' => $scorePercentage,
                ];
            }

            // Sort results using the same sort criteria
            usort($results, function ($a, $b) {
                $fieldA = $a[$this->sortField] ?? '';
                $fieldB = $b[$this->sortField] ?? '';

                if (is_numeric($fieldA) && is_numeric($fieldB)) {
                    $comparison = $fieldA <=> $fieldB;
                } else {
                    $comparison = strcmp($fieldA, $fieldB);
                }

                return $this->sortDirection === 'asc' ? $comparison : -$comparison;
            });

            return $results;

        } catch (\Exception $e) {
            Log::error('Error getting all results for export', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'exam_id' => $this->exam_id,
            ]);

            return [];
        }
    }

    /**
     * Calculate statistics from provided results array
     */
    protected function calculateStatsFromResults($results)
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
            } // 50% is passing

            if ($totalStudents > 0) {
                $highestScore = max($highestScore, $scorePercentage);
                $lowestScore = min($lowestScore, $scorePercentage);
            }
        }

        $averageScore = $totalStudents > 0 ? round($totalScorePercentage / $totalStudents, 2) : 0;
        $passRate = $totalStudents > 0 ? round(($passCount / $totalStudents) * 100, 2) : 0;

        return [
            'totalStudents' => $totalStudents,
            'averageScore' => $averageScore,
            'passRate' => $passRate,
            'highestScore' => $highestScore,
            'lowestScore' => $lowestScore,
        ];
    }

    public function render()
    {
        // Query builder for exams
        $examsQuery = Exam::with('course');

        // If user is a lecturer, only show their exams
        if ($this->isLecturer && $this->currentUser) {
            $examsQuery->where('user_id', $this->currentUser->id);
        }

        // Get the exams based on the filter
        $exams = $examsQuery->orderBy('created_at', 'desc')->get();

        // Generate paginated data if we have an exam selected
        $examSessions = collect([]);
        if ($this->exam_id) {
            // Build base query for exam sessions
            $query = ExamSession::where('exam_id', $this->exam_id)
                ->whereNotNull('completed_at')
                ->with([
                    'student',
                    'exam.course',
                    'responses.question.options',
                    'student.student',
                ]);

            // Apply filters
            if ($this->search) {
                $searchTerm = $this->search;
                $query->where(function ($query) use ($searchTerm) {
                    $query->whereHas('student', function ($q) use ($searchTerm) {
                        $q->where('name', 'like', '%'.$searchTerm.'%')
                            ->orWhere('email', 'like', '%'.$searchTerm.'%');
                    });

                    $query->orWhereHas('student.student', function ($q) use ($searchTerm) {
                        $q->where('student_id', 'like', '%'.$searchTerm.'%');
                    });
                });
            }

            if ($this->college_class_id) {
                $studentIds = Student::where('college_class_id', $this->college_class_id)
                    ->join('users', 'students.email', '=', 'users.email')
                    ->pluck('users.id');

                $query->whereIn('student_id', $studentIds);
            }

            if ($this->cohort_id) {
                $studentIds = Student::where('cohort_id', $this->cohort_id)
                    ->join('users', 'students.email', '=', 'users.email')
                    ->pluck('users.id');

                $query->whereIn('student_id', $studentIds);
            }

            // Get paginated results
            $examSessions = $query->paginate($this->perPage);

            // Load results if needed
            if (! $this->hasResults && $examSessions->count() > 0) {
                $this->loadExamResults();
            }
        }

        return view('livewire.admin.exam-results-component', [
            'exams' => $exams,
            'collegeClasses' => CollegeClass::orderBy('name')->get(),
            'cohorts' => \App\Models\Cohort::where('is_active', true)->orderBy('name')->get(),
            'paginatedSessions' => $examSessions,
        ]);
    }
}
