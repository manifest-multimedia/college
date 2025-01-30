<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Exam;
use Livewire\WithPagination;
use App\Exports\ExamResultsExport;
use App\Models\ExamSession;
use App\Models\Student;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;
use App\Models\ScoredQuestion;

class ExamResultsModule extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $mode = 'index';
    public $selected_exam_id;

    public function render()
    {
        $results = collect();

        if ($this->selected_exam_id) {
            $exam = Exam::find($this->selected_exam_id);
            $questions_per_session = $exam->questions_per_session ?? $exam->questions->count();
            
            $examSessions = ExamSession::with([
                'student.user', 
                'exam.course', 
                'scoredQuestions.question.options',
                'scoredQuestions.response'
            ])
            ->where('exam_id', $this->selected_exam_id)
            ->paginate(25);

            $results = $examSessions->through(function ($session) use ($questions_per_session, $exam) {
                // First, ensure scored questions are stored for this session
                $this->ensureScoredQuestionsExist($session, $questions_per_session);

                // Get number of correct answers from attempted questions
                $correct_answers = $session->scoredQuestions
                    ->filter(function ($scoredQuestion) {
                        $correct_option = $scoredQuestion->question->options
                            ->where('is_correct', true)
                            ->first();
                        
                        return $correct_option && 
                            $scoredQuestion->response->selected_option == $correct_option->id;
                    })
                    ->count();

                // Calculate total questions answered
                $total_answered = $session->scoredQuestions->count();

                return [
                    'date' => $session->created_at->format('Y-m-d'),
                    'student_id' => $session->student->student_id ?? 'N/A',
                    'student_name' => $session->student->user->name ?? 'N/A',
                    'course' => $session->exam->course->name ?? 'N/A',
                    'score' => $correct_answers . '/' . $questions_per_session,
                    'answered' => $total_answered . ' questions',
                    'percentage' => $questions_per_session > 0 
                        ? round(($correct_answers / $questions_per_session) * 100, 2)
                        : 0,
                    'session_id' => $session->id
                ];
            });
        }

        return view('livewire.exam-results-module', [
            'exams' => Exam::with('course')->get(),
            'results' => $results
        ]);
    }

    protected function ensureScoredQuestionsExist($session, $questions_per_session)
    {
        // Only create scored questions if they don't exist
        if ($session->scoredQuestions->isEmpty()) {
            // Get the first X responses chronologically
            $responses = $session->responses()
                ->with('question')
                ->orderBy('created_at')
                ->take($questions_per_session)
                ->get();

            // Store these as scored questions
            foreach ($responses as $response) {
                ScoredQuestion::create([
                    'exam_session_id' => $session->id,
                    'question_id' => $response->question_id,
                    'response_id' => $response->id
                ]);
            }

            // Refresh the session to get the new scored questions
            $session->load('scoredQuestions.question.options', 'scoredQuestions.response');
        }
    }

    public function exportResults()
    {
        if (!$this->selected_exam_id) {
            return;
        }
        
        $exam = Exam::find($this->selected_exam_id);
        $filename = Str::slug($exam->course->name) . '-results.xlsx';
        
        return Excel::download(new ExamResultsExport($this->selected_exam_id), $filename);
    }
}
