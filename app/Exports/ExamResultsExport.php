<?php

namespace App\Exports;

use App\Models\ExamSession;
use App\Models\Exam;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ExamResultsExport implements FromCollection, WithHeadings
{
    protected $exam_id;

    public function __construct($exam_id)
    {
        $this->exam_id = $exam_id;
    }

    public function collection()
    {
        $exam = Exam::find($this->exam_id);
        $questions_per_session = $exam->questions_per_session ?? $exam->number_of_questions;

        return ExamSession::with([
            'student.user', 
            'exam.course', 
            'responses.question',
            'responses.option'
        ])
        ->where('exam_id', $this->exam_id)
        ->get()
        ->map(function ($session) use ($questions_per_session, $exam) {
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
            'Percentage'
        ];
    }
} 