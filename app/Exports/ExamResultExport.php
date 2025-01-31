<?php

namespace App\Exports;

use App\Models\ExamSession;
use App\Models\Student;
use App\Models\ScoredQuestion;
use App\Models\Exam;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ExamResultExport implements FromCollection, WithHeadings
{
    protected $exam_id;
    protected $student_id;
    protected $student;
   
    public function __construct($exam_id, $student_id)
    {
        $this->exam_id = $exam_id;
        $this->student_id = $student_id;
        $this->student = Student::with('user')->where('student_id', $student_id)->first();
    }

    public function collection()
    {
        $exam = Exam::find($this->exam_id);
    //   dd($this->student->name, $this->student->user->id,$exam->id, $exam->course->name);

        $sessions = ExamSession::with([
            'student',
            'responses.question.options',
            'exam.course'
        ])
        ->where('exam_id', $this->exam_id)
        ->where('student_id', $this->student->id)
        ->get();

       
        
        if ($sessions->isEmpty()) {
            dd('No exam session found for given exam_id and student_id');
        }


        return $sessions->flatMap(function ($session) {
            return $session->responses->map(function ($response, $index) use ($session) {
                $question = $response->question;
                $selectedOption = $question->options->firstWhere('id', $response->selected_option);
                $correctOption = $question->options->firstWhere('is_correct', true);
                
                return [
                    'number' => $index + 1,
                    'exam_session_id' => $session->id,
                    'student_id' => $session->student->student_id,
                    'student_name' => $session->student->name,
                    'student_email' => $session->student->email,
                    'course' => $session->exam->course->name,
                    'question_id' => $question->id,
                    'question_text' => $question->question_text,
                    'selected_option_id' => $response->selected_option,
                    'selected_option_text' => $selectedOption ? $selectedOption->option_text : 'N/A',
                    'correct_option_id' => $correctOption ? $correctOption->id : 'N/A',
                    'correct_option_text' => $correctOption ? $correctOption->option_text : 'N/A',
                    'is_correct' => $selectedOption ? ($selectedOption->is_correct ? 'Yes' : 'No') : 'N/A',
                    'response_time' => $response->created_at->format('Y-m-d H:i:s'),
                    'exam_mode' => config('app.url') === 'http://college.local.test' ? 'Offline' : 'Online'
                ];
            });
        });
    }

    public function headings(): array
    {
        return [
           
            '#',
            'Exam Session ID',
            'Student ID',
            'Student Name',
            'Student Email',
            'Course',
            'Question ID',
            'Question Text',
            'Selected Option ID',
            'Selected Option Text',
            'Correct Option ID',
            'Correct Option Text',
            'Is Correct',
            'Response Time',
            'Exam Mode'
        ];
    }
} 