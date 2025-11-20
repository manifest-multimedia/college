<?php

namespace App\Services\Exams;

use App\Models\AcademicYear;
use App\Models\Exam;
use App\Models\ExamClearance;
use App\Models\OfflineExam;
use App\Models\Semester;
use App\Models\Student;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class ExamClearanceService
{
    /**
     * Check if a student can be cleared for a specific exam based on fee payment.
     *
     * @param  Student  $student  The student to check clearance for
     * @param  Model  $exam  The exam (Exam or OfflineExam) to check clearance for
     * @param  AcademicYear|null  $academicYear  The academic year context
     * @param  Semester|null  $semester  The semester context
     * @return bool Whether the student meets the clearance threshold
     */
    public function checkClearance(Student $student, Model $exam, ?AcademicYear $academicYear = null, ?Semester $semester = null): bool
    {
        if (! $academicYear) {
            $academicYear = AcademicYear::where('is_current', true)->first();
        }

        if (! $semester) {
            $semester = Semester::where('is_current', true)->first();
        }

        if (! $academicYear || ! $semester) {
            Log::error('ExamClearanceService: No current academic year or semester found');

            return false;
        }

        // Get the clearance threshold from the exam
        $threshold = $this->getClearanceThreshold($exam);

        // Get student's payment percentage
        $paymentPercentage = $student->getFeePaymentPercentage($academicYear->id, $semester->id);

        return $paymentPercentage >= $threshold;
    }

    /**
     * Get the clearance threshold from an exam model
     *
     * @param  Model  $exam  The exam (Exam or OfflineExam) to get threshold from
     * @return int The clearance threshold percentage
     */
    protected function getClearanceThreshold(Model $exam): int
    {
        // Default to 60% if not specified
        $threshold = 60;

        if (method_exists($exam, 'getAttribute')) {
            $threshold = $exam->getAttribute('clearance_threshold') ?? $threshold;
        }

        return $threshold;
    }

    /**
     * Process clearance for a single student and exam
     *
     * @param  Student  $student  The student to process
     * @param  Model  $exam  The exam (Exam or OfflineExam) to process
     * @param  AcademicYear|null  $academicYear  The academic year context
     * @param  Semester|null  $semester  The semester context
     * @param  bool  $manualOverride  Whether this is a manual override
     * @param  string|null  $overrideReason  Reason for the override
     * @param  int|null  $clearedById  User ID who processed the override
     * @return ExamClearance The created or updated exam clearance record
     */
    public function processClearance(
        Student $student,
        Model $exam,
        ?AcademicYear $academicYear = null,
        ?Semester $semester = null,
        bool $manualOverride = false,
        ?string $overrideReason = null,
        ?int $clearedById = null
    ): ExamClearance {
        if (! $academicYear) {
            $academicYear = AcademicYear::where('is_current', true)->first();
        }

        if (! $semester) {
            $semester = Semester::where('is_current', true)->first();
        }

        // Check if a clearance record already exists
        $clearance = ExamClearance::where('student_id', $student->id)
            ->where('clearable_type', get_class($exam))
            ->where('clearable_id', $exam->id)
            ->where('academic_year_id', $academicYear->id)
            ->where('semester_id', $semester->id)
            ->first();

        $isCleared = $manualOverride || $this->checkClearance($student, $exam, $academicYear, $semester);
        $status = $isCleared ? 'cleared' : 'denied';

        if (! $clearance) {
            // Create new clearance record
            $clearance = new ExamClearance([
                'student_id' => $student->id,
                'academic_year_id' => $academicYear->id,
                'semester_id' => $semester->id,
                'is_cleared' => $isCleared,
                'status' => $status,
                'is_manual_override' => $manualOverride,
                'override_reason' => $overrideReason,
                'cleared_by' => $clearedById,
                'cleared_at' => $isCleared ? now() : null,
                'clearance_code' => $this->generateClearanceCode($student, $exam),
            ]);

            if ($exam instanceof Exam) {
                $clearance->exam_type_id = $exam->type_id; // For backward compatibility
            } elseif ($exam instanceof OfflineExam) {
                $clearance->exam_type_id = $exam->type_id; // For backward compatibility
            }

            $exam->clearances()->save($clearance);
        } else {
            // Update existing clearance record
            $clearance->update([
                'is_cleared' => $isCleared,
                'status' => $status,
                'is_manual_override' => $manualOverride,
                'override_reason' => $manualOverride ? $overrideReason : $clearance->override_reason,
                'cleared_by' => $manualOverride ? $clearedById : $clearance->cleared_by,
                'cleared_at' => $isCleared ? now() : $clearance->cleared_at,
            ]);
        }

        // Log the clearance action
        $this->logClearanceAction($clearance, $exam, $manualOverride);

        // Generate entry ticket if cleared
        if ($isCleared) {
            $this->generateEntryTicket($clearance, $exam);
        }

        return $clearance;
    }

    /**
     * Process clearance for multiple students for an exam
     *
     * @param  array|Illuminate\Support\Collection  $students  The students to process
     * @param  Model  $exam  The exam to process
     * @param  AcademicYear|null  $academicYear  The academic year context
     * @param  Semester|null  $semester  The semester context
     * @return array The created or updated exam clearance records
     */
    public function processBulkClearance($students, Model $exam, ?AcademicYear $academicYear = null, ?Semester $semester = null): array
    {
        $results = [];

        foreach ($students as $student) {
            $results[] = $this->processClearance($student, $exam, $academicYear, $semester);
        }

        return $results;
    }

    /**
     * Generate a unique clearance code for a student and exam
     *
     * @param  Student  $student  The student
     * @param  Model  $exam  The exam
     * @return string The generated clearance code
     */
    protected function generateClearanceCode(Student $student, Model $exam): string
    {
        $prefix = $exam instanceof OfflineExam ? 'OFFEXM' : 'EXMCLR';
        $code = strtoupper(uniqid($prefix));

        return $code;
    }

    /**
     * Log a clearance action
     *
     * @param  ExamClearance  $clearance  The clearance record
     * @param  Model  $exam  The exam model
     * @param  bool  $manualOverride  Whether this was a manual override
     */
    protected function logClearanceAction(ExamClearance $clearance, Model $exam, bool $manualOverride): void
    {
        $examType = $exam instanceof OfflineExam ? 'offline' : 'online';
        $actionType = $manualOverride ? 'manual' : 'automatic';
        $clearanceStatus = $clearance->is_cleared ? 'cleared' : 'denied';

        Log::info("Student {$clearance->student_id} {$clearanceStatus} for {$examType} exam {$exam->id} via {$actionType} clearance check");
    }

    /**
     * Generate an entry ticket for a cleared exam
     *
     * @param  ExamClearance  $clearance  The clearance record
     * @param  Model  $exam  The exam model
     * @return \App\Models\ExamEntryTicket|null
     */
    protected function generateEntryTicket(ExamClearance $clearance, Model $exam)
    {
        try {
            // Check if ticket already exists
            $existingTicket = \App\Models\ExamEntryTicket::where('exam_clearance_id', $clearance->id)
                ->where('student_id', $clearance->student_id)
                ->where('ticketable_type', get_class($exam))
                ->where('ticketable_id', $exam->id)
                ->first();

            if ($existingTicket) {
                return $existingTicket;
            }

            // Create new entry ticket
            $ticket = new \App\Models\ExamEntryTicket([
                'exam_clearance_id' => $clearance->id,
                'student_id' => $clearance->student_id,
                'exam_type_id' => $clearance->exam_type_id, // For backward compatibility
                'is_verified' => false,
                'is_active' => true,
                'expires_at' => now()->addDays(7), // Default expiry of 7 days
            ]);

            $exam->examEntryTickets()->save($ticket);

            return $ticket;
        } catch (\Exception $e) {
            Log::error('Error generating entry ticket: '.$e->getMessage());

            return null;
        }
    }
}
