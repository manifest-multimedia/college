<div>
    <x-components.dashboard.default>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h1 class="card-title">
                            <i class="fas fa-money-bill-wave me-2"></i> Student Billing Manager
                        </h1>
                    </div>
                    <div class="card-body">
                        <!-- Alerts -->
                        @if($successMessage)
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ $successMessage }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @if($errorMessage)
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ $errorMessage }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <!-- Tabs -->
                        <ul class="nav nav-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="bills-tab" data-bs-toggle="tab" href="#bills" role="tab">Fee Bills</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="manage-tab" data-bs-toggle="tab" href="#manage" role="tab">Manage Fee Types</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="generate-tab" data-bs-toggle="tab" href="#generate" role="tab">Bulk Generate Bills</a>
                            </li>
                        </ul>

                        <div class="tab-content">
                            <!-- Bills Tab -->
                            <div class="tab-pane fade show active" id="bills" role="tabpanel">
                                <div class="row mt-3">
                                    <div class="col-md-3">
                                        <div class="input-group mb-3">
                                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                                            <input wire:model.live.debounce.300ms="search" type="text" class="form-control" placeholder="Search students...">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <select wire:model.live="selectedClass" class="form-select mb-3">
                                            <option value="">All Classes</option>
                                            @foreach($collegeClasses as $class)
                                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <select wire:model.live="selectedAcademicYear" class="form-select mb-3">
                                            <option value="">All Academic Years</option>
                                            @foreach($academicYears as $year)
                                                <option value="{{ $year->id }}">{{ $year->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <select wire:model.live="selectedSemester" class="form-select mb-3">
                                            <option value="">All Semesters</option>
                                            @foreach($semesters as $semester)
                                                <option value="{{ $semester->id }}">{{ $semester->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <select wire:model.live="statusFilter" class="form-select mb-3">
                                            <option value="">All Statuses</option>
                                            <option value="pending">Pending</option>
                                            <option value="partial">Partial</option>
                                            <option value="paid">Paid</option>
                                            <option value="overdue">Overdue</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Student ID</th>
                                                <th>Student Name</th>
                                                <th>Class</th>
                                                <th>Academic Year</th>
                                                <th>Semester</th>
                                                <th>Total Amount</th>
                                                <th>Amount Paid</th>
                                                <th>Balance</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($bills as $bill)
                                                <tr>
                                                    <td>{{ $bill->student->student_id }}</td>
                                                    <td>{{ $bill->student->last_name }}, {{ $bill->student->first_name }}</td>
                                                    <td>{{ $bill->student->collegeClass->name }}</td>
                                                    <td>{{ $bill->academicYear->name }}</td>
                                                    <td>{{ $bill->semester->name }}</td>
                                                    <td>{{ number_format($bill->total_amount, 2) }}</td>
                                                    <td>{{ number_format($bill->amount_paid, 2) }}</td>
                                                    <td>{{ number_format($bill->balance, 2) }}</td>
                                                    <td>
                                                        @if($bill->status == 'pending')
                                                            <span class="badge bg-warning">Pending</span>
                                                        @elseif($bill->status == 'partial')
                                                            <span class="badge bg-info">Partial ({{ $bill->payment_percentage }}%)</span>
                                                        @elseif($bill->status == 'paid')
                                                            <span class="badge bg-success">Paid</span>
                                                        @else
                                                            <span class="badge bg-danger">Overdue</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm">
                                                            <a href="{{ route('finance.record-payment', $bill->id) }}" class="btn btn-primary">
                                                                <i class="fas fa-cash-register"></i> Record Payment
                                                            </a>
                                                            <a href="{{ route('finance.view-bill', $bill->id) }}" class="btn btn-info">
                                                                <i class="fas fa-eye"></i> View
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="10" class="text-center">No bills found</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                <div class="d-flex justify-content-center mt-3">
                                    {{ $bills->links() }}
                                </div>
                            </div>

                            <!-- Manage Fee Types Tab -->
                            <div class="tab-pane fade" id="manage" role="tabpanel">
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-header">
                                                <h4 class="card-title">Fee Types</h4>
                                            </div>
                                            <div class="card-body">
                                                <button class="btn btn-primary mb-3" wire:click="$set('feeTypeModalOpen', true)">
                                                    <i class="fas fa-plus"></i> Create New Fee Type
                                                </button>
                                                
                                                <div class="table-responsive">
                                                    <table class="table table-bordered">
                                                        <thead>
                                                            <tr>
                                                                <th>Name</th>
                                                                <th>Code</th>
                                                                <th>Description</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @forelse($feeTypes as $feeType)
                                                                <tr>
                                                                    <td>{{ $feeType->name }}</td>
                                                                    <td>{{ $feeType->code }}</td>
                                                                    <td>{{ $feeType->description ?? 'N/A' }}</td>
                                                                </tr>
                                                            @empty
                                                                <tr>
                                                                    <td colspan="3" class="text-center">No fee types found</td>
                                                                </tr>
                                                            @endforelse
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-header">
                                                <h4 class="card-title">Fee Structures</h4>
                                            </div>
                                            <div class="card-body">
                                                <button class="btn btn-primary mb-3" wire:click="$set('feeStructureModalOpen', true)">
                                                    <i class="fas fa-plus"></i> Create New Fee Structure
                                                </button>
                                                
                                                <!-- Fee structure list would be added here, but it could get quite complex -->
                                                <div class="alert alert-info">
                                                    Fee structures define the amount to bill per fee type for specific classes, academic years, and semesters.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Bulk Generate Bills Tab -->
                            <div class="tab-pane fade" id="generate" role="tabpanel">
                                <div class="row mt-3">
                                    <div class="col-md-8 mx-auto">
                                        <div class="card">
                                            <div class="card-header">
                                                <h4 class="card-title">Generate Bills in Bulk</h4>
                                            </div>
                                            <div class="card-body">
                                                <form wire:submit="generateBills">
                                                    <div class="mb-3">
                                                        <label class="form-label">Class</label>
                                                        <select wire:model="bulkBillingClass" class="form-select">
                                                            <option value="">Select a Class</option>
                                                            @foreach($collegeClasses as $class)
                                                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                                                            @endforeach
                                                        </select>
                                                        @error('bulkBillingClass') <div class="text-danger mt-1">{{ $message }}</div> @enderror
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label">Academic Year</label>
                                                        <select wire:model="bulkBillingAcademicYear" class="form-select">
                                                            <option value="">Select Academic Year</option>
                                                            @foreach($academicYears as $year)
                                                                <option value="{{ $year->id }}">{{ $year->name }}</option>
                                                            @endforeach
                                                        </select>
                                                        @error('bulkBillingAcademicYear') <div class="text-danger mt-1">{{ $message }}</div> @enderror
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label">Semester</label>
                                                        <select wire:model="bulkBillingSemester" class="form-select">
                                                            <option value="">Select Semester</option>
                                                            @foreach($semesters as $semester)
                                                                <option value="{{ $semester->id }}">{{ $semester->name }}</option>
                                                            @endforeach
                                                        </select>
                                                        @error('bulkBillingSemester') <div class="text-danger mt-1">{{ $message }}</div> @enderror
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label">Bill Date</label>
                                                        <input type="date" wire:model="bulkBillDate" class="form-control">
                                                        @error('bulkBillDate') <div class="text-danger mt-1">{{ $message }}</div> @enderror
                                                    </div>
                                                    
                                                    <div class="d-grid">
                                                        <button type="submit" class="btn btn-primary">
                                                            <i class="fas fa-file-invoice-dollar me-2"></i> Generate Bills
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Fee Type Modal -->
        <div class="modal fade" id="feeTypeModal" tabindex="-1" wire:ignore.self>
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Create New Fee Type</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit="saveFeeType">
                            <div class="mb-3">
                                <label class="form-label">Name</label>
                                <input type="text" wire:model="newFeeType.name" class="form-control" placeholder="E.g. Tuition Fees">
                                @error('newFeeType.name') <div class="text-danger mt-1">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Code</label>
                                <input type="text" wire:model="newFeeType.code" class="form-control" placeholder="E.g. TF">
                                @error('newFeeType.code') <div class="text-danger mt-1">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea wire:model="newFeeType.description" class="form-control" rows="3"></textarea>
                                @error('newFeeType.description') <div class="text-danger mt-1">{{ $message }}</div> @enderror
                            </div>
                            <div class="d-flex justify-content-end">
                                <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Save Fee Type</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Fee Structure Modal -->
        <div class="modal fade" id="feeStructureModal" tabindex="-1" wire:ignore.self>
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Create New Fee Structure</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit="saveFeeStructure">
                            <div class="mb-3">
                                <label class="form-label">Fee Type</label>
                                <select wire:model="newFeeStructure.fee_type_id" class="form-select">
                                    <option value="">Select Fee Type</option>
                                    @foreach($feeTypes as $feeType)
                                        <option value="{{ $feeType->id }}">{{ $feeType->name }}</option>
                                    @endforeach
                                </select>
                                @error('newFeeStructure.fee_type_id') <div class="text-danger mt-1">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Class</label>
                                <select wire:model="newFeeStructure.college_class_id" class="form-select">
                                    <option value="">Select a Class</option>
                                    @foreach($collegeClasses as $class)
                                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                                    @endforeach
                                </select>
                                @error('newFeeStructure.college_class_id') <div class="text-danger mt-1">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Academic Year</label>
                                <select wire:model="newFeeStructure.academic_year_id" class="form-select">
                                    <option value="">Select Academic Year</option>
                                    @foreach($academicYears as $year)
                                        <option value="{{ $year->id }}">{{ $year->name }}</option>
                                    @endforeach
                                </select>
                                @error('newFeeStructure.academic_year_id') <div class="text-danger mt-1">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Semester</label>
                                <select wire:model="newFeeStructure.semester_id" class="form-select">
                                    <option value="">Select Semester</option>
                                    @foreach($semesters as $semester)
                                        <option value="{{ $semester->id }}">{{ $semester->name }}</option>
                                    @endforeach
                                </select>
                                @error('newFeeStructure.semester_id') <div class="text-danger mt-1">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Amount</label>
                                <input type="number" wire:model="newFeeStructure.amount" class="form-control" step="0.01" min="0">
                                @error('newFeeStructure.amount') <div class="text-danger mt-1">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-check mb-3">
                                <input type="checkbox" wire:model="newFeeStructure.is_mandatory" class="form-check-input" id="isMandatory">
                                <label class="form-check-label" for="isMandatory">Is Mandatory</label>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Save Fee Structure</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </x-components.dashboard.default>

    @push('scripts')
    <script>
        document.addEventListener('livewire:initialized', function () {
            window.addEventListener('closeModals', event => {
                $('#feeTypeModal').modal('hide');
                $('#feeStructureModal').modal('hide');
            });
        });

        // Initialize Bootstrap modals
        window.addEventListener('livewire:initialized', function () {
            Livewire.hook('element.updated', () => {
                if (@this.feeTypeModalOpen) {
                    $('#feeTypeModal').modal('show');
                }
                if (@this.feeStructureModalOpen) {
                    $('#feeStructureModal').modal('show');
                }
            });
        });
    </script>
    @endpush
</div>