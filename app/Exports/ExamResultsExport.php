<?php

namespace App\Exports;

use App\Models\ExamSession;
use App\Models\Exam;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ExamResultsExport implements FromCollection, WithHeadings
{
    protected $exam_id;
    protected $college_class_id;

    public function __construct($exam_id, $college_class_id)
    {
        $this->exam_id = $exam_id;
        $this->college_class_id = $college_class_id;
    }
    public function collection()
    {
        $exam = Exam::find($this->exam_id);
        $questions_per_session = $exam->questions_per_session ?? $exam->number_of_questions;
    
        $examSessionsData = ExamSession::where('exam_id', $this->exam_id)
            ->whereHas('student', function ($query) {
                $query->where('college_class_id', $this->college_class_id);
            })
            ->with([
                'student.user', 
                'exam.course', 
                'scoredQuestions' => function ($query) {
                    $query->with(['question.options', 'response']);
                }
            ])
            ->get()->sortBy(function ($session) {
                // Extract the numeric part of student_id
                preg_match('/\d+$/', $session->student->student_id, $matches);
                return (int)($matches[0] ?? 0);
            });
    
        return $examSessionsData->map(function ($session) use ($questions_per_session) {
            $correct_answers = $session->scoredQuestions->filter(function ($scoredQuestion) {
                $correct_option = $scoredQuestion->question->options
                    ->where('is_correct', true)
                    ->first();
    
                return $correct_option && 
                    optional($scoredQuestion->response)->selected_option == $correct_option->id;
            })->count();
    
            $total_answered = $session->scoredQuestions->count();
    
            return [
                'date' => $session->created_at->format('Y-m-d'),
                'student_id' => $session->student->student_id ?? 'N/A',
                'student_name' => $session->student->user->name ?? 'N/A',
                'course' => $session->exam->course->name ?? 'N/A',
                'score' => "{$correct_answers}/{$questions_per_session}",
                'answered' => "{$total_answered}/{$questions_per_session}",
                'percentage' => $questions_per_session > 0 
                    ? round(($correct_answers / $questions_per_session) * 100, 2)
                    : 0
            ];
        });
    }
    
    public function headings(): array
    {
        return [
            'Date',
            'Student ID',
            'Student Name',
            'Course',
            'Score',
            'Answered',
            'Percentage'
        ];
    }
}