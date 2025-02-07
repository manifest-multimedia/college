<?php 

namespace App\Exports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;

class ResultsExport implements FromCollection, WithHeadings, ShouldAutoSize, WithMapping
{
    protected $filters;

    public function __construct($filters)
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $studentsQuery = Student::query();

        if (isset($this->filters['filter_student_id'])) {
            $studentsQuery->where('student_id', 'like', '%' . $this->filters['filter_student_id'] . '%');
        }

        if (isset($this->filters['filter_email'])) {
            $studentsQuery->where('email', 'like', '%' . $this->filters['filter_email'] . '%');
        }

        if (isset($this->filters['filter_by_exam'])) {
            $examId = $this->filters['filter_by_exam'];
            $userIds = \App\Models\ExamSession::where('exam_id', $examId)->pluck('student_id')->toArray();
            $studentsQuery->whereIn('user_id', $userIds);
        }

        if (isset($this->filters['filter_by_class'])) {
            $classId = $this->filters['filter_by_class'];
            $studentsQuery->whereHas('collegeClass', function ($query) use ($classId) {
                $query->where('id', $classId);
            });
        }

        return $studentsQuery->with(['examSessions' => function ($query) {
            if (isset($this->filters['filter_by_exam'])) {
                $query->where('exam_id', $this->filters['filter_by_exam']);
            }
            $query->with('exam.course', 'responses');
        }])->get();
    }

    public function map($student): array
    {
        $rows = [];

        foreach ($student->examSessions as $examSession) {
            $courseName = optional($examSession->exam->course)->name ?? 'N/A';
            $score = computeResults($examSession->id, 'score') ?? '0/0';
            $answered = computeResults($examSession->id, 'total_answered') ?? 0;
            $percentage = computeResults($examSession->id, 'percentage') ?? '0%';

            $rows[] = [
                $student->student_id,
                $examSession->created_at->format('Y-m-d H:i:s'),
                $student->first_name . ' ' . $student->last_name,
                $courseName,
                $score,
                $answered,
                $percentage,
            ];
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Student ID',
            'Date',
            'Name',
            'Course',
            'Score',
            'Answered',
            'Percentage',
        ];
    }
}