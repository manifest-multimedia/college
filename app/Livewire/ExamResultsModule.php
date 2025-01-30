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
            $questions_per_session = $exam->questions_per_session ?? $exam->number_of_questions;
            
            // Get paginated results from the database first
            $examSessions = ExamSession::with([
                'student.user', 
                'exam.course', 
                'responses.question',
                'responses.option'
            ])
            ->where('exam_id', $this->selected_exam_id)
            ->paginate(25);

            // Map the paginated results
            $results = $examSessions->through(function ($session) use ($questions_per_session, $exam) {
                // Get the total possible marks for this exam
                $total_possible_marks = $exam->questions()
                    ->take($questions_per_session)
                    ->sum('mark');

                // Get the first X responses where X is questions_per_session
                $scored_responses = $session->responses()
                    ->with(['question', 'option'])
                    ->take($questions_per_session)
                    ->get();

                // Calculate total marks obtained
                $marks_obtained = $scored_responses
                    ->filter(function ($response) {
                        return $response->option && $response->option->is_correct;
                    })
                    ->sum(function ($response) {
                        return $response->question->mark;
                    });

                return [
                    'date' => $session->created_at->format('Y-m-d'),
                    'student_id' => $session->student->student_id ?? 'N/A',
                    'student_name' => $session->student->user->name ?? 'N/A',
                    'course' => $session->exam->course->name ?? 'N/A',
                    'score' => $marks_obtained . '/' . $total_possible_marks,
                    'percentage' => $total_possible_marks > 0 
                        ? round(($marks_obtained / $total_possible_marks) * 100, 2) 
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
