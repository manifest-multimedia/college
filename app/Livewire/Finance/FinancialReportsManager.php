<?php

namespace App\Livewire\Finance;

use App\Models\AcademicYear;
use App\Models\CollegeClass;
use App\Models\FeePayment;
use App\Models\FeeType;
use App\Models\Semester;
use App\Models\StudentFeeBill;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use App\Exports\FeeCollectionExport;
use App\Exports\OutstandingFeesExport;
use Maatwebsite\Excel\Facades\Excel;

class FinancialReportsManager extends Component
{
    use WithPagination;
    
    // Filter properties
    public $reportType = 'fee_collection';
    public $academicYearId;
    public $semesterId;
    public $collegeClassId;
    public $feeTypeId;
    public $startDate;
    public $endDate;
    public $exportFormat = 'excel'; // 'excel' or 'pdf'
    
    // Export flags
    public $processing = false;
    
    public function mount()
    {
        // Set default values
        $this->academicYearId = AcademicYear::orderBy('year', 'desc')->first()?->id;
        $this->semesterId = Semester::where('is_current', true)->first()?->id ?? 
                        Semester::orderBy('id', 'desc')->first()?->id;
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->format('Y-m-d');
    }
    
    public function updatedReportType()
    {
        // Reset pagination when changing report type
        $this->resetPage();
    }
    
    public function generateReport()
    {
        $this->validate([
            'reportType' => 'required|in:fee_collection,outstanding_fees,payment_summary',
            'academicYearId' => 'required|exists:academic_years,id',
            'semesterId' => 'required|exists:semesters,id',
            'startDate' => 'required|date',
            'endDate' => 'required|date|after_or_equal:startDate',
            'exportFormat' => 'required|in:excel,pdf',
        ]);
        
        $this->processing = true;
        
        try {
            switch ($this->reportType) {
                case 'fee_collection':
                    $this->generateFeeCollectionReport();
                    break;
                case 'outstanding_fees':
                    $this->generateOutstandingFeesReport();
                    break;
                case 'payment_summary':
                    $this->generatePaymentSummaryReport();
                    break;
            }
            
            $this->processing = false;
        } catch (\Exception $e) {
            $this->processing = false;
            session()->flash('error', 'Error generating report: ' . $e->getMessage());
        }
    }
    
    private function generateFeeCollectionReport()
    {
        $filename = 'fee_collections_' . Carbon::now()->format('YmdHis');
        
        if ($this->exportFormat == 'excel') {
            return Excel::download(
                new FeeCollectionExport(
                    $this->academicYearId, 
                    $this->semesterId, 
                    $this->collegeClassId,
                    $this->feeTypeId,
                    $this->startDate,
                    $this->endDate
                ), 
                $filename . '.xlsx'
            );
        } else {
            // Use PDF export logic (you'll need to implement this class)
            return Excel::download(
                new FeeCollectionExport(
                    $this->academicYearId, 
                    $this->semesterId, 
                    $this->collegeClassId,
                    $this->feeTypeId,
                    $this->startDate,
                    $this->endDate
                ), 
                $filename . '.pdf',
                \Maatwebsite\Excel\Excel::DOMPDF
            );
        }
    }
    
    private function generateOutstandingFeesReport()
    {
        $filename = 'outstanding_fees_' . Carbon::now()->format('YmdHis');
        
        if ($this->exportFormat == 'excel') {
            return Excel::download(
                new OutstandingFeesExport(
                    $this->academicYearId, 
                    $this->semesterId, 
                    $this->collegeClassId
                ), 
                $filename . '.xlsx'
            );
        } else {
            // Use PDF export logic
            return Excel::download(
                new OutstandingFeesExport(
                    $this->academicYearId, 
                    $this->semesterId, 
                    $this->collegeClassId
                ), 
                $filename . '.pdf',
                \Maatwebsite\Excel\Excel::DOMPDF
            );
        }
    }
    
    private function generatePaymentSummaryReport()
    {
        // For now, we'll show a preview of the report in the UI
        session()->flash('message', 'Payment summary report preview loaded. Click Export to download.');
        return;
    }
    
    public function getFeeCollectionsProperty()
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
                $query->whereHas('studentFeeBill', function($q) {
                    $q->where('fee_type_id', $this->feeTypeId);
                });
            })
            ->whereBetween('payment_date', [$this->startDate, $this->endDate])
            ->orderBy('payment_date', 'desc')
            ->paginate(15);
    }
    
    public function getOutstandingFeesProperty()
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
            ->orderBy('balance', 'desc')
            ->paginate(15);
    }
    
    public function getPaymentSummaryProperty()
    {
        return FeePayment::query()
            ->select(
                DB::raw('DATE(payment_date) as payment_date'),
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('COUNT(*) as payment_count')
            )
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
            ->whereBetween('payment_date', [$this->startDate, $this->endDate])
            ->groupBy(DB::raw('DATE(payment_date)'))
            ->orderBy('payment_date', 'desc')
            ->paginate(15);
    }
    
    public function getAcademicYearsProperty()
    {
        return AcademicYear::orderBy('year', 'desc')->get();
    }
    
    public function getSemestersProperty()
    {
        return Semester::orderBy('id')->get();
    }
    
    public function getCollegeClassesProperty()
    {
        return CollegeClass::orderBy('name')->get();
    }
    
    public function getFeeTypesProperty()
    {
        return FeeType::where('is_active', true)->orderBy('name')->get();
    }
    
    public function render()
    {
        $reportData = null;
        
        switch ($this->reportType) {
            case 'fee_collection':
                $reportData = $this->feeCollections;
                break;
            case 'outstanding_fees':
                $reportData = $this->outstandingFees;
                break;
            case 'payment_summary':
                $reportData = $this->paymentSummary;
                break;
        }
        
        return view('livewire.finance.financial-reports-manager', [
            'reportData' => $reportData,
            'academicYears' => $this->academicYears,
            'semesters' => $this->semesters,
            'collegeClasses' => $this->collegeClasses,
            'feeTypes' => $this->feeTypes,
        ])->layout('components.dashboard.default');
    }
}
