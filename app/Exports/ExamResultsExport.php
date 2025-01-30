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
        $required_questions = $exam->number_of_questions;

        return ExamSession::with(['student.user', 'exam.course', 'responses'])
            ->where('exam_id', $this->exam_id)
            ->get()
            ->map(function ($session) use ($required_questions) {
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