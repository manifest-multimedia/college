<?php

namespace App\Exports;

use App\Models\StudentFeeBill;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class OutstandingFeesExport implements FromQuery, WithHeadings, WithMapping, WithTitle
{
    use Exportable;
    
    protected $academicYearId;
    protected $semesterId;
    protected $collegeClassId;
    
    public function __construct($academicYearId, $semesterId, $collegeClassId = null)
    {
        $this->academicYearId = $academicYearId;
        $this->semesterId = $semesterId;
        $this->collegeClassId = $collegeClassId;
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query()
    {
        return StudentFeeBill::query()
            ->with(['student', 'academicYear', 'semester'])
            ->where('academic_year_id', $this->academicYearId)
            ->where('semester_id', $this->semesterId)
            ->when($this->collegeClassId, function($query) {
                $query->whereHas('student', function($q) {
                    $q->where('college_class_id', $this->collegeClassId);
                });
            })
            ->where('balance', '>', 0)
            ->orderBy('balance', 'desc');
    }
    
    /**
     * @var StudentFeeBill $bill
     */
    public function map($bill): array
    {
        // Calculate payment percentage
        $paymentPercentage = 0;
        if ($bill->total_amount > 0) {
            $paymentPercentage = ($bill->total_paid / $bill->total_amount) * 100;
        }
        
        return [
            $bill->student->student_id ?? 'N/A',
            $bill->student->first_name . ' ' . $bill->student->last_name,
            $bill->student->collegeClass->name ?? 'N/A',
            $bill->academicYear->name,
            $bill->semester->name,
            $bill->billing_date ? $bill->billing_date->format('Y-m-d') : 'N/A',
            $bill->total_amount,
            $bill->total_paid,
            $bill->balance,
            number_format($paymentPercentage, 1) . '%',
        ];
    }
    
    public function headings(): array
    {
        return [
            'Student ID',
            'Student Name',
            'Class',
            'Academic Year',
            'Semester',
            'Billing Date',
            'Total Amount',
            'Amount Paid',
            'Outstanding Balance',
            'Payment Status'
        ];
    }
    
    public function title(): string
    {
        return 'Outstanding Fees';
    }
}