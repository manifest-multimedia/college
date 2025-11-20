<?php

namespace App\Livewire;

use App\Models\Student;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class StudentDetails extends Component
{
    public $studentId;

    public $student;

    public $loading = true;

    public function mount($studentId)
    {
        $this->studentId = $studentId;
        $this->loadStudent();
    }

    public function loadStudent()
    {
        try {
            $this->student = Student::with([
                'CollegeClass', 
                'Cohort', 
                'User.roles', 
                'examSessions' => function ($query) {
                    $query->with([
                        'exam' => function ($q) {
                            $q->with('course', 'questionSets')
                              ->withCount('questions');
                        },
                        'responses.question.options'
                    ])->orderBy('created_at', 'desc');
                }
            ])
            ->find($this->studentId);

            if (! $this->student) {
                session()->flash('error', 'Student not found.');
            }

            $this->loading = false;
        } catch (\Exception $e) {
            Log::error('Error loading student: '.$e->getMessage());
            session()->flash('error', 'Failed to load student information.');
            $this->loading = false;
        }
    }

    public function getSessionScore($session)
    {
        $exam = $session->exam;
        if (! $exam) {
            return ['obtained' => 0, 'total' => 0, 'percentage' => 0];
        }

        // Logic from ExamResponseTracker
        // Use questions_per_session if available, otherwise fall back to total questions count
        $questionsPerSession = $exam->questions_per_session ?? $exam->questions_count;

        $responses = $session->responses;
        $processedResponses = collect();

        foreach ($responses as $response) {
            $question = $response->question;
            if (! $question) {
                continue;
            }

            $correctOption = $question->options->where('is_correct', true)->first();
            $isCorrect = ($correctOption && $response->selected_option == $correctOption->id);
            $questionMark = $question->mark ?? 1;

            $processedResponses->push([
                'is_correct' => $isCorrect,
                'mark_value' => $questionMark,
            ]);
        }

        // Only take the configured number of questions per session
        $limitedResponses = $processedResponses->take($questionsPerSession);

        $obtainedMarks = $limitedResponses->where('is_correct', true)->sum('mark_value');
        $totalMarks = $limitedResponses->sum('mark_value');

        $percentage = $totalMarks > 0 ? round(($obtainedMarks / $totalMarks) * 100, 2) : 0;

        return [
            'obtained' => $obtainedMarks,
            'total' => $totalMarks,
            'percentage' => $percentage
        ];
    }

    public function getExamName($session)
    {
        $exam = $session->exam;
        if (!$exam) return 'Unknown Exam';

        // If exam has a single question set, use its name
        if ($exam->questionSets->count() === 1) {
            return $exam->questionSets->first()->name;
        }

        // Fallback to Course Code - Type
        $courseCode = $exam->course->course_code ?? '';
        $type = ucfirst($exam->type ?? 'Exam');
        
        return $courseCode ? "$courseCode - $type" : $type;
    }


    public function deleteExamSession($sessionId)
    {
        if (! auth()->user()->hasRole(['Super Admin', 'System User'])) {
            session()->flash('error', 'You do not have permission to delete exam sessions.');

            return;
        }

        try {
            $session = \App\Models\ExamSession::find($sessionId);
            if ($session) {
                $session->delete();
                session()->flash('success', 'Exam session deleted successfully.');
                $this->loadStudent(); // Reload to update the list
            } else {
                session()->flash('error', 'Exam session not found.');
            }
        } catch (\Exception $e) {
            Log::error('Error deleting exam session: '.$e->getMessage());
            session()->flash('error', 'Failed to delete exam session.');
        }
    }

    public function render()
    {
        return view('livewire.student-details');
    }
}
