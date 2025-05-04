<div>
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="card-title">
                    <i class="fas fa-money-bill-wave me-2"></i>Student Billing Management
                </h1>
                <div>
                    <button wire:click="openNewBillModal" class="btn btn-primary">
                        <i class="fas fa-plus-circle me-2"></i>New Bill
                    </button>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-3">
                    <label for="academicYear">Academic Year</label>
                    <select id="academicYear" wire:model.live="academicYearId" class="form-select">
                        <option value="">All Academic Years</option>
                        @foreach($academicYears as $academicYear)
                            <option value="{{ $academicYear->id }}">{{ $academicYear->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="semester">Semester</label>
                    <select id="semester" wire:model.live="semesterId" class="form-select">
                        <option value="">All Semesters</option>
                        @foreach($semesters as $semester)
                            <option value="{{ $semester->id }}">{{ $semester->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="class">Class</label>
                    <select id="class" wire:model.live="collegeClassId" class="form-select">
                        <option value="">All Classes</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="search">Search</label>
                    <input type="text" id="search" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Search student name, ID">
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-striped">
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
                        @if($bills->isEmpty())
                            <tr>
                                <td colspan="9" class="text-center">No bills found.</td>
                            </tr>
                        @else
                            @foreach($bills as $bill)
                                <tr>
                                    <td>{{ $bill->student->student_id ?? 'N/A' }}</td>
                                    <td>{{ $bill->student->full_name ?? $bill->student->first_name . ' ' . $bill->student->last_name }}</td>
                                    <td>{{ $bill->academicYear->name }}</td>
                                    <td>{{ $bill->semester->name }}</td>
                                    <td>{{ number_format($bill->total_amount, 2) }}</td>
                                    <td>{{ number_format($bill->amount_paid, 2) }}</td>
                                    <td>{{ number_format($bill->balance, 2) }}</td>
                                    <td>
                                        @if($bill->status === 'paid')
                                            <span class="badge bg-success">Paid</span>
                                        @elseif($bill->status === 'partially_paid')
                                            <span class="badge bg-warning text-dark">Partially Paid ({{ number_format($bill->payment_percentage, 1) }}%)</span>
                                        @else
                                            <span class="badge bg-danger">Pending</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <button wire:click="viewBill({{ $bill->id }})" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $bills->links() }}
            </div>
        </div>
    </div>

    <!-- New Bill Modal -->
    <div class="modal @if($showNewBillModal) show @endif" tabindex="-1" role="dialog" style="display: @if($showNewBillModal) block @else none @endif; background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create New Bill</h5>
                    <button type="button" class="btn-close" wire:click="closeNewBillModal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="createNewBill">
                        <div class="mb-3">
                            <label for="newBillStudentId" class="form-label">Student</label>
                            <select id="newBillStudentId" wire:model="newBillStudentId" class="form-select @error('newBillStudentId') is-invalid @enderror">
                                <option value="">Select Student</option>
                                @foreach($students as $student)
                                    <option value="{{ $student->id }}">{{ $student->full_name ?? $student->first_name . ' ' . $student->last_name }} ({{ $student->student_id }})</option>
                                @endforeach
                            </select>
                            @error('newBillStudentId') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="newBillAcademicYearId" class="form-label">Academic Year</label>
                            <select id="newBillAcademicYearId" wire:model="newBillAcademicYearId" class="form-select @error('newBillAcademicYearId') is-invalid @enderror">
                                <option value="">Select Academic Year</option>
                                @foreach($academicYears as $academicYear)
                                    <option value="{{ $academicYear->id }}">{{ $academicYear->name }}</option>
                                @endforeach
                            </select>
                            @error('newBillAcademicYearId') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="newBillSemesterId" class="form-label">Semester</label>
                            <select id="newBillSemesterId" wire:model="newBillSemesterId" class="form-select @error('newBillSemesterId') is-invalid @enderror">
                                <option value="">Select Semester</option>
                                @foreach($semesters as $semester)
                                    <option value="{{ $semester->id }}">{{ $semester->name }}</option>
                                @endforeach
                            </select>
                            @error('newBillSemesterId') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Generate Bill</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Batch Bills Modal -->
    <div class="modal @if($showBatchBillsModal) show @endif" tabindex="-1" role="dialog" style="display: @if($showBatchBillsModal) block @else none @endif; background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Generate Batch Bills</h5>
                    <button type="button" class="btn-close" wire:click="closeBatchBillsModal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="generateBatchBills">
                        <div class="mb-3">
                            <label for="batchAcademicYearId" class="form-label">Academic Year</label>
                            <select id="batchAcademicYearId" wire:model="batchAcademicYearId" class="form-select @error('batchAcademicYearId') is-invalid @enderror">
                                <option value="">Select Academic Year</option>
                                @foreach($academicYears as $academicYear)
                                    <option value="{{ $academicYear->id }}">{{ $academicYear->name }}</option>
                                @endforeach
                            </select>
                            @error('batchAcademicYearId') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="batchSemesterId" class="form-label">Semester</label>
                            <select id="batchSemesterId" wire:model="batchSemesterId" class="form-select @error('batchSemesterId') is-invalid @enderror">
                                <option value="">Select Semester</option>
                                @foreach($semesters as $semester)
                                    <option value="{{ $semester->id }}">{{ $semester->name }}</option>
                                @endforeach
                            </select>
                            @error('batchSemesterId') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="batchClassId" class="form-label">Class</label>
                            <select id="batchClassId" wire:model="batchClassId" class="form-select @error('batchClassId') is-invalid @enderror">
                                <option value="">Select Class</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                                @endforeach
                            </select>
                            @error('batchClassId') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>This will generate fee bills for all active students in the selected class.
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Generate Bills</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Batch Bills Button -->
    <div class="card mt-4">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="card-title">
                    <i class="fas fa-file-invoice-dollar me-2"></i>Student Fee Billing
                </h1>
                <div>
                    <button class="btn btn-primary" wire:click="openBatchBillsModal">
                        <i class="fas fa-layer-group me-2"></i>Generate Batch Bills
                    </button>
                </div>
            </div>
        </div>
    </div>

    @script
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('notify', (data) => {
                // You can replace this with your preferred notification system
                alert(data.message);
            });
        });
    @endscript
</div>