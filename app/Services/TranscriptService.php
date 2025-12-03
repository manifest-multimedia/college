<?php

namespace App\Services;

use App\Exports\TranscriptExport;
use App\Models\AcademicYear;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\OfflineExamScore;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Subject;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class TranscriptService
{
    /**
     * Generate transcript data for a student
     *
     * @param  int  $studentId
     * @param  int|null  $academicYearId
     * @param  int|null  $semesterId
     * @return array
     */
    public function generateTranscriptData($studentId, $academicYearId = null, $semesterId = null)
    {
        try {
            $student = Student::with([
                'collegeClass',
                'cohort',
                'user',
            ])->findOrFail($studentId);

            // Get academic context
            $academicYear = $academicYearId ? AcademicYear::find($academicYearId) : AcademicYear::where('is_current', true)->first();
            $semester = $semesterId ? Semester::find($semesterId) : null;

            // Get all courses/subjects for the student
            $subjects = $this->getStudentSubjects($student, $academicYear, $semester);

            // Get transcript entries
            $transcriptEntries = [];
            $totalCreditHours = 0;
            $totalGradePoints = 0;
            $totalCreditHoursAttempted = 0;

            foreach ($subjects as $subject) {
                $entry = $this->generateSubjectEntry($student, $subject);
                if ($entry) {
                    $transcriptEntries[] = $entry;

                    if ($entry['credit_hours'] > 0) {
                        $totalCreditHoursAttempted += $entry['credit_hours'];

                        if ($entry['grade_points'] !== null) {
                            $totalCreditHours += $entry['credit_hours'];
                            $totalGradePoints += ($entry['grade_points'] * $entry['credit_hours']);
                        }
                    }
                }
            }

            // Calculate GPA
            $gpa = $totalCreditHours > 0 ? round($totalGradePoints / $totalCreditHours, 2) : 0.00;
            $cgpa = $this->calculateCumulativeGPA($student, $academicYear);

            return [
                'student' => $student,
                'academic_year' => $academicYear,
                'semester' => $semester,
                'transcript_entries' => $transcriptEntries,
                'summary' => [
                    'total_credit_hours_attempted' => $totalCreditHoursAttempted,
                    'total_credit_hours_earned' => $totalCreditHours,
                    'total_grade_points' => round($totalGradePoints, 2),
                    'semester_gpa' => $gpa,
                    'cumulative_gpa' => $cgpa,
                ],
                'generated_at' => now(),
            ];

        } catch (\Exception $e) {
            Log::error('Error generating transcript data', [
                'student_id' => $studentId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Get subjects/courses for a student
     */
    protected function getStudentSubjects($student, $academicYear = null, $semester = null)
    {
        $query = Subject::query();

        // Filter by academic year and semester if provided
        if ($academicYear) {
            $query->where('year_id', $academicYear->id);
        }

        if ($semester) {
            $query->where('semester_id', $semester->id);
        }

        // Filter by student's class
        if ($student->college_class_id) {
            $query->where('college_class_id', $student->college_class_id);
        }

        return $query->orderBy('course_code')->get();
    }

    /**
     * Generate transcript entry for a subject
     */
    protected function generateSubjectEntry($student, $subject)
    {
        // Get online exam scores for this subject
        $onlineScore = $this->getOnlineExamScore($student, $subject);

        // Get offline exam scores for this subject
        $offlineScore = $this->getOfflineExamScore($student, $subject);

        // If no scores found, skip this subject
        if (! $onlineScore && ! $offlineScore) {
            return null;
        }

        // Determine final score and grade
        $finalScore = $this->calculateFinalScore($onlineScore, $offlineScore);
        $letterGrade = $this->getLetterGrade($finalScore);
        $gradePoints = $this->getGradePoints($letterGrade);
        $creditHours = $subject->credit_hours ?? 3.0;

        return [
            'subject_code' => $subject->course_code,
            'subject_name' => $subject->name,
            'credit_hours' => $creditHours,
            'online_score' => $onlineScore ? $onlineScore['percentage'] : null,
            'offline_score' => $offlineScore ? $offlineScore['percentage'] : null,
            'final_score' => $finalScore,
            'letter_grade' => $letterGrade,
            'grade_points' => $gradePoints,
            'status' => $this->getPassStatus($finalScore),
            'exam_details' => [
                'online' => $onlineScore,
                'offline' => $offlineScore,
            ],
        ];
    }

    /**
     * Get online exam score for a subject
     */
    protected function getOnlineExamScore($student, $subject)
    {
        // Get the latest exam session for this student and subject
        $examSession = ExamSession::whereHas('exam', function ($query) use ($subject) {
            $query->where('course_id', $subject->id);
        })
            ->whereHas('student', function ($query) use ($student) {
                $query->where('email', $student->email);
            })
            ->whereNotNull('completed_at')
            ->latest('completed_at')
            ->first();

        if (! $examSession) {
            return null;
        }

        // Calculate score
        $exam = $examSession->exam;
        // Use ResultsService for consistent score calculation
        $resultsService = app(\App\Services\ResultsService::class);
        $result = $resultsService->calculateOnlineExamScore($examSession);

        $totalQuestions = $result['total_questions'];
        $correctAnswers = $result['correct_answers'];
        $totalMarks = $result['total_marks'];
        $obtainedMarks = $result['obtained_marks'];
        $percentage = $result['percentage'];

        return [
            'exam_id' => $exam->id,
            'exam_title' => $exam->course->name ?? 'Online Exam',
            'session_id' => $examSession->id,
            'total_questions' => $totalQuestions,
            'correct_answers' => $correctAnswers,
            'total_marks' => $totalMarks,
            'obtained_marks' => $obtainedMarks,
            'percentage' => $percentage,
            'completed_at' => $examSession->completed_at,
        ];
    }

    /**
     * Get offline exam score for a subject
     */
    protected function getOfflineExamScore($student, $subject)
    {
        $score = OfflineExamScore::whereHas('offlineExam', function ($query) use ($subject) {
            $query->where('course_id', $subject->id);
        })
            ->where('student_id', $student->id)
            ->latest('created_at')
            ->first();

        if (! $score) {
            return null;
        }

        return [
            'exam_id' => $score->offline_exam_id,
            'exam_title' => $score->offlineExam->title,
            'score' => $score->score,
            'total_marks' => $score->total_marks,
            'percentage' => $score->percentage,
            'remarks' => $score->remarks,
            'exam_date' => $score->exam_date,
            'recorded_at' => $score->created_at,
        ];
    }

    /**
     * Calculate final score from online and offline scores
     */
    protected function calculateFinalScore($onlineScore, $offlineScore)
    {
        $resultsService = app(\App\Services\ResultsService::class);

        return $resultsService->calculateFinalScore($onlineScore, $offlineScore);
    }

    /**
     * Convert percentage score to letter grade
     */
    protected function getLetterGrade($percentage)
    {
        $resultsService = app(\App\Services\ResultsService::class);

        return $resultsService->getLetterGrade($percentage);
    }

    /**
     * Convert letter grade to grade points
     */
    protected function getGradePoints($letterGrade)
    {
        $resultsService = app(\App\Services\ResultsService::class);

        return $resultsService->getGradePoints($letterGrade);
    }

    /**
     * Get pass/fail status
     */
    protected function getPassStatus($percentage)
    {
        $resultsService = app(\App\Services\ResultsService::class);

        return $resultsService->getPassStatus($percentage);
    }

    /**
     * Calculate cumulative GPA for all semesters up to the given academic year
     */
    protected function calculateCumulativeGPA($student, $academicYear = null)
    {
        try {
            $allSubjects = Subject::when($academicYear, function ($query) use ($academicYear) {
                $query->where('year_id', '<=', $academicYear->id);
            })
                ->when($student->college_class_id, function ($query) use ($student) {
                    $query->where('college_class_id', $student->college_class_id);
                })
                ->orderBy('year_id')
                ->orderBy('semester_id')
                ->get();

            $totalGradePoints = 0;
            $totalCreditHours = 0;

            foreach ($allSubjects as $subject) {
                $entry = $this->generateSubjectEntry($student, $subject);

                if ($entry && $entry['grade_points'] !== null && $entry['credit_hours'] > 0) {
                    $totalCreditHours += $entry['credit_hours'];
                    $totalGradePoints += ($entry['grade_points'] * $entry['credit_hours']);
                }
            }

            return $totalCreditHours > 0 ? round($totalGradePoints / $totalCreditHours, 2) : 0.00;

        } catch (\Exception $e) {
            Log::error('Error calculating cumulative GPA', [
                'student_id' => $student->id,
                'error' => $e->getMessage(),
            ]);

            return 0.00;
        }
    }

    /**
     * Generate transcript in different formats
     */
    public function generateTranscript($studentId, $format = 'array', $academicYearId = null, $semesterId = null)
    {
        $data = $this->generateTranscriptData($studentId, $academicYearId, $semesterId);

        switch ($format) {
            case 'pdf':
                return $this->generatePDF($data);
            case 'csv':
                return $this->generateCSV($data);
            case 'excel':
                return $this->generateExcel($data);
            default:
                return $data;
        }
    }

    /**
     * Generate PDF transcript
     */
    public function generatePDF($data)
    {
        try {
            $pdf = Pdf::loadView('exports.transcript-pdf', $data);

            // Set paper size and orientation
            $pdf->setPaper('a4', 'portrait');

            // Generate filename
            $filename = 'transcript_'.$data['student']->student_id.'_'.now()->format('Y-m-d').'.pdf';

            return response()->streamDownload(
                fn () => print ($pdf->output()),
                $filename,
                [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="'.$filename.'"',
                ]
            );
        } catch (\Exception $e) {
            Log::error('Error generating PDF transcript', [
                'student_id' => $data['student']->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Generate CSV transcript
     */
    public function generateCSV($data)
    {
        $csvData = [];

        // Add header information
        $csvData[] = ['Student Transcript'];
        $csvData[] = ['Student ID', $data['student']->student_id];
        $csvData[] = ['Student Name', $data['student']->full_name];
        $csvData[] = ['Class', $data['student']->collegeClass->name ?? 'N/A'];
        $csvData[] = ['Academic Year', $data['academic_year']->name ?? 'N/A'];
        $csvData[] = ['Semester', $data['semester']->name ?? 'All Semesters'];
        $csvData[] = ['Generated', $data['generated_at']->format('Y-m-d H:i:s')];
        $csvData[] = []; // Empty row

        // Add course headers
        $csvData[] = [
            'Course Code',
            'Course Name',
            'Credit Hours',
            'Online Score (%)',
            'Offline Score (%)',
            'Final Score (%)',
            'Letter Grade',
            'Grade Points',
            'Status',
        ];

        // Add course data
        foreach ($data['transcript_entries'] as $entry) {
            $csvData[] = [
                $entry['subject_code'],
                $entry['subject_name'],
                $entry['credit_hours'],
                $entry['online_score'] ?? 'N/A',
                $entry['offline_score'] ?? 'N/A',
                $entry['final_score'],
                $entry['letter_grade'],
                $entry['grade_points'],
                $entry['status'],
            ];
        }

        // Add summary
        $csvData[] = []; // Empty row
        $csvData[] = ['Summary'];
        $csvData[] = ['Total Credit Hours Attempted', $data['summary']['total_credit_hours_attempted']];
        $csvData[] = ['Total Credit Hours Earned', $data['summary']['total_credit_hours_earned']];
        $csvData[] = ['Total Grade Points', $data['summary']['total_grade_points']];
        $csvData[] = ['Semester GPA', $data['summary']['semester_gpa']];
        $csvData[] = ['Cumulative GPA', $data['summary']['cumulative_gpa']];

        return $csvData;
    }

    /**
     * Generate Excel transcript
     */
    public function generateExcel($data)
    {
        try {
            $filename = 'transcript_'.$data['student']->student_id.'_'.now()->format('Y-m-d').'.xlsx';

            return Excel::download(new TranscriptExport($data), $filename);
        } catch (\Exception $e) {
            Log::error('Error generating Excel transcript', [
                'student_id' => $data['student']->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
