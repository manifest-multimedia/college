<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\ExamSession;
use App\Models\Exam;
use App\Models\Student;
use App\Models\Response;
use App\Models\User;
use Illuminate\Support\Facades\Log;
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
    
    public function mount()
    {
        // Initialize component
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
            $student = Student::where('student_id', 'like', '%' . $this->student_id . '%')->first();
            
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
                        'email' => $student->email
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
                'student_id' => $this->student_id
            ]);
        }
    }
    
    public function loadStudentExams()
    {
        if (!$this->foundUser) return;
        
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
        if (!$this->foundUser || !$this->exam_id) return;
        
        // Get all sessions for this student and exam using user_id
        $this->studentExamSessions = ExamSession::where('student_id', $this->foundUser->id)
                                              ->where('exam_id', $this->exam_id)
                                              ->orderBy('created_at', 'desc')
                                              ->get();
    }
    
    public function loadSessionResponses()
    {
        if (!$this->session_id) return;
        
        // Get all responses for this session with questions and correct options
        $session = ExamSession::with(['responses.question.options' => function($query) {
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
                $this->totalQuestions = $exam->questions->count();
                $this->totalMarks = $this->totalQuestions * $exam->marks_per_question;
            }
            
            // Get marks_per_question value for use in closure
            $marksPerQuestion = $exam ? $exam->marks_per_question : 0;
            
            // Prepare responses data for display, including correct answers
            $this->sessionResponses = $session->responses->map(function($response) use ($marksPerQuestion) {
                $question = $response->question;
                $correctOption = $question->options->where('is_correct', true)->first();
                $selectedOption = $question->options->where('id', $response->selected_option)->first();
                
                $isCorrect = ($correctOption && $response->selected_option == $correctOption->id);
                $isAttempted = !is_null($response->selected_option);
                
                // Update metrics
                if ($isAttempted) {
                    $this->totalAttempted++;
                }
                
                if ($isCorrect) {
                    $this->totalCorrect++;
                    $this->obtainedMarks += $marksPerQuestion;
                }
                
                return [
                    'question_id' => $question->id,
                    'question_text' => $question->question_text,
                    'correct_option_id' => $correctOption ? $correctOption->id : null,
                    'correct_option_text' => $correctOption ? $correctOption->option_text : 'No correct answer defined',
                    'selected_option_id' => $response->selected_option,
                    'selected_option_text' => $selectedOption ? $selectedOption->option_text : 'No answer selected',
                    'is_correct' => $isCorrect,
                    'is_attempted' => $isAttempted,
                    'all_options' => $question->options->map(function($option) {
                        return [
                            'id' => $option->id,
                            'text' => $option->option_text,
                            'is_correct' => $option->is_correct
                        ];
                    })
                ];
            });
            
            // Calculate percentage
            if ($this->totalMarks > 0) {
                $this->scorePercentage = round(($this->obtainedMarks / $this->totalMarks) * 100, 2);
            }
            
            $this->responsesFound = count($this->sessionResponses) > 0;
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
            'exams' => $exams
        ]);
    }
}
