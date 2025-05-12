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

    public $examExpired = false;
    public $timeExpiredAt = null;

    protected $listeners = ['submitExam', 'examTimeExpired'];

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
                
                Log::info('Using existing exam session', [
                    'session_id' => $this->examSession->id,
                    'student_id' => $this->student->student_id,
                    'start_time' => $this->examSession->started_at->toDateTimeString(),
                    'end_time' => $this->examSession->completed_at->toDateTimeString()
                ]);
            } else {
                // Create a new exam session with current time as start time
                $currentTime = now();
                $examEndTime = $currentTime->copy()->addMinutes((int) $this->exam->duration);
                
                // Create a new session with the current time
                $this->examSession = ExamSession::create([
                    'exam_id' => $this->exam->id,
                    'student_id' => $this->user->id,
                    'started_at' => $currentTime,
                    'completed_at' => $examEndTime,
                ]);
                
                // Log the creation of a new exam session
                Log::info('New exam session created', [
                    'session_id' => $this->examSession->id,
                    'student_id' => $this->student->student_id,
                    'exam_start' => $currentTime->toDateTimeString(),
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
            // TEMPORARY CHANGE (May 12, 2025): Removed time expiration restrictions to allow students 
            // to save their answers at all times during ongoing exams. This bypasses the normal checks
            // for exam expiration to ensure students can submit even after the timer ends.
            // TODO: Restore proper time restrictions after current exam period.
            
            /* Original code commented out:
            // Check if the exam is expired but has extra time
            $hasExtraTime = $this->examSession && $this->examSession->extra_time_minutes > 0;
            $isExpired = $this->isExamExpired();
            
            // Don't process responses if exam is expired and has no extra time
            if ($isExpired && !$hasExtraTime) {
                Log::info('Response not saved - exam is expired without extra time', [
                    'session_id' => $this->examSession->id,
                    'question_id' => $questionId,
                    'student_id' => $this->user->id
                ]);
                return;
            }
            */
            
            // Log the response being saved
            Log::info('Saving exam response', [
                'session_id' => $this->examSession->id,
                'question_id' => $questionId,
                'answer' => $answer,
                'student_id' => $this->user->id,
                'exam_expired' => $this->isExamExpired(),
                'has_extra_time' => $this->examSession && $this->examSession->extra_time_minutes > 0
            ]);
            
            // Create or update the response in the database
            $response = Response::updateOrCreate(
                [
                    'exam_session_id' => $this->examSession->id,
                    'question_id' => $questionId,
                    'student_id' => $this->user->id,
                ],
                ['selected_option' => $answer]
            );

            // Update the responses array in memory
            $this->responses[$questionId] = $answer;
            
            // Store in session as backup
            session()->put('responses', $this->responses);
            
            // Dispatch browser event in Laravel 12 format
            $this->dispatch('responseUpdated');
        } catch (\Throwable $th) {
            // Log any errors that occur
            Log::error('Error storing exam response', [
                'error' => $th->getMessage(),
                'question_id' => $questionId,
                'student_id' => $this->user->id ?? null
            ]);
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
            // Get the actual start time from when the student started the exam
            $startedAt = Carbon::parse($this->examSession->started_at);
            
            // Calculate the expected completion time based on the exam duration
            $examDuration = (int) $this->exam->duration;
            $extraTime = (int) $this->examSession->extra_time_minutes;
            $totalDuration = $examDuration + $extraTime;
            
            // Calculate proper end time based on the actual start time 
            // (when the student logged in to take the exam)
            $adjustedCompletionTime = $startedAt->copy()->addMinutes($totalDuration);
            
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
                'session_id' => $this->examSession->id,
                'student_id' => $this->student->student_id,
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

    /**
     * Handle automatic exam submission when time expires
     */
    public function examTimeExpired()
    {
        try {
            // Mark the exam as expired
            $this->examExpired = true;
            $this->timeExpiredAt = now();
            
            // Log the expiration
            Log::info('Exam time expired - automatic submission', [
                'session_id' => $this->examSession->id,
                'student_id' => $this->student->student_id,
                'exam_id' => $this->exam->id,
                'time' => $this->timeExpiredAt
            ]);
            
            // Calculate and save the score
            $score = $this->calculateScore();
            
            // Update the session with completion info
            $this->examSession->update([
                'completed_at' => $this->timeExpiredAt,
                'score' => $score,
                'auto_submitted' => true,
            ]);
            
            session()->flash('message', 'Time expired! Your exam has been automatically submitted.');
        } catch (\Exception $e) {
            Log::error('Error handling exam expiration', [
                'error' => $e->getMessage(),
                'session_id' => $this->examSession->id ?? null,
                'student_id' => $this->student->student_id ?? null
            ]);
        }
    }
    
    /**
     * Check if the exam is expired or completed
     */
    public function isExamExpired()
    {
        // First check if extra time has been recently added
        $extraTimeAdded = $this->examSession && $this->examSession->extra_time_minutes > 0 && 
                          $this->examSession->extra_time_added_at && 
                          Carbon::parse($this->examSession->extra_time_added_at)->isToday();
                          
        // If extra time was added recently, treat the exam as active regardless of completed_at
        if ($extraTimeAdded && Carbon::now()->lt($this->examSession->adjustedCompletionTime)) {
            Log::info('Exam is active due to recently added extra time', [
                'session_id' => $this->examSession->id ?? null,
                'extra_time_minutes' => $this->examSession->extra_time_minutes,
                'extra_time_added_at' => $this->examSession->extra_time_added_at,
                'adjustedCompletionTime' => $this->examSession->adjustedCompletionTime
            ]);
            return false;
        }
        
        // Don't count an exam as expired if completed_at is in the future (meaning it's the scheduled end time)
        if ($this->examSession && $this->examSession->completed_at && Carbon::parse($this->examSession->completed_at)->isFuture()) {
            Log::info('Exam is not expired because completed_at is in the future');
            return false;
        }
        
        // Always use adjustedCompletionTime which includes any extra time
        if ($this->examSession && $this->examSession->adjustedCompletionTime && Carbon::now()->lt($this->examSession->adjustedCompletionTime)) {
            Log::info('Exam is not expired - still within adjusted completion time');
            return false;
        }
        
        // Check if the exam was explicitly marked as completed with score
        if ($this->examSession && $this->examSession->completed_at && $this->examSession->score !== null) {
            Log::info('Exam is expired because it was submitted with a score');
            return true;
        }
        
        // Check if the current time is past the adjusted completion time
        if ($this->examSession && Carbon::now()->gt($this->examSession->adjustedCompletionTime)) {
            Log::info('Exam is expired because current time is past adjustedCompletionTime');
            return true;
        }
        
        Log::info('Exam is active and not expired');
        return false;
    }

    public function render()
    {
        // Check if exam is expired
        $examExpired = $this->isExamExpired();
        
        // Determine if student has extra time
        $hasExtraTime = $this->examSession && $this->examSession->extra_time_minutes > 0;
        
        // Student can submit if they have extra time and are within the adjusted completion time
        $canStillSubmit = $hasExtraTime && Carbon::now()->lt($this->examSession->adjustedCompletionTime);
        
        return view('livewire.online-examination', [
            'questions' => $this->questions,
            'exam' => $this->exam,
            'remainingTime' => $this->remainingTime,
            'examExpired' => $examExpired,
            'hasExtraTime' => $hasExtraTime,
            'canStillSubmit' => $canStillSubmit,
            'extraTimeMinutes' => $this->examSession ? $this->examSession->extra_time_minutes : 0
        ]);
    }
}
