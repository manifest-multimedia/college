<?php

namespace App\Exports;

use App\Models\FeePayment;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class FeeCollectionExport implements FromQuery, WithHeadings, WithMapping, WithTitle
{
    use Exportable;
    
    protected $academicYearId;
    protected $semesterId;
    protected $collegeClassId;
    protected $feeTypeId;
    protected $startDate;
    protected $endDate;
    
    public function __construct($academicYearId, $semesterId, $collegeClassId = null, $feeTypeId = null, $startDate = null, $endDate = null)
    {
        $this->academicYearId = $academicYearId;
        $this->semesterId = $semesterId;
        $this->collegeClassId = $collegeClassId;
        $this->feeTypeId = $feeTypeId;
        $this->startDate = $startDate ?? now()->subMonth()->format('Y-m-d');
        $this->endDate = $endDate ?? now()->format('Y-m-d');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query()
    {
        return FeePayment::query()
            ->with(['student', 'studentFeeBill.feeType', 'recordedBy'])
            ->when($this->academicYearId, function($query) {
                $query->whereHas('studentFeeBill', function($q) {
                    $q->where('academic_year_id', $this->academicYearId);
                });
            })
            ->when($this->semesterId, function($query) {
                $query->whereHas('studentFeeBill', function($q) {
                    $q->where('semester_id', $this->semesterId);
                });
            })
            ->when($this->collegeClassId, function($query) {
                $query->whereHas('student', function($q) {
                    $q->where('college_class_id', $this->collegeClassId);
                });
            })
            ->when($this->feeTypeId, function($query) {
                $query->whereHas('studentFeeBill.billItems', function($q) {
                    $q->where('fee_type_id', $this->feeTypeId);
                });
            })
            ->whereBetween('payment_date', [$this->startDate, $this->endDate])
            ->orderBy('payment_date', 'desc');
    }
    
    /**
     * @var FeePayment $payment
     */
    public function map($payment): array
    {
        return [
            $payment->payment_date->format('Y-m-d'),
            $payment->receipt_number,
            $payment->student->student_id,
            $payment->student->first_name . ' ' . $payment->student->last_name,
            $payment->studentFeeBill->academicYear->name ?? '',
            $payment->studentFeeBill->semester->name ?? '',
            $payment->payment_method,
            $payment->reference_number,
            $payment->amount,
            $payment->recordedBy->name ?? 'System',
        ];
    }
    
    public function headings(): array
    {
        return [
            'Date',
            'Receipt Number',
            'Student ID',
            'Student Name',
            'Academic Year',
            'Semester',
            'Payment Method',
            'Reference',
            'Amount',
            'Recorded By'
        ];
    }
    
    public function title(): string
    {
        return 'Fee Collections';
    }
}