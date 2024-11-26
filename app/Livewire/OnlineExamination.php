<?php

namespace App\Livewire;

use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\Student;
use App\Models\Response;
use Livewire\Component;
use Carbon\Carbon;

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

    protected $listeners = ['submitExam'];

    public function mount($examPassword, $student_id = null)
    {
        $this->examPassword = $examPassword;
        $this->student_id = $student_id;
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
        // Start the exam session and track start time
        $this->examSession = ExamSession::create([
            'exam_id' => $this->exam->id,
            'student_id' => $this->student_id,
            'start_time' => now(),
            'end_time' => now()->addMinutes($this->exam->duration),
        ]);

        $this->examStartTime = now();
        $this->remainingTime = $this->exam->duration * 60; // seconds
    }

    public function loadQuestions()
    {
        // Reshuffle questions for each student
        $examQuestions = $this->exam->questions()->inRandomOrder()->get();

        $this->questions = $examQuestions->map(function ($question) {
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
            ['answer' => $answer]
        );

        $this->responses[$questionId] = $answer; // Update local responses
    }

    public function submitExam()
    {
        // Mark the exam as completed
        $this->examSession->update([
            'completed_at' => now(),
        ]);

        // Redirect or show completion message
        session()->flash('message', 'Exam submitted successfully.');
        return redirect()->route('exam.results', ['examSession' => $this->examSession->id]);
    }

    public function endExam()
    {
        // Automatically end the exam after the duration
        $this->examSession->update([
            'completed_at' => now(),
        ]);

        session()->flash('message', 'Exam has ended due to time expiration.');
        return redirect()->route('exam.results', ['examSession' => $this->examSession->id]);
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
        // Update remaining time every second
        if ($this->remainingTime > 0) {
            $this->remainingTime--;
        } else {
            $this->endExam();
        }
    }
}
