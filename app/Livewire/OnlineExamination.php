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

            // Check for existing exam session
            $existingSession = ExamSession::where('exam_id', $this->exam->id)
                ->where('student_id', $this->user->id)
                ->first();

            if ($existingSession) {
                // Use the existing session
                $this->examSession = $existingSession;
            } else {
                // Create a new exam session with proper start and end times
                $examStartTime = Carbon::parse($this->exam->date); // Use the scheduled exam date from the exam
                $examEndTime = $examStartTime->copy()->addMinutes((int) $this->exam->duration);
                
                // Create a new session with the proper times
                $this->examSession = ExamSession::create([
                    'exam_id' => $this->exam->id,
                    'student_id' => $this->user->id,
                    'started_at' => $examStartTime,
                    'completed_at' => $examEndTime,
                ]);
                
                // Log the creation of a new exam session
                Log::info('New exam session created', [
                    'session_id' => $this->examSession->id,
                    'student_id' => $this->student->student_id,
                    'exam_start' => $examStartTime->toDateTimeString(),
                    'exam_end' => $examEndTime->toDateTimeString()
                ]);
            }

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
        try {
            // Get the exam's scheduled date
            $examDate = Carbon::parse($this->exam->date);
            
            // Get the actual start time from the exam schedule rather than the session start time
            $startedAt = $examDate;
            
            // Calculate the expected completion time based on the exam duration
            $examDuration = (int) $this->exam->duration;
            $extraTime = (int) $this->examSession->extra_time_minutes;
            $totalDuration = $examDuration + $extraTime;
            
            // Calculate proper end time based on the exam's scheduled date
            $adjustedCompletionTime = $examDate->copy()->addMinutes($totalDuration);
            
            // Set the values for the view
            $this->timerStart = $startedAt;
            $this->timerFinish = $adjustedCompletionTime;
            $this->startedAt = $startedAt->format('l, jS F Y h:i A');
            $this->estimatedEndTime = $adjustedCompletionTime->format('l, jS F Y h:i A');

            // Calculate remaining time in seconds
            $currentTime = Carbon::now();
            $remainingSeconds = 0;
            
            if ($currentTime->lt($adjustedCompletionTime)) {
                $remainingSeconds = $currentTime->diffInSeconds($adjustedCompletionTime);
            }

            // Convert to hours, minutes, seconds
            $this->hours = floor($remainingSeconds / 3600);
            $this->minutes = floor(($remainingSeconds % 3600) / 60);
            $this->seconds = $remainingSeconds % 60;

            // Log actual times for debugging
            Log::info('Timer values calculated', [
                'exam_id' => $this->exam->id,
                'exam_date' => $this->exam->date,
                'exam_duration' => $examDuration,
                'extra_time' => $extraTime,
                'start_time' => $startedAt->toDateTimeString(),
                'end_time' => $adjustedCompletionTime->toDateTimeString(),
                'current_time' => $currentTime->toDateTimeString(),
                'remaining_seconds' => $remainingSeconds
            ]);

            return $adjustedCompletionTime->toIso8601String();
        } catch (\Exception $e) {
            Log::error('Error calculating remaining time', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return now()->addHour()->toIso8601String();
        }
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
