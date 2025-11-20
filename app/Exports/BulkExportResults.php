<?php

namespace App\Exports;

use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\Student;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BulkExportResults implements FromCollection, WithHeadings
{
    protected $exam_id;

    public function __construct($exam_id)
    {
        $this->exam_id = $exam_id;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $exam = Exam::find($this->exam_id);
        if (! $exam) {
            dd('No exam found for given exam_id');
        }

        $sessions = ExamSession::with([
            'student.user',
            'responses.question.options',
            'exam.course',
        ])
            ->where('exam_id', $this->exam_id)
            ->get();

        if ($sessions->isEmpty()) {
            dd('No exam sessions found for given exam_id');
        }

        return $sessions->flatMap(function ($session) {
            return $session->responses->map(function ($response, $index) use ($session) {
                // Check if the student relationship is not null
                if (! $session->student) {
                    return [];
                }

                $question = $response->question;
                $selectedOption = $question->options->firstWhere('id', $response->selected_option);
                $correctOption = $question->options->firstWhere('is_correct', true);

                // Filter out students with no student_id or no name
                if (! $session->student->student_id || ! $session->student->name) {
                    return [];
                }

                return [
                    'number' => $index + 1,
                    'exam_session_id' => $session->id,
                    'session_started_at' => $session->started_at,
                    'session_completed_at' => $session->completed_at,
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
                    'exam_mode' => config('app.url') === 'http://college.local.test' ? 'Offline' : 'Online',
                    'college_class_id' => $session->student->college_class_id,
                ];
            })->filter(); // Filter out any empty arrays
        });
    }

    public function headings(): array
    {
        return [
            '#',
            'exam_session_id',
            'session_started_at',
            'session_completed_at',
            'student_id',
            'student_name',
            'student_email',
            'course_name',
            'question_id',
            'question_text',
            'selected_option_id',
            'selected_option_text',
            'correct_option_id',
            'correct_option_text',
            'is_correct',
            'response_time',
            'exam_mode',
            'college_class_id',
        ];
    }
}
