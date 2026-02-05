<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\CollegeClass;
use App\Models\FeeStructure;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentFeeBill;
use App\Models\StudentFeeBillItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class StudentBillingService
{
    /**
     * Generate a bill for a specific student
     *
     * @param  int  $academicYearId
     * @param  int  $semesterId
     * @param  array|null  $selectedFeeStructureIds  Optional array of fee structure IDs to include
     * @return StudentFeeBill
     */
    public function generateBill(Student $student, $academicYearId, $semesterId, ?array $selectedFeeStructureIds = null)
    {
        // Start a transaction to ensure data integrity
        return DB::transaction(function () use ($student, $academicYearId, $semesterId, $selectedFeeStructureIds) {
            // Check if student already has a bill for this academic year and semester
            $existingBill = StudentFeeBill::where('student_id', $student->id)
                ->where('academic_year_id', $academicYearId)
                ->where('semester_id', $semesterId)
                ->first();

            if ($existingBill) {
                throw new \Exception('Student already has a bill for this academic year and semester');
            }

            // Get all applicable fee structures for this student's class
            $feeStructuresQuery = FeeStructure::where('college_class_id', $student->college_class_id)
                ->where('academic_year_id', $academicYearId)
                ->where('semester_id', $semesterId)
                ->where('is_active', true);

            // If specific fees are selected, filter by those IDs
            if ($selectedFeeStructureIds !== null && ! empty($selectedFeeStructureIds)) {
                $feeStructuresQuery->whereIn('id', $selectedFeeStructureIds);
            }

            $feeStructures = $feeStructuresQuery->get();

            if ($feeStructures->isEmpty()) {
                throw new \Exception('No fee structures selected or available for this class, academic year, and semester');
            }

            // Calculate total amount
            $totalAmount = $feeStructures->sum('amount');

            // Create the bill record
            $bill = new StudentFeeBill;
            $bill->student_id = $student->id;
            $bill->academic_year_id = $academicYearId;
            $bill->semester_id = $semesterId;
            $bill->total_amount = $totalAmount;
            $bill->amount_paid = 0.00;
            $bill->balance = $totalAmount;
            $bill->payment_percentage = 0.00;
            $bill->status = 'pending';
            $bill->billing_date = Carbon::now();
            $bill->bill_reference = 'BILL-'.Str::upper(Str::random(8));
            $bill->save();

            // Create bill items for each fee type
            foreach ($feeStructures as $feeStructure) {
                $billItem = new StudentFeeBillItem;
                $billItem->student_fee_bill_id = $bill->id;
                $billItem->fee_type_id = $feeStructure->fee_type_id;
                $billItem->fee_structure_id = $feeStructure->id;
                $billItem->amount = $feeStructure->amount;
                $billItem->save();
            }

            return $bill;
        });
    }

    /**
     * Generate bills for all students in a class
     *
     * @param  int  $classId
     * @param  int  $academicYearId
     * @param  int  $semesterId
     * @return array
     */
    public function generateBillsForClass($classId, $academicYearId, $semesterId)
    {
        $class = CollegeClass::findOrFail($classId);
        $academicYear = AcademicYear::findOrFail($academicYearId);
        $semester = Semester::findOrFail($semesterId);

        // Get all students in the class who don't already have bills for this semester
        $students = Student::where('college_class_id', $classId)
            ->whereDoesntHave('feeBills', function ($query) use ($academicYearId, $semesterId) {
                $query->where('academic_year_id', $academicYearId)
                    ->where('semester_id', $semesterId);
            })
            ->get();

        if ($students->isEmpty()) {
            throw new \Exception('No students found without existing bills for this class, academic year, and semester');
        }

        $generatedBills = [];

        foreach ($students as $student) {
            try {
                $bill = $this->generateBill($student, $academicYearId, $semesterId);
                $generatedBills[] = $bill;

                Log::info("Bill generated for student {$student->id} ({$student->full_name})");
            } catch (\Exception $e) {
                Log::error("Failed to generate bill for student {$student->id}: {$e->getMessage()}");
                // Continue with next student
            }
        }

        return $generatedBills;
    }

    /**
     * Update payment status and percentages for a bill
     *
     * @return StudentFeeBill
     */
    public function updateBillPaymentStatus(StudentFeeBill $bill)
    {
        $totalAmount = $bill->total_amount;
        $amountPaid = $bill->feePayments()->sum('amount');
        $balance = $totalAmount - $amountPaid;
        $paymentPercentage = $totalAmount > 0 ? ($amountPaid / $totalAmount * 100) : 0;

        // Update status based on payment percentage
        if ($paymentPercentage >= 99.99) {
            $status = 'paid'; // Fully paid
        } elseif ($paymentPercentage > 0) {
            $status = 'partially_paid'; // Partially paid
        } else {
            $status = 'pending'; // No payment made
        }

        // Update the bill
        $bill->amount_paid = $amountPaid;
        $bill->balance = $balance;
        $bill->payment_percentage = $paymentPercentage;
        $bill->status = $status;
        $bill->save();

        return $bill;
    }
}
