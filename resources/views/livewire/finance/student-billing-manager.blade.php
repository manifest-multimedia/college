<div>
    <div class="row">
        <!-- Fee Billing Management Card -->
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h1 class="card-title">
                        <i class="fas fa-file-invoice-dollar mr-2"></i> Student Fee Billing
                    </h1>
                    <div class="card-tools">
                        <button class="btn btn-primary" data-toggle="modal" data-target="#batchBillingModal">
                            <i class="fas fa-money-check-alt"></i> Generate Batch Bills
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Academic Year</label>
                                <select class="form-control" wire:model="academicYearId">
                                    <option value="">All Academic Years</option>
                                    @foreach($academicYears as $year)
                                        <option value="{{ $year->id }}">{{ $year->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Semester</label>
                                <select class="form-control" wire:model="semesterId">
                                    <option value="">All Semesters</option>
                                    @foreach($semesters as $semester)
                                        <option value="{{ $semester->id }}">{{ $semester->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Class</label>
                                <select class="form-control" wire:model="collegeClassId">
                                    <option value="">All Classes</option>
                                    @foreach($classes as $class)
                                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Search</label>
                                <input type="text" class="form-control" placeholder="Search student name, ID..." wire:model.debounce.300ms="search">
                            </div>
                        </div>
                    </div>

                    <!-- Billing Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Student ID</th>
                                    <th>Student Name</th>
                                    <th>Academic Year</th>
                                    <th>Semester</th>
                                    <th>Total Amount</th>
                                    <th>Amount Paid</th>
                                    <th>Balance</th>
                                    <th>Payment Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($bills as $bill)
                                    <tr>
                                        <td>{{ $bill->student->student_id }}</td>
                                        <td>{{ $bill->student->full_name }}</td>
                                        <td>{{ $bill->academicYear->name }}</td>
                                        <td>{{ $bill->semester->name }}</td>
                                        <td>{{ number_format($bill->total_amount, 2) }}</td>
                                        <td>{{ number_format($bill->amount_paid, 2) }}</td>
                                        <td>{{ number_format($bill->balance, 2) }}</td>
                                        <td>
                                            @if($bill->payment_status === 'unpaid')
                                                <span class="badge badge-danger">Unpaid</span>
                                            @elseif($bill->payment_status === 'partial')
                                                <span class="badge badge-warning">Partial</span>
                                            @elseif($bill->payment_status === 'complete')
                                                <span class="badge badge-success">Complete</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <button class="btn btn-sm btn-info" wire:click="viewBill({{ $bill->id }})">
                                                    <i class="fas fa-eye"></i> View
                                                </button>
                                                <a href="{{ route('finance.payment.create', ['billId' => $bill->id]) }}" class="btn btn-sm btn-success">
                                                    <i class="fas fa-money-bill"></i> Record Payment
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">No bills found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination Links -->
                    <div class="mt-4">
                        {{ $bills->links() }}
                    </div>

                    <!-- Students Without Bills Section (if applicable) -->
                    @if($studentsWithoutBills->count() > 0 && $academicYearId && $semesterId && $collegeClassId)
                        <div class="mt-5">
                            <h5>Students Without Bills for Selected Filters</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Student ID</th>
                                            <th>Student Name</th>
                                            <th>Class</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($studentsWithoutBills as $student)
                                            <tr>
                                                <td>{{ $student->student_id }}</td>
                                                <td>{{ $student->full_name }}</td>
                                                <td>{{ $student->collegeClass->name }}</td>
                                                <td>
                                                    <button class="btn btn-sm btn-primary" wire:click="generateBillForStudent({{ $student->id }})">
                                                        <i class="fas fa-file-invoice"></i> Generate Bill
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Batch Billing Modal -->
    <div class="modal fade" id="batchBillingModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Generate Batch Bills</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="form-group">
                            <label>Academic Year</label>
                            <select class="form-control @error('selectedAcademicYearId') is-invalid @enderror" 
                                    wire:model="selectedAcademicYearId">
                                <option value="">Select Academic Year</option>
                                @foreach($academicYears as $year)
                                    <option value="{{ $year->id }}">{{ $year->name }}</option>
                                @endforeach
                            </select>
                            @error('selectedAcademicYearId')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label>Semester</label>
                            <select class="form-control @error('selectedSemesterId') is-invalid @enderror" 
                                    wire:model="selectedSemesterId">
                                <option value="">Select Semester</option>
                                @foreach($semesters as $semester)
                                    <option value="{{ $semester->id }}">{{ $semester->name }}</option>
                                @endforeach
                            </select>
                            @error('selectedSemesterId')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label>Class</label>
                            <select class="form-control @error('selectedCollegeClassId') is-invalid @enderror" 
                                    wire:model="selectedCollegeClassId">
                                <option value="">Select Class</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                                @endforeach
                            </select>
                            @error('selectedCollegeClassId')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" wire:click="confirmBatchBilling">
                        Generate Bills
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Batch Billing Confirmation Modal -->
    @if($showBatchBillingConfirmation)
        <div class="modal fade show" style="display: block; background-color: rgba(0,0,0,0.5);" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirm Batch Billing</h5>
                        <button type="button" class="close" wire:click="cancelBatchBilling">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to generate fee bills for all students in 
                            <strong>{{ CollegeClass::find($selectedCollegeClassId)->name ?? '' }}</strong> 
                            for <strong>{{ Semester::find($selectedSemesterId)->name ?? '' }}</strong> 
                            of <strong>{{ AcademicYear::find($selectedAcademicYearId)->name ?? '' }}</strong>?
                        </p>
                        <p class="text-danger">
                            <i class="fas fa-exclamation-triangle"></i> 
                            This action will create bills for all students without existing bills for the selected criteria.
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="cancelBatchBilling">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="generateBatchBills">
                            <i class="fas fa-check"></i> Confirm and Generate
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Billing Result Modal -->
    @if($showBillingResult)
        <div class="modal fade show" style="display: block; background-color: rgba(0,0,0,0.5);" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Billing Results</h5>
                        <button type="button" class="close" wire:click="closeBillingResult">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="text-center">
                            <i class="fas fa-check-circle text-success fa-4x mb-3"></i>
                            <h4>Bills Generated Successfully!</h4>
                            <p>Generated bills for <strong>{{ $billingResult['totalStudents'] ?? 0 }}</strong> students in 
                              <strong>{{ $billingResult['class'] ?? '' }}</strong> for 
                              <strong>{{ $billingResult['semester'] ?? '' }}</strong> of 
                              <strong>{{ $billingResult['academicYear'] ?? '' }}</strong>
                            </p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" wire:click="closeBillingResult">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- JavaScript for modals -->
    <script>
        document.addEventListener('livewire:load', function () {
            window.addEventListener('notify', event => {
                toastr[event.detail.type](event.detail.message);
            });
        });
    </script>
</div>