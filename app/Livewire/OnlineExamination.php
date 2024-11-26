<?php

namespace App\Livewire;

use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\Student;
use App\Models\Response;
use Livewire\Component;
use Carbon\Carbon;
use App\Models\User;

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


    protected $listeners = ['submitExam'];

    public function mount($examPassword, $student_id = null)
    {
        $this->examPassword = $examPassword;
        $this->student_id = $student_id;
        $student = Student::where('id', $student_id)->first();
        $this->student_name = $student->first_name . ' ' . $student->last_name;
        $this->exam = Exam::with('course')->where('password', $this->examPassword)->first();

        if (!$this->exam) {
            abort(404, 'Exam not found');
        }

        // Initialize the session
        $this->initializeExamSession();
        $this->loadQuestions();
    }
    public function initializeExamSession()
    {


        $user = '';
        try {
            $student = Student::where('id', $this->student_id)->first();

            // Check if student has a user account, else create one
            if (User::where('email', $student->email)->exists()) {
                $user = User::where('email', $student->email)->first();
            } else {
                $user = $student->createUser();
            }

            // Check if there is an existing session for the student and exam
            $this->examSession = ExamSession::where('exam_id', $this->exam->id)
                ->where('student_id', $user->id)
                ->first();

            // If no session exists, create a new session
            $duration = (int) $this->exam->duration;

            if (!$this->examSession) {
                $this->examSession = ExamSession::create([
                    'exam_id' => $this->exam->id,
                    'student_id' => $user->id,
                    'started_at' => now(),
                    'completed_at' => now()->addMinutes($duration), // Casted duration
                ]);
            }
        } catch (\Throwable $th) {
            // Handle any exceptions here (optional)
        }

        // Remove direct calculation of remaining time
        $this->examStartTime = Carbon::parse($this->examSession->started_at);
        $this->remainingTime = $this->getRemainingTime();
    }




    public function loadQuestions()
    {
        // Reshuffle questions for each student
        $examQuestions = $this->exam->questions()->inRandomOrder()->get();

        $this->questions = $examQuestions->map(function ($question) {
            // Load student's previous responses if any
            $response = Response::where('exam_session_id', $this->examSession->id)
                ->where('question_id', $question->id)
                ->first();

            $this->responses[$question->id] = $response ? $response->selected_option : null;

            return [
                'id' => $question->id,
                'question' => $question->question_text,
                'options' => $question->options()->get()->toArray(),
                'marks' => $question->mark
            ];
        });
    }

    public function storeResponse($questionId, $answer)
    {
        // Store the student's response
        $response = Response::updateOrCreate(
            ['exam_session_id' => $this->examSession->id, 'question_id' => $questionId],
            ['selected_option' => $answer]
        );

        $this->responses[$questionId] = $answer; // Update local responses
    }

    public function submitExam()
    {
        // Calculate score before marking the exam as completed
        $score = $this->calculateScore();

        // Mark the exam as completed and store the score
        $this->examSession->update([
            'completed_at' => now(),
            'score' => $score,
        ]);

        // Redirect or show completion message
        session()->flash('message', 'Exam submitted successfully.');
        return redirect()->route('take-exam');
        // return redirect()->route('exam.results', ['examSession' => $this->examSession->id]);
    }

    public function endExam()
    {
        // Automatically end the exam after the duration
        $this->examSession->update([
            'completed_at' => now(),
        ]);

        session()->flash('message', 'Exam has ended due to time expiration.');



        // Return to take-exam route

        return redirect()->route('take-exam');
    }

    public function calculateScore()
    {
        $score = 0;
        // Calculate the score based on the responses
        foreach ($this->responses as $questionId => $answer) {
            $question = $this->exam->questions()->find($questionId);
            if ($question && $answer == $question->correct_option) {
                $score += $question->mark; // Add mark for correct answer
            }
        }
        return $score;
    }

    public function render()
    {
        return view('livewire.online-examination', [
            'questions' => $this->questions,
            'exam' => $this->exam,
            'remainingTime' => $this->remainingTime,
        ]);
    }

    public function countdown()
    {
        $this->remainingTime = $this->getRemainingTime();

        if ($this->remainingTime <= 0) {
            // $this->endExam();
        }
    }


    public function getRemainingTime()
    {
        // Ensure consistent timezone for calculation
        $currentTime = now()->setTimezone(config('app.timezone'));
        $completedAt = Carbon::parse($this->examSession->completed_at)->setTimezone(config('app.timezone'));

        $remainingTime = $completedAt->diffInSeconds($currentTime, false); // Signed difference

        return max($remainingTime, 0); // Ensure non-negative
    }
}
