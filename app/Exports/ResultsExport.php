<?php

namespace App\Exports;

use App\Models\Student;
use App\Models\User;
use App\Models\ExamSession;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;

class ResultsExport implements FromCollection, WithHeadings, ShouldAutoSize, WithMapping
{
    protected $filters;

    public function __construct($filters)
    {
        $this->filters = $filters; // Pass filters from the controller
    }

    // public function collection()
    // {
    //     // Apply filters to fetch students
    //     $studentsQuery = Student::query();

    //     if (isset($this->filters['filter_student_id'])) {
    //         $studentsQuery->where('student_id', 'like', '%' . $this->filters['filter_student_id'] . '%');
    //     }

    //     if (isset($this->filters['filter_email'])) {
    //         $studentsQuery->where('email', 'like', '%' . $this->filters['filter_email'] . '%');
    //     }

    //     if (isset($this->filters['filter_by_exam'])) {
    //         $examId = $this->filters['filter_by_exam'];
    //         $userIds = ExamSession::where('exam_id', $examId)->pluck('student_id')->toArray();
    //         $studentsQuery->whereIn('user_id', $userIds); // Assuming user_id links to users
    //     }

    //     if (isset($this->filters['filter_by_class'])) {
    //         $classId = $this->filters['filter_by_class'];
    //         $studentsQuery->whereHas('collegeClass', function ($query) use ($classId) {
    //             $query->where('id', $classId);
    //         });
    //     }

    //     return $studentsQuery->with('user', 'examSessions.exam.course', 'examSessions.responses')->get();
    // }

    public function collection()
    {
        // Apply filters to fetch students
        $studentsQuery = Student::query();

        if (isset($this->filters['filter_student_id'])) {
            $studentsQuery->where('student_id', 'like', '%' . $this->filters['filter_student_id'] . '%');
        }

        if (isset($this->filters['filter_email'])) {
            $studentsQuery->where('email', 'like', '%' . $this->filters['filter_email'] . '%');
        }

        if (isset($this->filters['filter_by_exam'])) {
            $examId = $this->filters['filter_by_exam'];

            // Get User IDs from ExamSessions where exam_id matches
            $userIds = ExamSession::where('exam_id', $examId)
                ->pluck('student_id') // student_id here is actually the User ID
                ->toArray();

            if (!empty($userIds)) {
                // Get emails of Users with these IDs
                $userEmails = User::whereIn('id', $userIds)
                    ->pluck('email')
                    ->toArray();

                if (!empty($userEmails)) {
                    // Filter students by email
                    $studentsQuery->whereIn('email', $userEmails);
                } else {
                    $studentsQuery->whereNull('id'); // No matching students, return an empty result
                }
            } else {
                $studentsQuery->whereNull('id'); // No matching users, return an empty result
            }
        }

        if (isset($this->filters['filter_by_class'])) {
            $classId = $this->filters['filter_by_class'];
            $studentsQuery->whereHas('collegeClass', function ($query) use ($classId) {
                $query->where('id', $classId);
            });
        }

        return $studentsQuery->with('examSessions.exam.course', 'examSessions.responses')->get();
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
