<?php

namespace App\Livewire\Admin;

use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\Response;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;

class ExamResponseTracker extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $student_id = '';

    public $exam_id = null;

    public $session_id = null;

    public $search = '';

    public $perPage = 10;

    // Track if we've found a valid student
    public $studentFound = false;

    // Store student details when found
    public $foundStudent = null;

    public $foundUser = null;

    // For displaying exam sessions for a selected student
    public $studentExamSessions = [];

    // For tracking a specific session's responses
    public $sessionResponses = [];

    // Score metrics
    public $totalQuestions = 0;

    public $totalAttempted = 0;

    public $totalCorrect = 0;

    public $totalMarks = 0;

    public $obtainedMarks = 0;

    public $scorePercentage = 0;

    // Track if responses are found
    public $responsesFound = false;

    // For storing device access logs
    public $deviceAccessLogs = [];

    // For direct linking with all needed parameters
    protected $queryString = ['student_id', 'exam_id', 'session_id'];

    public function mount()
    {
        // Check if student_id is provided in URL
        if (! empty($this->student_id) && strlen($this->student_id) >= 3) {
            $this->findStudent();

            // If exam_id is also provided, automatically load sessions
            if ($this->exam_id && $this->foundUser) {
                $this->loadExamSessions();

                // If session_id is also provided, automatically load responses
                if ($this->session_id) {
                    $this->loadSessionResponses();
                }
            }
        }
    }

    public function updatedStudentId()
    {
        // Reset related fields when student_id changes
        $this->reset(['exam_id', 'session_id', 'studentExamSessions', 'sessionResponses', 'studentFound', 'foundStudent', 'foundUser', 'responsesFound']);

        // Only look up student if we have at least 3 characters
        if (strlen($this->student_id) >= 3) {
            $this->findStudent();
        }
    }

    public function updatedExamId()
    {
        // Reset session when exam changes
        $this->reset(['session_id', 'sessionResponses', 'responsesFound']);

        // If we have a student and exam, find sessions
        if ($this->foundStudent && $this->foundUser && $this->exam_id) {
            $this->loadExamSessions();
        }
    }

    public function updatedSessionId()
    {
        // When session is selected, load responses
        if ($this->session_id) {
            $this->loadSessionResponses();
        } else {
            $this->reset(['sessionResponses', 'responsesFound']);
        }
    }

    public function findStudent()
    {
        try {
            // Find student by student_id (college ID)
            $student = Student::where('student_id', 'like', '%'.$this->student_id.'%')->first();

            if ($student) {
                $this->foundStudent = $student;
                $this->studentFound = true;

                // Find associated user account by email
                $user = User::where('email', $student->email)->first();
                if ($user) {
                    $this->foundUser = $user;
                    // Load exams this student has taken
                    $this->loadStudentExams();
                } else {
                    // Log warning that student has no user account
                    Log::warning('Student found but has no associated user account', [
                        'student_id' => $student->id,
                        'student_college_id' => $student->student_id,
                        'email' => $student->email,
                    ]);
                }
            } else {
                $this->studentFound = false;
                $this->foundStudent = null;
                $this->foundUser = null;
            }
        } catch (\Exception $e) {
            Log::error('Error finding student', [
                'error' => $e->getMessage(),
                'student_id' => $this->student_id,
            ]);
        }
    }

    public function loadStudentExams()
    {
        if (! $this->foundUser) {
            return;
        }

        // Get all exams the student has taken using user_id
        $examSessions = ExamSession::where('student_id', $this->foundUser->id)
            ->with('exam.course')
            ->get()
            ->groupBy('exam_id');

        // For the exam dropdown
        $examIds = $examSessions->keys();
        $exams = Exam::with('course')->whereIn('id', $examIds)->get();

        // Store the exams for the view
        $this->dispatch('updateExamsList', $exams);
    }

    public function loadExamSessions()
    {
        if (! $this->foundUser || ! $this->exam_id) {
            return;
        }

        // Get all sessions for this student and exam using user_id
        $this->studentExamSessions = ExamSession::where('student_id', $this->foundUser->id)
            ->where('exam_id', $this->exam_id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function loadSessionResponses()
    {
        if (! $this->session_id) {
            return;
        }

        try {
            // Get all responses for this session with questions and correct options
            $session = ExamSession::with(['responses.question.options' => function ($query) {
                $query->orderBy('id', 'asc');
            }])
                ->find($this->session_id);

            if ($session) {
                // Reset metrics
                $this->totalQuestions = 0;
                $this->totalAttempted = 0;
                $this->totalCorrect = 0;
                $this->totalMarks = 0;
                $this->obtainedMarks = 0;
                $this->scorePercentage = 0;

                // Get exam details for total questions and marks calculation
                $exam = Exam::find($session->exam_id);
                if ($exam) {
                    // Use questions_per_session if available, otherwise fall back to total questions
                    $questionsPerSession = $exam->questions_per_session ?? $exam->questions->count();
                    $this->totalQuestions = $questionsPerSession;

                    // Get all responses for this session
                    $responses = $session->responses;

                    // Create a collection of processed responses so we can sort/limit them
                    $processedResponses = collect();

                    // Process each response to calculate metrics
                    foreach ($responses as $response) {
                        $question = $response->question;
                        if (! $question) {
                            continue;
                        }

                        $correctOption = $question->options->where('is_correct', true)->first();
                        $selectedOption = $question->options->where('id', $response->selected_option)->first();

                        $isCorrect = ($correctOption && $response->selected_option == $correctOption->id);
                        $isAttempted = ! is_null($response->selected_option);

                        // Get question mark value, default to 1 if not specified
                        $questionMark = $question->mark ?? 1;

                        // Add to processed responses collection with relevant metrics
                        $processedResponses->push([
                            'response' => $response,
                            'question' => $question,
                            'correct_option' => $correctOption,
                            'selected_option' => $selectedOption,
                            'is_correct' => $isCorrect,
                            'is_attempted' => $isAttempted,
                            'mark_value' => $questionMark,
                        ]);
                    }

                    // Log the original counts for debugging
                    $originalCount = $processedResponses->count();
                    $originalAttempted = $processedResponses->where('is_attempted', true)->count();

                    // Only take the configured number of questions per session
                    // This ensures we don't count extra questions from shuffling
                    $limitedResponses = $processedResponses->take($questionsPerSession);

                    // Calculate metrics from the limited responses
                    $this->totalAttempted = $limitedResponses->where('is_attempted', true)->count();
                    // Use ResultsService for consistent score calculation across all locations
                    $resultsService = app(\App\Services\ResultsService::class);

                    // Get responses collection from limitedResponses for service
                    // Filter out null responses (unanswered questions) to ensure accurate score calculation
                    $responsesForService = $limitedResponses->pluck('response')->filter();

                    // Calculate scores using the service
                    $scoreData = $resultsService->calculateOnlineExamScore(
                        $session,
                        $questionsPerSession,
                        $responsesForService
                    );

                    // Extract calculated values
                    $this->totalCorrect = $scoreData['correct_answers'];
                    $this->obtainedMarks = $scoreData['obtained_marks'];
                    $this->totalMarks = $scoreData['total_marks'];

                    // Map the limited responses for display
                    $this->sessionResponses = $limitedResponses->map(function ($item) {
                        return [
                            'question_id' => $item['question']->id,
                            'question_text' => $item['question']->question_text,
                            'correct_option_id' => $item['correct_option'] ? $item['correct_option']->id : null,
                            'correct_option_text' => $item['correct_option'] ? $item['correct_option']->option_text : 'No correct answer defined',
                            'selected_option_id' => $item['response']->selected_option,
                            'selected_option_text' => $item['selected_option'] ? $item['selected_option']->option_text : 'No answer selected',
                            'is_correct' => $item['is_correct'],
                            'is_attempted' => $item['is_attempted'],
                            'mark' => $item['mark_value'],
                            'all_options' => $item['question']->options->map(function ($option) {
                                return [
                                    'id' => $option->id,
                                    'text' => $option->option_text,
                                    'is_correct' => $option->is_correct,
                                ];
                            }),
                        ];
                    });

                    // Log if we're limiting the displayed responses
                    if ($originalCount > $questionsPerSession) {
                        Log::info('Limiting response tracker responses', [
                            'session_id' => $this->session_id,
                            'exam_id' => $session->exam_id,
                            'original_count' => $originalCount,
                            'original_attempted' => $originalAttempted,
                            'limited_to' => $questionsPerSession,
                            'limited_attempted' => $this->totalAttempted,
                        ]);
                    }

                    // Use percentage from ResultsService
                    $this->scorePercentage = $scoreData['percentage'];

                    // Load device access logs for this session
                    $this->loadDeviceAccessLogs($session->id);
                }

                $this->responsesFound = count($this->sessionResponses) > 0;
            }
        } catch (\Exception $e) {
            Log::error('Error loading exam responses', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'session_id' => $this->session_id,
            ]);
        }
    }

    public function loadDeviceAccessLogs($sessionId)
    {
        try {
            // Get all device access logs for this exam session
            $logs = ExamSession::find($sessionId)
                ->deviceAccessLogs()
                ->orderBy('access_time', 'asc')
                ->get();

            // Transform the logs to extract useful browser and device information
            $this->deviceAccessLogs = $logs->map(function ($log) {
                $deviceInfo = [];

                // Parse device_info if it's JSON
                if (is_string($log->device_info)) {
                    try {
                        $parsedInfo = json_decode($log->device_info, true);
                        if (is_array($parsedInfo)) {
                            $deviceInfo = $parsedInfo;
                        } else {
                            $deviceInfo = ['raw' => $log->device_info];
                        }
                    } catch (\Exception $e) {
                        $deviceInfo = ['raw' => $log->device_info];
                    }
                } else {
                    $deviceInfo = ['raw' => (string) $log->device_info];
                }

                return [
                    'id' => $log->id,
                    'access_time' => $log->access_time,
                    'ip_address' => $log->ip_address,
                    'is_conflict' => $log->is_conflict,
                    'browser' => $deviceInfo['browser'] ?? 'Unknown',
                    'platform' => $deviceInfo['platform'] ?? 'Unknown',
                    'device_type' => $deviceInfo['device_type'] ?? 'Unknown',
                    'user_agent' => $deviceInfo['user_agent'] ?? '',
                    'screen_resolution' => $deviceInfo['screen_resolution'] ?? 'Unknown',
                    'timezone' => $deviceInfo['timezone'] ?? 'Unknown',
                    'device_identifier' => $deviceInfo['device_identifier'] ?? 'Unknown',
                    'all_info' => $deviceInfo,
                ];
            })->toArray();
        } catch (\Exception $e) {
            Log::error('Error loading device access logs', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'session_id' => $sessionId,
            ]);
            $this->deviceAccessLogs = [];
        }
    }

    public function render()
    {
        $exams = [];

        if ($this->studentFound && $this->foundUser) {
            // Get all exams the student has taken using user_id
            $studentExamIds = ExamSession::where('student_id', $this->foundUser->id)
                ->pluck('exam_id')
                ->unique();

            $exams = Exam::whereIn('id', $studentExamIds)->get();
        }

        return view('livewire.admin.exam-response-tracker', [
            'exams' => $exams,
        ]);
    }
}
