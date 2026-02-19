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
                <div class="col-md-2">
                    <label for="semester">Semester</label>
                    <select id="semester" wire:model.live="semesterId" class="form-select">
                        <option value="">All Semesters</option>
                        @foreach ($semesters as $semester)
                            <option value="{{ $semester->id }}">{{ $semester->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="cohort">Cohort</label>
                    <select id="cohort" wire:model.live="cohortId" class="form-select">
                        <option value="">All Cohorts</option>
                        @foreach ($cohorts as $cohort)
                            <option value="{{ $cohort->id }}">{{ $cohort->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="class">Program</label>
                    <select id="class" wire:model.live="collegeClassId" class="form-select">
                        <option value="">All Programs</option>
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
                                    <td>GH₵ {{ number_format($bill->total_amount, 2) }}</td>
                                    <td>GH₵ {{ number_format($bill->amount_paid, 2) }}</td>
                                    <td>GH₵ {{ number_format($bill->balance, 2) }}</td>
                                    <td>
                                        @php
                                            $status = $bill->getPaymentStatus();
                                        @endphp
                                        @if ($status === 'paid')
                                            <span class="badge bg-success">Paid (100%)</span>
                                        @elseif($status === 'partial')
                                            <span class="badge bg-warning text-dark">Partially Paid
                                                ({{ number_format($bill->payment_percentage, 1) }}%)</span>
                                        @else
                                            <span class="badge bg-danger">Unpaid ({{ number_format($bill->payment_percentage, 1) }}%)</span>
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
                        <select id="semesterSelect" wire:model.live="newBillSemesterId" class="form-select">
                            <option value="">-- Select Semester --</option>
                            @foreach ($semesters as $semester)
                                <option value="{{ $semester->id }}">{{ $semester->name }}</option>
                            @endforeach
                        </select>
                        @error('newBillSemesterId')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    @if (!empty($availableFees))
                        <div class="mb-3">
                            <label class="form-label">Select Fees to Include</label>
                            <div class="border rounded p-3" style="max-height: 250px; overflow-y: auto;">
                                @foreach ($availableFees as $fee)
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" 
                                            value="{{ $fee['id'] }}" 
                                            wire:model="selectedFeeIds"
                                            id="fee{{ $fee['id'] }}"
                                            @if ($fee['is_mandatory']) disabled checked @endif>
                                        <label class="form-check-label d-flex justify-content-between w-100" for="fee{{ $fee['id'] }}">
                                            <span>
                                                {{ $fee['fee_type']['name'] ?? 'Unknown Fee' }}
                                                @if ($fee['is_mandatory'])
                                                    <span class="badge bg-primary ms-2">Mandatory</span>
                                                @endif
                                                @if (!empty($fee['applicable_gender']) && $fee['applicable_gender'] !== 'all')
                                                    <span class="badge bg-info ms-1">{{ $fee['applicable_gender'] === 'female' ? 'Female only' : 'Male only' }}</span>
                                                @endif
                                            </span>
                                            <strong>GH₵ {{ number_format($fee['amount'], 2) }}</strong>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>Mandatory fees are automatically selected. Only fees applicable to this student’s gender are shown.
                            </small>
                        </div>

                        <div class="alert alert-info">
                            <strong>Total Selected:</strong> 
                            GH₵ {{ number_format(collect($availableFees)->whereIn('id', $selectedFeeIds)->sum('amount'), 2) }}
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeNewBillModal">Cancel</button>
                    <button type="button" class="btn btn-primary" wire:click="createNewBill" 
                        @if (empty($selectedFeeIds)) disabled @endif>Generate Bill</button>
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
                        <i class="fas fa-info-circle me-2"></i>This will generate bills for active students in the
                        selected program. Optionally select a batch (cohort) to bill only that group.
                    </div>

                    <div class="mb-3">
                        <label for="batchAcademicYear" class="form-label">Academic Year</label>
                        <select id="batchAcademicYear" wire:model.live="batchAcademicYearId" class="form-select">
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
                        <select id="batchSemester" wire:model.live="batchSemesterId" class="form-select">
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
                        <label for="batchClass" class="form-label">Program</label>
                        <select id="batchClass" wire:model.live="batchClassId" class="form-select">
                            <option value="">-- Select Program --</option>
                            @foreach ($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                        @error('batchClassId')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="batchCohort" class="form-label">Batch (Cohort)</label>
                        <select id="batchCohort" wire:model.live="batchCohortId" class="form-select">
                            <option value="">All batches in program</option>
                            @foreach ($cohorts as $cohort)
                                <option value="{{ $cohort->id }}">{{ $cohort->name }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Optional. Select a batch to bill only students in that cohort.</small>
                    </div>

                    @if (!empty($batchAvailableFees))
                        <div class="mb-3">
                            <label class="form-label">Select Fees to Include</label>
                            <div class="border rounded p-3" style="max-height: 250px; overflow-y: auto;">
                                @foreach ($batchAvailableFees as $fee)
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" 
                                            value="{{ $fee['id'] }}" 
                                            wire:model="batchSelectedFeeIds"
                                            id="batchFee{{ $fee['id'] }}"
                                            @if ($fee['is_mandatory']) disabled checked @endif>
                                        <label class="form-check-label d-flex justify-content-between w-100" for="batchFee{{ $fee['id'] }}">
                                            <span>
                                                {{ $fee['fee_type']['name'] ?? 'Unknown Fee' }}
                                                @if ($fee['is_mandatory'])
                                                    <span class="badge bg-primary ms-2">Mandatory</span>
                                                @endif
                                                @if (!empty($fee['applicable_gender']) && $fee['applicable_gender'] !== 'all')
                                                    <span class="badge bg-info ms-1">{{ $fee['applicable_gender'] === 'female' ? 'Female only' : 'Male only' }}</span>
                                                @endif
                                            </span>
                                            <strong>GH₵ {{ number_format($fee['amount'], 2) }}</strong>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>Mandatory fees are auto-selected. Female/Male-only fees are applied only to students of that gender when bills are generated.
                            </small>
                        </div>

                        <div class="alert alert-info">
                            <strong>Total Selected:</strong> 
                            GH₵ {{ number_format(collect($batchAvailableFees)->whereIn('id', $batchSelectedFeeIds)->sum('amount'), 2) }}
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                        wire:click="closeBatchBillsModal">Cancel</button>
                    <button type="button" class="btn btn-success" wire:click="generateBatchBills"
                        @if (empty($batchSelectedFeeIds)) disabled @endif>Generate Bills</button>
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
