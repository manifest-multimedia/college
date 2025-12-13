<?php

namespace App\Exports;

use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\Student;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ExamResultsExport implements FromCollection, WithHeadings
{
    protected $exam_id;

    protected $college_class_id;

    public function __construct($exam_id, $college_class_id = null)
    {
        $this->exam_id = $exam_id;
        $this->college_class_id = $college_class_id;
    }

    public function collection()
    {
        try {
            $exam = Exam::find($this->exam_id);
            if (! $exam) {
                Log::error('Exam not found for export', ['exam_id' => $this->exam_id]);

                return collect([]);
            }

            $questionsPerSession = $exam->questions_per_session ?? $exam->questions()->count();

            // Base query for exam sessions
            // Include sessions with completed_at OR auto_submitted flag (for expired exams)
            $query = ExamSession::where('exam_id', $this->exam_id)
                ->where(function($q) {
                    $q->whereNotNull('completed_at')
                      ->orWhere('auto_submitted', true);
                })
                ->with([
                    'student', // This is actually User model
                    'exam.course',
                    'responses.question.options',
                ]);

            // Apply college class filter if provided
            if ($this->college_class_id) {
                // Get students in this college class
                $studentIds = Student::where('college_class_id', $this->college_class_id)
                    ->join('users', 'students.email', '=', 'users.email')
                    ->pluck('users.id');

                $query->whereIn('student_id', $studentIds);
            }

            // Get all results (no pagination)
            $examSessions = $query->get();

            // Process results for export
            return $examSessions->map(function ($session) use ($questionsPerSession) {
                // Find the student record using the user email
                $userEmail = $session->student->email ?? null;
                $student = $userEmail ? Student::where('email', $userEmail)->first() : null;

                // Initialize counters
                $totalAttempted = 0;
                $totalCorrect = 0;
                $totalMarks = 0;
                $obtainedMarks = 0;

                // Use ResultsService for consistent calculation
                $resultsService = app(\App\Services\ResultsService::class);
                $result = $resultsService->calculateOnlineExamScore($session, $questionsPerSession);

                $totalAttempted = $result['total_answered'];
                $totalCorrect = $result['correct_answers'];
                $obtainedMarks = $result['obtained_marks'];
                $totalMarks = $result['total_marks'];
                $scorePercentage = $result['percentage'];                // Return formatted row for export

                return [
                    'date' => $session->completed_at ? $session->completed_at->format('Y-m-d') : ($session->started_at ? $session->started_at->format('Y-m-d') : 'N/A'),
                    'student_id' => $student ? $student->student_id : 'N/A',
                    'student_name' => $session->student->name ?? 'N/A',
                    'course' => $session->exam->course->name ?? 'N/A',
                    'score' => "{$totalCorrect}/{$questionsPerSession}",
                    'marks' => "{$obtainedMarks}/{$totalMarks}",
                    'answered' => "{$totalAttempted}/{$questionsPerSession}",
                    'percentage' => $scorePercentage,
                ];
            });
        } catch (\Exception $e) {
            Log::error('Error generating Excel export', [
                'error' => $e->getMessage(),
                'exam_id' => $this->exam_id,
            ]);

            return collect([]);
        }
    }

    public function headings(): array
    {
        return [
            'Date',
            'Student ID',
            'Student Name',
            'Course',
            'Score',
            'Marks',
            'Answered',
            'Percentage',
        ];
    }
}
