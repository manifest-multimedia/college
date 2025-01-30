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
            $required_questions = $exam->number_of_questions;

            // Get paginated results from the database first
            $examSessions = ExamSession::with(['student.user', 'exam.course', 'responses'])
                ->where('exam_id', $this->selected_exam_id)
                ->paginate(25);

            // Map the paginated results
            $results = $examSessions->through(function ($session) use ($required_questions) {
                $total_answered = $session->responses->count();
                $correct_answers = $session->responses->where('is_correct', true)->count();
                
                $score_denominator = min($total_answered, $required_questions);
                
                return [
                    'date' => $session->created_at->format('Y-m-d'),
                    'student_id' => $session->student->id ?? 'N/A',
                    'student_name' => $session->student->user->name ?? 'N/A',
                    'course' => $session->exam->course->name ?? 'N/A',
                    'score' => $correct_answers . '/' . $score_denominator,
                    'percentage' => $score_denominator > 0 
                        ? round(($correct_answers / $score_denominator) * 100, 2) 
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
