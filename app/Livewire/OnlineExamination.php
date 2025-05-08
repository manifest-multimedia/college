<?php

namespace App\Livewire;

use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\Student;
use App\Models\Response;
use Livewire\Component;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class OnlineExamination extends Component
{
    public $examPassword = '';
    public $student_id = null;
    public $exam;
    public $questions = [];
    public $responses = [];
    public $remainingTime;
    public $examStartTime;
    public $examSession;
    public $student_name;
    public $hours;
    public $minutes;
    public $seconds;
    public $user;
    public $student;
    public $student_index;
    public $startedAt;
    public $estimatedEndTime;

    public $timerStart;
    public $timerFinish;

    protected $listeners = ['submitExam'];

    public function mount($examPassword, $student_id = null)
    {
        $this->examPassword = $examPassword;
        $this->student_id = $student_id;

        $student = Student::find($student_id);
        if (!$student) {
            abort(404, 'Student not found');
        }

        $this->student_name = $student->first_name . ' ' . $student->last_name . ' ' . $student->other_name;
        $this->student = $student; // Save student instance for reuse
        $this->exam = Exam::with('course')->where('password', $this->examPassword)->first();

        if (!$this->exam) {
            abort(404, 'Exam not found');
        }

        // Initialize user and session
        $this->initializeExamSession();
        
        // Load existing responses from the database
        $this->loadResponses();
        
        // Then load questions with the loaded responses
        $this->loadQuestions();
    }

    protected function loadResponses()
    {
        // Load all responses for this exam session from the database
        $existingResponses = Response::where('exam_session_id', $this->examSession->id)->get();
        
        // Map responses to question IDs for easier access
        $this->responses = $existingResponses->mapWithKeys(function ($response) {
            return [$response->question_id => $response->selected_option];
        })->toArray();
        
        // Store in session as backup
        session()->put('responses', $this->responses);
    }

    public function initializeExamSession()
    {
        try {
            // Use existing or create a new user
            $this->user = User::firstOrCreate(
                ['email' => $this->student->email],
                ['name' => $this->student_name]
            );

            $this->student_index = $this->student->student_id;

            // Check or create exam session
            $this->examSession = ExamSession::firstOrCreate(
                [
                    'exam_id' => $this->exam->id,
                    'student_id' => $this->user->id,
                ],
                [
                    'started_at' => now(),
                    'completed_at' => now()->addMinutes((int) $this->exam->duration),
                ]
            );

            // Calculate exam end time including any extra time
            $examDuration = (int) $this->exam->duration;
            $extraTime = (int) $this->examSession->extra_time_minutes;
            $totalDuration = $examDuration + $extraTime;

            $this->examStartTime = Carbon::parse($this->examSession->started_at);
            $this->remainingTime = $this->calculateRemainingTime();

            // If extra time has been added, log it for monitoring
            if ($extraTime > 0) {
                Log::info('Extra time applied to exam session', [
                    'session_id' => $this->examSession->id,
                    'student_id' => $this->student->student_id,
                    'extra_minutes' => $extraTime,
                    'total_duration' => $totalDuration,
                    'new_end_time' => $this->examSession->adjustedCompletionTime
                ]);
            }
        } catch (\Throwable $th) {
            // Handle exceptions gracefully
            Log::error('Error initializing exam session', [
                'error' => $th->getMessage(),
                'student_id' => $this->student_id,
                'exam_id' => $this->exam->id
            ]);
            report($th);
        }
    }

    public function loadQuestions()
    {
        $examQuestions = $this->exam->questions()->inRandomOrder()->take(140)->get();

        $this->questions = $examQuestions->map(function ($question) {
            $response = Response::where('exam_session_id', $this->examSession->id)
                ->where('question_id', $question->id)
                ->first();

            $this->responses[$question->id] = $response ? $response->selected_option : null;

            return [
                'id' => $question->id,
                'question' => $question->question_text,
                'options' => $question->options()->get()->toArray(),
                'marks' => $question->mark,
            ];
        });
    }

    public function storeResponse($questionId, $answer)
    {
        try {
            $response = Response::updateOrCreate(
                [
                    'exam_session_id' => $this->examSession->id,
                    'question_id' => $questionId,
                    'student_id' => $this->user->id,
                ],
                ['selected_option' => $answer]
            );

            $this->responses[$questionId] = $answer;
            session()->put('responses', $this->responses);
        } catch (\Throwable $th) {
            // Handle exceptions gracefully
            report($th);
        }
    }

    public function submitExam()
    {
        try {
            $score = $this->calculateScore();
            $this->examSession->update([
                'completed_at' => now(),
                'score' => $score,
            ]);

            session()->flash('message', 'Exam submitted successfully.');
            return redirect()->route('take-exam');
        } catch (\Throwable $th) {
            // Handle submission errors
            report($th);
        }
    }

    public function calculateScore()
    {
        $score = 0;

        foreach ($this->responses as $questionId => $answer) {
            $question = $this->exam->questions()->find($questionId);
            if ($question && $answer == $question->correct_option) {
                $score += $question->mark;
            }
        }

        return $score;
    }

    public function calculateRemainingTime()
    {
        // Use the adjusted completion time (which includes extra time) from the model
        $adjustedCompletionTime = $this->examSession->adjustedCompletionTime;
        $currentTime = Carbon::now();

        // If the adjusted completion time is in the past, return 0
        if ($currentTime->gt($adjustedCompletionTime)) {
            return 0;
        }

        return $currentTime->diffInSeconds($adjustedCompletionTime);
    }

    public function getRemainingTime()
    {
        $startedAt = Carbon::parse($this->examSession->started_at);

        // Use the adjusted completion time instead of directly calculating from completed_at
        $adjustedCompletionTime = $this->examSession->adjustedCompletionTime;

        $this->timerStart = $startedAt;
        $this->timerFinish = $adjustedCompletionTime;

        $this->startedAt = $startedAt->format('l, jS F Y h:i A');
        $this->estimatedEndTime = $adjustedCompletionTime->format('l, jS F Y h:i A');

        // Calculate remaining time in seconds
        $remainingSeconds = $this->calculateRemainingTime();

        // Convert to hours, minutes, seconds
        $this->hours = floor($remainingSeconds / 3600);
        $this->minutes = floor(($remainingSeconds % 3600) / 60);
        $this->seconds = $remainingSeconds % 60;

        return $remainingSeconds;
    }

    public function render()
    {
        return view('livewire.online-examination', [
            'questions' => $this->questions,
            'exam' => $this->exam,
            'remainingTime' => $this->remainingTime,
        ]);
    }
}
