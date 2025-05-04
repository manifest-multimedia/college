<?php

namespace App\Services;

use App\Models\Student;
use App\Models\StudentFeeBill;
use App\Models\StudentFeeBillItem;
use App\Models\FeeStructure;
use App\Models\FeeType;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\FeePayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class StudentBillingManager
{
    /**
     * Generate a fee bill for a student based on their class, academic year, and semester
     * 
     * @param Student $student
     * @param int $academicYearId
     * @param int $semesterId
     * @return StudentFeeBill
     */
    public function generateBill(Student $student, int $academicYearId, int $semesterId): StudentFeeBill
    {
        // Start a database transaction
        return DB::transaction(function () use ($student, $academicYearId, $semesterId) {
            // Check if a bill already exists for this student, academic year, and semester
            $existingBill = $student->feeBills()
                ->where('academic_year_id', $academicYearId)
                ->where('semester_id', $semesterId)
                ->first();
                
            if ($existingBill) {
                return $existingBill; // Return the existing bill
            }
            
            // Get fee structures applicable to this student's class
            $feeStructures = FeeStructure::where('college_class_id', $student->college_class_id)
                ->where('academic_year_id', $academicYearId)
                ->where('semester_id', $semesterId)
                ->where('is_active', true)
                ->get();
                
            if ($feeStructures->isEmpty()) {
                throw new \Exception('No fee structures defined for this student class, academic year, and semester.');
            }
            
            // Calculate total amount
            $totalAmount = $feeStructures->sum('amount');
            
            // Generate a unique bill reference
            $billReference = 'BILL-' . strtoupper(Str::random(8));
            
            // Create the bill
            $feeBill = StudentFeeBill::create([
                'student_id' => $student->id,
                'academic_year_id' => $academicYearId,
                'semester_id' => $semesterId,
                'total_amount' => $totalAmount,
                'amount_paid' => 0.00,
                'balance' => $totalAmount,
                'payment_percentage' => 0.00,
                'status' => 'pending',
                'billing_date' => Carbon::now(),
                'bill_reference' => $billReference,
            ]);
            
            // Create bill items for each fee structure
            foreach ($feeStructures as $feeStructure) {
                StudentFeeBillItem::create([
                    'student_fee_bill_id' => $feeBill->id,
                    'fee_type_id' => $feeStructure->fee_type_id,
                    'fee_structure_id' => $feeStructure->id,
                    'amount' => $feeStructure->amount,
                ]);
            }
            
            return $feeBill;
        });
    }
    
    /**
     * Record a fee payment for a student
     * 
     * @param StudentFeeBill $feeBill
     * @param float $amount
     * @param string $paymentMethod
     * @param string $referenceNumber
     * @param string|null $receiptNumber
     * @param string|null $note
     * @param int $recordedBy
     * @return FeePayment
     */
    public function recordPayment(
        StudentFeeBill $feeBill,
        float $amount,
        string $paymentMethod,
        string $referenceNumber,
        ?string $receiptNumber = null,
        ?string $note = null,
        int $recordedBy
    ): FeePayment {
        return DB::transaction(function () use ($feeBill, $amount, $paymentMethod, $referenceNumber, $receiptNumber, $note, $recordedBy) {
            // Create the payment record
            $payment = FeePayment::create([
                'student_fee_bill_id' => $feeBill->id,
                'student_id' => $feeBill->student_id,
                'amount' => $amount,
                'payment_method' => $paymentMethod,
                'reference_number' => $referenceNumber,
                'receipt_number' => $receiptNumber,
                'note' => $note,
                'recorded_by' => $recordedBy,
                'payment_date' => Carbon::now(),
            ]);
            
            // Update the fee bill with the new payment amount
            $newAmountPaid = $feeBill->amount_paid + $amount;
            $newBalance = $feeBill->total_amount - $newAmountPaid;
            $newPaymentPercentage = ($newAmountPaid / $feeBill->total_amount) * 100;
            
            // Determine the new status
            $status = 'pending';
            if ($newBalance <= 0) {
                $status = 'paid';
            } elseif ($newAmountPaid > 0) {
                $status = 'partially_paid';
            }
            
            $feeBill->update([
                'amount_paid' => $newAmountPaid,
                'balance' => $newBalance,
                'payment_percentage' => $newPaymentPercentage,
                'status' => $status,
            ]);
            
            return $payment;
        });
    }
    
    /**
     * Generate fee bills for all students of a specific class for the academic year and semester
     * 
     * @param int $collegeClassId
     * @param int $academicYearId
     * @param int $semesterId
     * @return array
     */
    public function generateBillsForClass(int $collegeClassId, int $academicYearId, int $semesterId): array
    {
        $students = Student::where('college_class_id', $collegeClassId)->get();
        $generatedBills = [];
        
        foreach ($students as $student) {
            try {
                $bill = $this->generateBill($student, $academicYearId, $semesterId);
                $generatedBills[] = $bill;
            } catch (\Exception $e) {
                // Log the error, but continue with the next student
                \Log::error("Failed to generate bill for student ID {$student->id}: " . $e->getMessage());
            }
        }
        
        return $generatedBills;
    }
    
    /**
     * Get a student's payment history across all fee bills
     * 
     * @param Student $student
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getStudentPaymentHistory(Student $student)
    {
        return FeePayment::where('student_id', $student->id)
            ->with(['studentFeeBill.academicYear', 'studentFeeBill.semester'])
            ->orderBy('payment_date', 'desc')
            ->get();
    }
    
    /**
     * Get all outstanding fee bills for a student
     * 
     * @param Student $student
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getOutstandingBills(Student $student)
    {
        return $student->feeBills()
            ->where('status', '!=', 'paid')
            ->with(['academicYear', 'semester'])
            ->orderBy('billing_date', 'desc')
            ->get();
    }
}