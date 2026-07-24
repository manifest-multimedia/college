<?php

namespace App\Livewire\Reports;

use App\Services\Reports\ReportDiscoveryService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReportExport;

class ReportManager extends Component
{
    public $selectedReportId = null;

    public $filters = [];

    public $reportData = null;

    public $columns = [];

    public function mount()
    {
        // Initialization if needed
    }

    #[Computed]
    public function reportsByModule()
    {
        $discoveryService = app(ReportDiscoveryService::class);

        return $discoveryService->getReportsByModule();
    }

    public function selectReport($reportId)
    {
        $this->selectedReportId = $reportId;
        $this->reportData = null;
        $this->columns = [];

        $report = $this->getSelectedReport();
        if ($report) {
            $this->filters = $report->getDefaultFilters();
        }
    }

    public function getSelectedReport()
    {
        if (! $this->selectedReportId) {
            return null;
        }

        $discoveryService = app(ReportDiscoveryService::class);

        return $discoveryService->getReportById($this->selectedReportId);
    }

    public function generateReport()
    {
        $report = $this->getSelectedReport();
        if (! $report) {
            return;
        }

        $rules = $report->getValidationRules();

        if (! empty($rules)) {
            $validatedData = Validator::make($this->filters, $rules)->validate();
        }

        if (method_exists($report, 'setFilters')) {
            $report->setFilters($this->filters);
        }

        $this->columns = $report->getColumns();
        $this->reportData = $report->generateData($this->filters)->toArray();
    }

    public function exportPdf()
    {
        $report = $this->getSelectedReport();
        if (! $report || ! $this->reportData) {
            return;
        }

        $pdf = Pdf::loadView('reports.export.pdf', [
            'report' => $report,
            'data' => $this->reportData,
            'columns' => $this->columns,
            'filters' => $this->filters,
        ]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $report->getName().'.pdf');
    }

    public function exportExcel()
    {
        $report = $this->getSelectedReport();
        if (! $report || ! $this->reportData) {
            return;
        }

        $template = method_exists($report, 'getExcelTemplate') 
            ? $report->getExcelTemplate() 
            : 'reports.export.excel';

        return Excel::download(new ReportExport($template, [
            'report' => $report,
            'data' => $this->reportData,
            'columns' => $this->columns,
            'filters' => $this->filters,
        ]), $report->getName().'.xlsx');
    }

    public function render()
    {
        $discoveryService = app(ReportDiscoveryService::class);

        return view('livewire.reports.report-manager', [
            'reportsByModule' => $discoveryService->getReportsByModule(),
            'selectedReport' => $this->getSelectedReport(),
        ])->layout('components.dashboard.default', [
            'title' => 'Reports',
            'description' => 'Manage and generate reports',
        ]);
    }
}
