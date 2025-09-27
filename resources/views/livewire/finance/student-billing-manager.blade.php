<div>
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <h1 class="card-title mb-0">
                    <i class="fas fa-money-bill-wave me-2"></i>Student Billing Management
                </h1>
            </div>
            <div class="card-toolbar">
                    <button wire:click="openBatchBillsModal" class="btn btn-success me-2">
                        <i class="fas fa-users me-2"></i>Batch Bills
                    </button>
                    <button wire:click="openNewBillModal" class="btn btn-primary">
                        <i class="fas fa-plus-circle me-2"></i>New Bill
                    </button>
                </div>
        </div>

        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-3">
                    <label for="academicYear">Academic Year</label>
                    <select id="academicYear" wire:model.live="academicYearId" class="form-select">
                        <option value="">All Academic Years</option>
                        @foreach ($academicYears as $academicYear)
                            <option value="{{ $academicYear->id }}">{{ $academicYear->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="semester">Semester</label>
                    <select id="semester" wire:model.live="semesterId" class="form-select">
                        <option value="">All Semesters</option>
                        @foreach ($semesters as $semester)
                            <option value="{{ $semester->id }}">{{ $semester->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="class">Class</label>
                    <select id="class" wire:model.live="collegeClassId" class="form-select">
                        <option value="">All Classes</option>
                        @foreach ($classes as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="search">Search</label>
                    <input type="text" id="search" wire:model.live.debounce.300ms="search" class="form-control"
                        placeholder="Search student name, ID">
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
                        @if ($bills->isEmpty())
                            <tr>
                                <td colspan="9" class="text-center">No bills found.</td>
                            </tr>
                        @else
                            @foreach ($bills as $bill)
                                <tr>
                                    <td>{{ $bill->student->student_id ?? 'N/A' }}</td>
                                    <td>{{ $bill->student ? trim(($bill->student->first_name ?? '') . ' ' . ($bill->student->last_name ?? '')) : 'N/A' }}
                                    </td>
                                    <td>{{ $bill->academicYear->name }}</td>
                                    <td>{{ $bill->semester->name }}</td>
                                    <td>{{ number_format($bill->total_amount, 2) }}</td>
                                    <td>{{ number_format($bill->amount_paid, 2) }}</td>
                                    <td>{{ number_format($bill->balance, 2) }}</td>
                                    <td>
                                        @if ($bill->status === 'paid')
                                            <span class="badge bg-success">Paid</span>
                                        @elseif($bill->status === 'partially_paid')
                                            <span class="badge bg-warning text-dark">Partially Paid
                                                ({{ number_format($bill->payment_percentage, 1) }}%)</span>
                                        @else
                                            <span class="badge bg-danger">Pending</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <button wire:click="viewBill({{ $bill->id }})"
                                                class="btn btn-sm btn-info">
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
    <div class="modal @if ($showNewBillModal) show @endif" tabindex="-1" role="dialog"
        style="display: @if ($showNewBillModal) block @else none @endif; background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create New Student Bill</h5>
                    <button type="button" class="btn-close" wire:click="closeNewBillModal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="studentSelect" class="form-label">Select Student</label>
                        <select id="studentSelect" wire:model="newBillStudentId" class="form-select">
                            <option value="">-- Select Student --</option>
                            @foreach ($students as $student)
                                <option value="{{ $student->id }}">
                                    {{ trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? '')) }}
                                    ({{ $student->student_id }})</option>
                            @endforeach
                        </select>
                        @error('newBillStudentId')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="academicYearSelect" class="form-label">Academic Year</label>
                        <select id="academicYearSelect" wire:model="newBillAcademicYearId" class="form-select">
                            <option value="">-- Select Academic Year --</option>
                            @foreach ($academicYears as $year)
                                <option value="{{ $year->id }}">{{ $year->name }}</option>
                            @endforeach
                        </select>
                        @error('newBillAcademicYearId')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="semesterSelect" class="form-label">Semester</label>
                        <select id="semesterSelect" wire:model="newBillSemesterId" class="form-select">
                            <option value="">-- Select Semester --</option>
                            @foreach ($semesters as $semester)
                                <option value="{{ $semester->id }}">{{ $semester->name }}</option>
                            @endforeach
                        </select>
                        @error('newBillSemesterId')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeNewBillModal">Cancel</button>
                    <button type="button" class="btn btn-primary" wire:click="createNewBill">Generate Bill</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Batch Bills Modal -->
    <div class="modal @if ($showBatchBillsModal) show @endif" tabindex="-1" role="dialog"
        style="display: @if ($showBatchBillsModal) block @else none @endif; background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Generate Batch Bills</h5>
                    <button type="button" class="btn-close" wire:click="closeBatchBillsModal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>This will generate bills for all active students in the
                        selected class.
                    </div>

                    <div class="mb-3">
                        <label for="batchAcademicYear" class="form-label">Academic Year</label>
                        <select id="batchAcademicYear" wire:model="batchAcademicYearId" class="form-select">
                            <option value="">-- Select Academic Year --</option>
                            @foreach ($academicYears as $year)
                                <option value="{{ $year->id }}">{{ $year->name }}</option>
                            @endforeach
                        </select>
                        @error('batchAcademicYearId')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="batchSemester" class="form-label">Semester</label>
                        <select id="batchSemester" wire:model="batchSemesterId" class="form-select">
                            <option value="">-- Select Semester --</option>
                            @foreach ($semesters as $semester)
                                <option value="{{ $semester->id }}">{{ $semester->name }}</option>
                            @endforeach
                        </select>
                        @error('batchSemesterId')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="batchClass" class="form-label">Class</label>
                        <select id="batchClass" wire:model="batchClassId" class="form-select">
                            <option value="">-- Select Class --</option>
                            @foreach ($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                        @error('batchClassId')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                        wire:click="closeBatchBillsModal">Cancel</button>
                    <button type="button" class="btn btn-success" wire:click="generateBatchBills">Generate
                        Bills</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.addEventListener('notify', event => {
                const data = event.detail[0] || event.detail;
                // You can replace this with your preferred notification system
                alert(data.message);
            });
        });
    </script>
</div>
