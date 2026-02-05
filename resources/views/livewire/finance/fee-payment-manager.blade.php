<div>
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h1 class="card-title">
                                <i class="fa fa-money-bill-wave me-2"></i> Fee Payment Manager
                            </h1>
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="card-text">Use this interface to record and manage student fee payments. Search for a student, select their bill, and record payments.</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Filters and Search -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Search & Filters</h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa fa-search"></i></span>
                                    <input type="text" class="form-control" placeholder="Search by name or student ID..." wire:model.live.debounce.300ms="search">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <select class="form-select" wire:model.live="academicYearId">
                                    <option value="">-- Academic Year --</option>
                                    @foreach($academicYears as $year)
                                        <option value="{{ $year->id }}">{{ $year->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select class="form-select" wire:model.live="semesterId">
                                    <option value="">-- Semester --</option>
                                    @foreach($semesters as $semester)
                                        <option value="{{ $semester->id }}">{{ $semester->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select class="form-select" wire:model.live="cohortId">
                                    <option value="">-- Cohort --</option>
                                    @foreach($cohorts as $cohort)
                                        <option value="{{ $cohort->id }}">{{ $cohort->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <!-- Students List -->
            <div class="col-md-5">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Students</h5>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th class="px-3">Student ID</th>
                                        <th>Name</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($students as $student)
                                        <tr class="{{ $loadedStudent && $loadedStudent->id === $student->id ? 'table-primary' : '' }}">
                                            <td class="px-3">{{ $student->student_id }}</td>
                                            <td>{{ $student->first_name }} {{ $student->last_name }}</td>
                                            <td>
                                                <button class="btn btn-sm btn-primary" wire:click="loadStudent({{ $student->id }})">
                                                    <i class="fa fa-user"></i> Select
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center">No students found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="p-3">
                            {{ $students->links() }}
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Student Bill Details -->
            <div class="col-md-7">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                @if($loadedStudent)
                                    {{ $loadedStudent->first_name }} {{ $loadedStudent->last_name }}'s Bill
                                @else
                                    Student Bill Details
                                @endif
                            </h5>
                            @if($loadedBill)
                                <button class="btn btn-success" wire:click="openPaymentForm">
                                    <i class="fa fa-plus-circle"></i> Record Payment
                                </button>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        @if($loadedStudent && $loadedStudent->feeBills->isEmpty())
                            <div class="alert alert-warning">
                                <i class="fa fa-exclamation-circle"></i> No bills found for this student in the selected academic year and semester.
                            </div>
                        @elseif($loadedBill)
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h6>Bill #{{ $loadedBill->id }}</h6>
                                    <p class="mb-1"><strong>Academic Year:</strong> {{ $loadedBill->academicYear->name }}</p>
                                    <p class="mb-1"><strong>Semester:</strong> {{ $loadedBill->semester->name }}</p>
                                    <p class="mb-1"><strong>Bill Date:</strong> {{ $loadedBill->created_at->format('M d, Y') }}</p>
                                </div>
                                <div class="col-md-6 text-md-end">
                                    <div class="mb-1">
                                        <span class="fs-6">Total Bill Amount: </span>
                                        <span class="fs-5 fw-bold">{{ number_format($loadedBill->total_amount, 2) }}</span>
                                    </div>
                                    <div class="mb-1">
                                        <span class="fs-6">Amount Paid: </span>
                                        <span class="fs-5 fw-bold text-success">{{ number_format($loadedBill->total_paid, 2) }}</span>
                                    </div>
                                    <div>
                                        <span class="fs-6">Balance: </span>
                                        <span class="fs-5 fw-bold text-danger">{{ number_format($loadedBill->balance, 2) }}</span>
                                    </div>
                                    <div class="mt-2">
                                        <span class="fs-6">Payment Status: </span>
                                        @if($loadedBill->payment_percentage >= 100)
                                            <span class="badge bg-success fs-6">PAID (100%)</span>
                                        @elseif($loadedBill->payment_percentage >= 60)
                                            <span class="badge bg-primary fs-6">PARTIAL ({{ number_format($loadedBill->payment_percentage, 1) }}%)</span>
                                        @else
                                            <span class="badge bg-danger fs-6">UNPAID ({{ number_format($loadedBill->payment_percentage, 1) }}%)</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <!-- Payment History -->
                            <h6 class="mt-4">Payment History</h6>
                            @if($loadedBill->payments->isEmpty())
                                <div class="alert alert-info">
                                    No payments have been recorded for this bill.
                                </div>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Receipt #</th>
                                                <th>Method</th>
                                                <th>Amount</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($loadedBill->payments as $payment)
                                                <tr>
                                                    <td>{{ $payment->payment_date->format('M d, Y') }}</td>
                                                    <td>{{ $payment->receipt_number }}</td>
                                                    <td>{{ $payment->payment_method }}</td>
                                                    <td class="text-end">{{ number_format($payment->amount, 2) }}</td>
                                                    <td>
                                                        <button class="btn btn-sm btn-info" wire:click="viewPayment({{ $payment->id }})">
                                                            <i class="fa fa-eye"></i> View
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        @else
                            <div class="alert alert-info text-center py-5">
                                <i class="fa fa-info-circle fa-2x mb-3"></i>
                                <p>Select a student to view their bill details and record payments.</p>
                            </div>
                        @endif
                    </div>
                </div>
                
                <!-- Recent Payments -->
                <div class="card mt-4">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Recent Payments</h5>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th class="px-3">Date</th>
                                        <th>Student</th>
                                        <th>Receipt #</th>
                                        <th>Amount</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentPayments as $payment)
                                        <tr class="align-middle">
                                            <td class="px-3">{{ $payment->payment_date->format('M d, Y') }}</td>
                                            <td>{{ $payment->student->first_name }} {{ $payment->student->last_name }}</td>
                                            <td>{{ $payment->receipt_number }}</td>
                                            <td class="text-end">{{ number_format($payment->amount, 2) }}</td>
                                            <td>
                                                <button class="btn btn-sm btn-info" wire:click="viewPayment({{ $payment->id }})">
                                                    <i class="fa fa-eye"></i> View
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center">No recent payments found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Payment Form Modal -->
    <div class="modal fade" id="paymentFormModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Record Fee Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if($loadedStudent && $loadedBill)
                        <div class="mb-3">
                            <p><strong>Student:</strong> {{ $loadedStudent->first_name }} {{ $loadedStudent->last_name }}</p>
                            <p><strong>Bill Amount:</strong> {{ number_format($loadedBill->total_amount, 2) }}</p>
                            <p><strong>Amount Paid:</strong> {{ number_format($loadedBill->total_paid, 2) }}</p>
                            <p><strong>Balance:</strong> {{ number_format($loadedBill->balance, 2) }}</p>
                        </div>
                        <hr>
                        <form>
                            <div class="mb-3">
                                <label for="paymentAmount" class="form-label">Payment Amount</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" class="form-control @error('paymentAmount') is-invalid @enderror" 
                                        id="paymentAmount" wire:model="paymentAmount">
                                </div>
                                @error('paymentAmount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label for="paymentMethod" class="form-label">Payment Method</label>
                                <select class="form-select @error('paymentMethod') is-invalid @enderror" 
                                    id="paymentMethod" wire:model="paymentMethod">
                                    @foreach($paymentMethods as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('paymentMethod')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label for="referenceNumber" class="form-label">Reference Number</label>
                                <input type="text" class="form-control @error('referenceNumber') is-invalid @enderror" 
                                    id="referenceNumber" wire:model="referenceNumber" 
                                    placeholder="Bank transfer reference, cheque number, etc.">
                                @error('referenceNumber')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label for="paymentDate" class="form-label">Payment Date</label>
                                <input type="date" class="form-control @error('paymentDate') is-invalid @enderror" 
                                    id="paymentDate" wire:model="paymentDate">
                                @error('paymentDate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label for="note" class="form-label">Note</label>
                                <textarea class="form-control @error('note') is-invalid @enderror" 
                                    id="note" wire:model="note" rows="2"></textarea>
                                @error('note')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </form>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" wire:click="recordPayment">
                        <i class="fa fa-save"></i> Record Payment
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Payment Details Modal -->
    <div class="modal fade" id="paymentDetailsModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Payment Receipt</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if($this->selectedPayment)
                        <div class="text-center mb-4 border-bottom pb-3">
                            <h4>PAYMENT RECEIPT</h4>
                            <h6>Receipt #: {{ $this->selectedPayment->receipt_number }}</h6>
                            <p class="mb-0">Date: {{ $this->selectedPayment->payment_date->format('F d, Y') }}</p>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Student:</strong></div>
                            <div class="col-sm-8">{{ $this->selectedPayment->student->first_name }} {{ $this->selectedPayment->student->last_name }}</div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Student ID:</strong></div>
                            <div class="col-sm-8">{{ $this->selectedPayment->student->student_id }}</div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Academic Year:</strong></div>
                            <div class="col-sm-8">{{ $this->selectedPayment->studentFeeBill->academicYear->name }}</div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Semester:</strong></div>
                            <div class="col-sm-8">{{ $this->selectedPayment->studentFeeBill->semester->name }}</div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Amount:</strong></div>
                            <div class="col-sm-8 fw-bold">GH₵ {{ number_format($this->selectedPayment->amount, 2) }}</div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Payment Method:</strong></div>
                            <div class="col-sm-8">{{ $this->selectedPayment->payment_method }}</div>
                        </div>
                        
                        @if($this->selectedPayment->reference_number)
                            <div class="row mb-3">
                                <div class="col-sm-4"><strong>Reference:</strong></div>
                                <div class="col-sm-8">{{ $this->selectedPayment->reference_number }}</div>
                            </div>
                        @endif
                        
                        @if($this->selectedPayment->note)
                            <div class="row mb-3">
                                <div class="col-sm-4"><strong>Note:</strong></div>
                                <div class="col-sm-8">{{ $this->selectedPayment->note }}</div>
                            </div>
                        @endif
                        
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Recorded By:</strong></div>
                            <div class="col-sm-8">{{ $this->selectedPayment->recordedBy->name }}</div>
                        </div>
                        
                        <div class="text-center mt-4">
                            <p><em>Thank you for your payment!</em></p>
                        </div>
                    @else
                        <div class="alert alert-info">
                            Loading payment details...
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="window.print()">
                        <i class="fa fa-print"></i> Print Receipt
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('livewire:initialized', () => {
            const paymentFormModal = new bootstrap.Modal(document.getElementById('paymentFormModal'));
            const paymentDetailsModal = new bootstrap.Modal(document.getElementById('paymentDetailsModal'));
            
            @this.on('show-payment-form', () => {
                paymentFormModal.show();
            });
            
            @this.on('hide-payment-form', () => {
                paymentFormModal.hide();
            });
            
            @this.on('show-payment-details', () => {
                paymentDetailsModal.show();
            });
            
            @this.on('hide-payment-details', () => {
                paymentDetailsModal.hide();
            });
            
            Livewire.hook('commit', ({ component, commit, respond, succeed, fail }) => {
                succeed(({ snapshot, effect }) => {
                    // Handle showing/hiding modals after component updates
                    if (@this.showPaymentForm) {
                        paymentFormModal.show();
                    }
                    
                    if (@this.showPaymentDetails) {
                        paymentDetailsModal.show();
                    }
                });
            });
        });
    </script>

    <style>
        @media print {
            /* Hide everything except the receipt modal content */
            body * {
                visibility: hidden;
            }
            
            #paymentDetailsModal,
            #paymentDetailsModal * {
                visibility: visible;
            }
            
            #paymentDetailsModal {
                position: fixed;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                margin: 0;
                padding: 20px;
                background: white;
            }
            
            #paymentDetailsModal .modal-dialog {
                max-width: 100%;
                margin: 0;
            }
            
            #paymentDetailsModal .modal-content {
                border: none;
                box-shadow: none;
            }
            
            /* Hide modal header buttons and footer */
            #paymentDetailsModal .modal-header .btn-close,
            #paymentDetailsModal .modal-footer {
                display: none !important;
            }
            
            /* Optimize receipt content for printing */
            #paymentDetailsModal .modal-body {
                padding: 20px;
            }
        }
    </style>
</div>
