<div>
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title text-white">{{ $stats['total'] }}</h4>
                            <p class="card-text">Total Registrations</p>
                        </div>
                        <div>
                            <i class="fas fa-list fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title text-white">{{ $stats['pending'] }}</h4>
                            <p class="card-text">Pending Approval</p>
                        </div>
                        <div>
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title text-white">{{ $stats['approved'] }}</h4>
                            <p class="card-text">Approved</p>
                        </div>
                        <div>
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title text-white">{{ $stats['rejected'] }}</h4>
                            <p class="card-text">Rejected</p>
                        </div>
                        <div>
                            <i class="fas fa-times-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-filter"></i> Filters & Search
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="academicYear">Academic Year</label>
                        <select wire:model.live="selectedAcademicYear" class="form-control">
                            <option value="">-- All Academic Years --</option>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}">{{ $year->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="semester">Semester</label>
                        <select wire:model.live="selectedSemester" class="form-control">
                            <option value="">-- All Semesters --</option>
                            @foreach($semesters as $semester)
                                <option value="{{ $semester->id }}">{{ $semester->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select wire:model.live="filterStatus" class="form-control">
                            <option value="pending">Pending Approval</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                            <option value="all">All</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="search">Search</label>
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control" 
                               placeholder="Student name, ID, or course...">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Course Registrations Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="fas fa-book-open"></i> Course Registration Approvals
            </h5>
            @if($stats['pending'] > 0)
                <button type="button" class="btn btn-success btn-sm" onclick="bulkApproveSelected()">
                    <i class="fas fa-check"></i> Bulk Approve Selected
                </button>
            @endif
        </div>
        <div class="card-body">
            @if($registrations->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="thead-light">
                            <tr>
                                @if($filterStatus === 'pending')
                                    <th width="3%">
                                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                                    </th>
                                @endif
                                <th width="12%">Student ID</th>
                                <th width="20%">Student Name</th>
                                <th width="15%">Course Code</th>
                                <th width="25%">Course Name</th>
                                <th width="10%">Credits</th>
                                <th width="10%">Payment %</th>
                                <th width="12%">Registered On</th>
                                <th width="10%">Status</th>
                                @if($filterStatus === 'pending')
                                    <th width="15%">Actions</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($registrations as $registration)
                                <tr>
                                    @if($filterStatus === 'pending')
                                        <td>
                                            <input type="checkbox" class="registration-checkbox" 
                                                   value="{{ $registration->id }}">
                                        </td>
                                    @endif
                                    <td>{{ $registration->student->student_id ?? 'N/A' }}</td>
                                    <td>{{ $registration->student->full_name ?? 'N/A' }}</td>
                                    <td>{{ $registration->subject->course_code ?? 'N/A' }}</td>
                                    <td>{{ $registration->subject->name ?? 'N/A' }}</td>
                                    <td>{{ $registration->subject->credit_hours ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge badge-info">
                                            {{ number_format($registration->payment_percentage_at_registration, 1) }}%
                                        </span>
                                    </td>
                                    <td>{{ $registration->registered_at->format('M d, Y') }}</td>
                                    <td>
                                        @if($registration->is_approved)
                                            <span class="badge badge-success">
                                                <i class="fas fa-check"></i> Approved
                                            </span>
                                            @if($registration->approved_at)
                                                <small class="d-block text-muted">
                                                    {{ $registration->approved_at->format('M d, Y') }}
                                                </small>
                                            @endif
                                        @elseif($registration->rejected_at)
                                            <span class="badge badge-danger">
                                                <i class="fas fa-times"></i> Rejected
                                            </span>
                                            <small class="d-block text-muted">
                                                {{ $registration->rejected_at->format('M d, Y') }}
                                            </small>
                                            @if($registration->rejection_reason)
                                                <small class="d-block text-muted" title="{{ $registration->rejection_reason }}">
                                                    {{ Str::limit($registration->rejection_reason, 30) }}
                                                </small>
                                            @endif
                                        @else
                                            <span class="badge badge-warning">
                                                <i class="fas fa-clock"></i> Pending
                                            </span>
                                        @endif
                                    </td>
                                    @if($filterStatus === 'pending' && !$registration->is_approved && !$registration->rejected_at)
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button type="button" 
                                                        wire:click="approveRegistration({{ $registration->id }})"
                                                        class="btn btn-success btn-sm"
                                                        wire:confirm="Are you sure you want to approve this registration?">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button type="button" 
                                                        class="btn btn-danger btn-sm"
                                                        onclick="showRejectModal({{ $registration->id }}, '{{ $registration->student->full_name }}', '{{ $registration->subject->name }}')">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </td>
                                    @elseif($filterStatus !== 'pending')
                                        <!-- Show who approved/rejected if not in pending view -->
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>
                        Showing {{ $registrations->firstItem() }} to {{ $registrations->lastItem() }} 
                        of {{ $registrations->total() }} results
                    </div>
                    <div>
                        {{ $registrations->links() }}
                    </div>
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No course registrations found</h5>
                    <p class="text-muted">Try adjusting your filters or search criteria.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Rejection Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-labelledby="rejectModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="rejectModalLabel">Reject Course Registration</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to reject the course registration for:</p>
                    <div class="alert alert-info">
                        <strong>Student:</strong> <span id="rejectStudentName"></span><br>
                        <strong>Course:</strong> <span id="rejectCourseName"></span>
                    </div>
                    <div class="form-group">
                        <label for="rejectionReason">Reason for Rejection (Optional)</label>
                        <textarea class="form-control" id="rejectionReason" rows="3" 
                                  placeholder="Enter reason for rejection..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" onclick="confirmReject()">
                        <i class="fas fa-times"></i> Reject Registration
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let currentRejectId = null;

    function showRejectModal(id, studentName, courseName) {
        currentRejectId = id;
        document.getElementById('rejectStudentName').textContent = studentName;
        document.getElementById('rejectCourseName').textContent = courseName;
        document.getElementById('rejectionReason').value = '';
        $('#rejectModal').modal('show');
    }

    function confirmReject() {
        if (currentRejectId) {
            const reason = document.getElementById('rejectionReason').value;
            @this.rejectRegistration(currentRejectId, reason);
            $('#rejectModal').modal('hide');
        }
    }

    function toggleSelectAll() {
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.registration-checkbox');
        
        checkboxes.forEach(checkbox => {
            checkbox.checked = selectAll.checked;
        });
    }

    function bulkApproveSelected() {
        const checkboxes = document.querySelectorAll('.registration-checkbox:checked');
        const selectedIds = Array.from(checkboxes).map(cb => cb.value);
        
        if (selectedIds.length === 0) {
            alert('Please select at least one registration to approve.');
            return;
        }
        
        if (confirm(`Are you sure you want to approve ${selectedIds.length} registration(s)?`)) {
            @this.bulkApprove(selectedIds);
        }
    }

    // Listen for registration updates and refresh stats
    Livewire.on('registration-updated', () => {
        // Reset checkboxes
        document.getElementById('selectAll').checked = false;
        document.querySelectorAll('.registration-checkbox').forEach(cb => cb.checked = false);
    });
</script>
@endpush