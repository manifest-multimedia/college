<?php

namespace App\Observers;

use App\Jobs\ProcessExamClearanceJob;
use App\Models\Exam;
use App\Models\FeePayment;
use App\Models\OfflineExam;
use App\Models\Student;
use Illuminate\Support\Facades\Log;

class FeePaymentObserver
{
    /**
     * Handle the FeePayment "created" event.
     */
    public function created(FeePayment $payment): void
    {
        // Recalculate the bill's payment status
        $payment->studentFeeBill?->recalculatePaymentStatus();

        $this->processStudentClearance($payment);
    }

    /**
     * Handle the FeePayment "updated" event.
     */
    public function updated(FeePayment $payment): void
    {
        // Recalculate bill status if amount changed
        if ($payment->isDirty('amount')) {
            $payment->studentFeeBill?->recalculatePaymentStatus();
        }

        // Only process if payment status is changed to confirmed/approved
        if ($payment->isDirty('status') && ($payment->status === 'confirmed' || $payment->status === 'approved')) {
            $this->processStudentClearance($payment);
        }
    }

    /**
     * Process clearance checks for all exams for this student after a payment
     */
    protected function processStudentClearance(FeePayment $payment): void
    {
        try {
            // Get the student associated with this payment
            $student = Student::find($payment->student_id);

            if (! $student) {
                Log::error("Student not found for fee payment #{$payment->id}");

                return;
            }

            // Get the current academic year and semester from the payment's bill
            $academicYearId = $payment->studentFeeBill->academic_year_id;
            $semesterId = $payment->studentFeeBill->semester_id;

            // Find all published exams that might need clearance checks
            // We'll queue up individual jobs for each exam

            // Process online exams
            $onlineExams = Exam::where('status', 'published')->get();
            foreach ($onlineExams as $exam) {
                ProcessExamClearanceJob::dispatch(
                    $exam,
                    [$student],
                    $payment->academicYear,
                    $payment->semester
                )->onQueue('exam_clearances');
            }

            // Process offline exams
            $offlineExams = OfflineExam::where('status', 'published')->get();
            foreach ($offlineExams as $exam) {
                ProcessExamClearanceJob::dispatch(
                    $exam,
                    [$student],
                    $payment->academicYear,
                    $payment->semester
                )->onQueue('exam_clearances');
            }

            Log::info("Queued clearance checks for student #{$student->id} after fee payment #{$payment->id}");
        } catch (\Exception $e) {
            Log::error('Error processing student clearance: '.$e->getMessage());
        }
    }
}
