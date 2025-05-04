<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h1 class="card-title">
                            <i class="fa fa-chart-bar me-2"></i> Financial Reports
                        </h1>
                    </div>
                </div>
                <div class="card-body">
                    <p class="card-text">Generate financial reports for fee collections, outstanding fees, and payment summaries.</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Report Selection & Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Report Parameters</h5>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="reportType" class="form-label">Report Type</label>
                            <select id="reportType" class="form-select" wire:model.live="reportType">
                                <option value="fee_collection">Fee Collection Report</option>
                                <option value="outstanding_fees">Outstanding Fees Report</option>
                                <option value="payment_summary">Payment Summary Report</option>
                            </select>
                        </div>
                        
                        <div class="col-md-4">
                            <label for="academicYearId" class="form-label">Academic Year</label>
                            <select id="academicYearId" class="form-select" wire:model.live="academicYearId">
                                <option value="">All Academic Years</option>
                                @foreach($academicYears as $year)
                                    <option value="{{ $year->id }}">{{ $year->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-4">
                            <label for="semesterId" class="form-label">Semester</label>
                            <select id="semesterId" class="form-select" wire:model.live="semesterId">
                                <option value="">All Semesters</option>
                                @foreach($semesters as $semester)
                                    <option value="{{ $semester->id }}">{{ $semester->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="collegeClassId" class="form-label">Class</label>
                            <select id="collegeClassId" class="form-select" wire:model.live="collegeClassId">
                                <option value="">All Classes</option>
                                @foreach($collegeClasses as $class)
                                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        @if($reportType === 'fee_collection')
                        <div class="col-md-4">
                            <label for="feeTypeId" class="form-label">Fee Type</label>
                            <select id="feeTypeId" class="form-select" wire:model.live="feeTypeId">
                                <option value="">All Fee Types</option>
                                @foreach($feeTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                        
                        @if($reportType !== 'outstanding_fees')
                        <div class="col-md-{{ $reportType === 'fee_collection' ? '2' : '4' }}">
                            <label for="startDate" class="form-label">Start Date</label>
                            <input type="date" id="startDate" class="form-control" wire:model.live="startDate">
                        </div>
                        
                        <div class="col-md-{{ $reportType === 'fee_collection' ? '2' : '4' }}">
                            <label for="endDate" class="form-label">End Date</label>
                            <input type="date" id="endDate" class="form-control" wire:model.live="endDate">
                        </div>
                        @endif
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <label for="exportFormat" class="form-label">Export Format</label>
                            <div class="d-flex">
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="exportFormat" id="formatExcel" value="excel" wire:model="exportFormat" checked>
                                    <label class="form-check-label" for="formatExcel">
                                        Excel
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="exportFormat" id="formatPdf" value="pdf" wire:model="exportFormat">
                                    <label class="form-check-label" for="formatPdf">
                                        PDF
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-8 d-flex align-items-end justify-content-end">
                            <button type="button" class="btn btn-primary" wire:click="generateReport" wire:loading.attr="disabled">
                                <i class="fa fa-file-export me-1"></i>
                                <span wire:loading.remove wire:target="generateReport">Export Report</span>
                                <span wire:loading wire:target="generateReport">Processing...</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Report Preview -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Report Preview</h5>
                    </div>
                </div>
                <div class="card-body">
                    @if (session()->has('message'))
                        <div class="alert alert-success">
                            {{ session('message') }}
                        </div>
                    @endif
                    
                    @if (session()->has('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif
                    
                    @if($reportType === 'fee_collection' && $reportData && count($reportData))
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Receipt #</th>
                                        <th>Student</th>
                                        <th>Academic Year</th>
                                        <th>Semester</th>
                                        <th>Payment Method</th>
                                        <th>Reference</th>
                                        <th>Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($reportData as $payment)
                                        <tr>
                                            <td>{{ $payment->payment_date->format('Y-m-d') }}</td>
                                            <td>{{ $payment->receipt_number }}</td>
                                            <td>{{ $payment->student->first_name }} {{ $payment->student->last_name }}</td>
                                            <td>{{ $payment->studentFeeBill->academicYear->name ?? '' }}</td>
                                            <td>{{ $payment->studentFeeBill->semester->name ?? '' }}</td>
                                            <td>{{ $payment->payment_method }}</td>
                                            <td>{{ $payment->reference_number }}</td>
                                            <td class="text-end">{{ number_format($payment->amount, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="7" class="text-end">Total:</th>
                                        <th class="text-end">{{ number_format($reportData->sum('amount'), 2) }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        
                        <div class="d-flex justify-content-center mt-3">
                            {{ $reportData->links() }}
                        </div>
                    @elseif($reportType === 'outstanding_fees' && $reportData && count($reportData))
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th>Student ID</th>
                                        <th>Student Name</th>
                                        <th>Class</th>
                                        <th>Academic Year</th>
                                        <th>Semester</th>
                                        <th>Total Bill</th>
                                        <th>Paid</th>
                                        <th>Balance</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($reportData as $bill)
                                        <tr>
                                            <td>{{ $bill->student->student_id ?? 'N/A' }}</td>
                                            <td>{{ $bill->student->first_name }} {{ $bill->student->last_name }}</td>
                                            <td>{{ $bill->student->collegeClass->name ?? 'N/A' }}</td>
                                            <td>{{ $bill->academicYear->name }}</td>
                                            <td>{{ $bill->semester->name }}</td>
                                            <td class="text-end">{{ number_format($bill->total_amount, 2) }}</td>
                                            <td class="text-end">{{ number_format($bill->total_paid, 2) }}</td>
                                            <td class="text-end">{{ number_format($bill->balance, 2) }}</td>
                                            <td>
                                                @if($bill->payment_percentage >= 100)
                                                    <span class="badge bg-success">PAID</span>
                                                @elseif($bill->payment_percentage >= 60)
                                                    <span class="badge bg-primary">PARTIAL ({{ number_format($bill->payment_percentage, 1) }}%)</span>
                                                @else
                                                    <span class="badge bg-danger">UNPAID ({{ number_format($bill->payment_percentage, 1) }}%)</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="5" class="text-end">Totals:</th>
                                        <th class="text-end">{{ number_format($reportData->sum('total_amount'), 2) }}</th>
                                        <th class="text-end">{{ number_format($reportData->sum('total_paid'), 2) }}</th>
                                        <th class="text-end">{{ number_format($reportData->sum('balance'), 2) }}</th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        
                        <div class="d-flex justify-content-center mt-3">
                            {{ $reportData->links() }}
                        </div>
                    @elseif($reportType === 'payment_summary' && $reportData && count($reportData))
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Number of Payments</th>
                                        <th>Total Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($reportData as $summary)
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($summary->payment_date)->format('Y-m-d') }}</td>
                                            <td class="text-center">{{ $summary->payment_count }}</td>
                                            <td class="text-end">{{ number_format($summary->total_amount, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th class="text-end">Totals:</th>
                                        <th class="text-center">{{ $reportData->sum('payment_count') }}</th>
                                        <th class="text-end">{{ number_format($reportData->sum('total_amount'), 2) }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        
                        <div class="d-flex justify-content-center mt-3">
                            {{ $reportData->links() }}
                        </div>
                    @else
                        <div class="alert alert-info text-center">
                            <i class="fa fa-info-circle fa-2x mb-3"></i>
                            <p>Select your report parameters and click 'Export Report' to generate a report.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
