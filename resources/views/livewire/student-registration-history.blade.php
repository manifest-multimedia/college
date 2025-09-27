<div>
    @if(!$student)
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle me-2"></i>No student record found for your account. Please contact the administration.
        </div>
    @else
        <!-- Filters -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="academicYear">Filter by Academic Year</label>
                    <select wire:model.live="selectedAcademicYear" class="form-control">
                        <option value="">All Academic Years</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}">{{ $year->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="semester">Filter by Semester</label>
                    <select wire:model.live="selectedSemester" class="form-control">
                        <option value="">All Semesters</option>
                        @foreach($semesters as $semester)
                            <option value="{{ $semester->id }}">{{ $semester->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- Registration History Table -->
        @if($registrations->count() > 0)
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>Academic Year</th>
                            <th>Semester</th>
                            <th>Course Code</th>
                            <th>Subject Name</th>
                            <th>Credit Hours</th>
                            <th>Registration Date</th>
                            <th>Status</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($registrations as $registration)
                            <tr>
                                <td>{{ $registration->academicYear->name ?? 'N/A' }}</td>
                                <td>{{ $registration->semester->name ?? 'N/A' }}</td>
                                <td>{{ $registration->subject->course_code ?? 'N/A' }}</td>
                                <td>{{ $registration->subject->name ?? 'N/A' }}</td>
                                <td>{{ $registration->subject->credit_hours ?? 'N/A' }}</td>
                                <td>{{ $registration->registered_at->format('M d, Y g:i A') }}</td>
                                <td>
                                    @if($registration->is_approved)
                                        <span class="badge bg-success">
                                            <i class="fas fa-check me-1"></i>Approved
                                        </span>
                                    @elseif($registration->rejected_at)
                                        <span class="badge bg-danger">
                                            <i class="fas fa-times me-1"></i>Rejected
                                        </span>
                                    @else
                                        <span class="badge bg-warning">
                                            <i class="fas fa-clock me-1"></i>Pending
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($registration->is_approved && $registration->approved_at)
                                        <small class="text-success">
                                            <strong>Approved:</strong><br>
                                            {{ $registration->approved_at->format('M d, Y g:i A') }}
                                            @if($registration->approvedBy)
                                                <br>by {{ $registration->approvedBy->name }}
                                            @endif
                                        </small>
                                    @elseif($registration->rejected_at)
                                        <small class="text-danger">
                                            <strong>Rejected:</strong><br>
                                            {{ $registration->rejected_at->format('M d, Y g:i A') }}
                                            @if($registration->rejectedBy)
                                                <br>by {{ $registration->rejectedBy->name }}
                                            @endif
                                            @if($registration->rejection_reason)
                                                <br><strong>Reason:</strong> {{ $registration->rejection_reason }}
                                            @endif
                                        </small>
                                    @else
                                        <small class="text-muted">
                                            Awaiting approval from Finance Department
                                        </small>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    Showing {{ $registrations->firstItem() }} to {{ $registrations->lastItem() }} 
                    of {{ $registrations->total() }} registrations
                </div>
                <div>
                    {{ $registrations->links() }}
                </div>
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No course registrations found</h5>
                <p class="text-muted">You haven't registered for any courses yet.</p>
                <a href="{{ route('courseregistration') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Register for Courses
                </a>
            </div>
        @endif
    @endif
</div>