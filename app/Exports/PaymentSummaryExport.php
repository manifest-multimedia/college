<?php

namespace App\Exports;

use App\Models\FeePayment;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class PaymentSummaryExport implements FromQuery, WithHeadings, WithMapping, WithTitle
{
    use Exportable;

    protected $academicYearId;

    protected $semesterId;

    protected $collegeClassId;

    protected $cohortId;

    protected $startDate;

    protected $endDate;

    public function __construct(
        $academicYearId,
        $semesterId,
        $collegeClassId = null,
        $cohortId = null,
        $startDate = null,
        $endDate = null
    ) {
        $this->academicYearId = $academicYearId;
        $this->semesterId = $semesterId;
        $this->collegeClassId = $collegeClassId;
        $this->cohortId = $cohortId;
        $this->startDate = $startDate ?? now()->subMonth()->format('Y-m-d');
        $this->endDate = $endDate ?? now()->format('Y-m-d');
    }

    public function query()
    {
        return FeePayment::query()
            ->select(
                DB::raw('DATE(payment_date) as payment_date'),
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('COUNT(*) as payment_count')
            )
            ->when($this->academicYearId, function ($query) {
                $query->whereHas('studentFeeBill', function ($q) {
                    $q->where('academic_year_id', $this->academicYearId);
                });
            })
            ->when($this->semesterId, function ($query) {
                $query->whereHas('studentFeeBill', function ($q) {
                    $q->where('semester_id', $this->semesterId);
                });
            })
            ->when($this->collegeClassId, function ($query) {
                $query->whereHas('student', function ($q) {
                    $q->where('college_class_id', $this->collegeClassId);
                });
            })
            ->when($this->cohortId, function ($query) {
                $query->whereHas('student', function ($q) {
                    $q->where('cohort_id', $this->cohortId);
                });
            })
            ->whereBetween('payment_date', [$this->startDate, $this->endDate])
            ->groupBy(DB::raw('DATE(payment_date)'))
            ->orderBy('payment_date', 'desc');
    }

    public function map($summary): array
    {
        return [
            $summary->payment_date,
            (int) $summary->payment_count,
            (float) $summary->total_amount,
        ];
    }

    public function headings(): array
    {
        return [
            'Date',
            'Number of Payments',
            'Total Amount',
        ];
    }

    public function title(): string
    {
        return 'Payment Summary';
    }
}
