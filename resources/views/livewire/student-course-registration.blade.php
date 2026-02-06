<div><div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <div>
                        <h1 class="card-title mb-2">
                            <i class="fas fa-book-open me-2"></i>Course Registration
                        </h1>
                        @if($student)
                            <p class="card-subtitle text-muted mb-1">
                                Student: {{ $student->first_name }} {{ $student->last_name }} ({{ $student->student_id }})
                            </p>
                            @if($currentAcademicYear && $currentSemester)
                                <p class="card-subtitle text-muted mb-0">
                                    Academic Year: {{ $currentAcademicYear->name }} | Semester: {{ $currentSemester->name }}
                                </p>
                            @endif
                        @endif
                    </div>
                </div>

                <div class="card-body">
                    <!-- Flash Messages -->
                    @if(session('success'))
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger">
                            <i class="fas fa-times-circle me-2"></i>{{ session('error') }}
                        </div>
                    @endif

                    @if(!$student)
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>No student record found for your account. Please contact the administration.
                        </div>
                    @elseif(!$currentAcademicYear || !$currentSemester)
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>No active academic year or semester found. Please contact the administration.
                        </div>
                    @else
                        <!-- Payment Status Card -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card border-left-{{ $registrationMessageType === 'success' ? 'success' : 'warning' }}">
                                    <div class="card-body">
                                        <h5 class="card-title">
                                            <i class="fas fa-credit-card me-2"></i>Payment Status
                                        </h5>
                                        <div class="alert alert-{{ $registrationMessageType }} mb-0">
                                            <i class="fas fa-{{ $registrationMessageType === 'success' ? 'check-circle' : 'exclamation-triangle' }} me-2"></i>
                                            {{ $registrationMessage }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">
                                            <i class="fas fa-chart-pie me-2"></i>Payment Progress
                                        </h5>
                                        <div class="progress mb-2">
                                            <div class="progress-bar bg-{{ $paymentPercentage >= 60 ? 'success' : 'warning' }}" 
                                                 style="width: {{ $paymentPercentage }}%">
                                                {{ number_format($paymentPercentage, 1) }}%
                                            </div>
                                        </div>
                                        <small class="text-muted">Required: 60% minimum for course registration</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if($registrationAllowed && $availableSubjects->isNotEmpty())
                            <!-- Course Selection Form -->
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-list-check me-2"></i>Available Subjects for Registration
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        @foreach($availableSubjects as $subject)
                                            <div class="col-md-6 col-lg-4 mb-3">
                                                <div class="card h-100 subject-card {{ in_array($subject->id, $selectedSubjects) ? 'border-primary' : '' }}">
                                                    <div class="card-body">
                                                        <div class="form-check">
                                                            <input class="form-check-input" 
                                                                   type="checkbox" 
                                                                   wire:click="toggleSubject({{ $subject->id }})"
                                                                   {{ in_array($subject->id, $selectedSubjects) ? 'checked' : '' }}
                                                                   id="subject-{{ $subject->id }}">
                                                            <label class="form-check-label w-100" for="subject-{{ $subject->id }}">
                                                                <h6 class="card-title">{{ $subject->course_code ?? 'N/A' }}</h6>
                                                                <h5 class="card-subtitle mb-2">{{ $subject->name }}</h5>
                                                                <p class="card-text">
                                                                    <small class="text-muted">
                                                                        <i class="fas fa-graduation-cap me-1"></i>
                                                                        {{ $subject->credit_hours ?? 'N/A' }} Credit Hours
                                                                    </small>
                                                                </p>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    @if(count($selectedSubjects) > 0)
                                        <div class="mt-4 text-center">
                                            <div class="alert alert-info">
                                                <i class="fas fa-info-circle me-2"></i>
                                                You have selected {{ count($selectedSubjects) }} subject(s) for registration.
                                            </div>
                                            <button type="button" 
                                                    wire:click="submitRegistration" 
                                                    class="btn btn-primary btn-lg"
                                                    wire:confirm="Are you sure you want to submit your course registration? This will replace any previous registrations for this semester.">
                                                <i class="fas fa-paper-plane me-2"></i>
                                                Submit Course Registration
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>

                        @elseif($registrationAllowed && $availableSubjects->isEmpty())
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                No subjects are available for registration in your class for this semester. Please contact the Academic Office.
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Course registration is currently disabled. Please ensure you meet the payment requirements.
                            </div>
                        @endif

                        <!-- Current/Previous Registrations -->
                        @if($existingRegistrations->isNotEmpty())
                            <div class="card mt-4">
                                <div class="card-header bg-secondary text-white">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-history me-2"></i>Your Course Registrations
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Course Code</th>
                                                    <th>Subject Name</th>
                                                    <th>Credit Hours</th>
                                                    <th>Registration Date</th>
                                                    <th>Status</th>
                                                    <th>Approval Info</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($existingRegistrations as $registration)
                                                    <tr>
                                                        <td>{{ $registration->subject->course_code ?? 'N/A' }}</td>
                                                        <td>{{ $registration->subject->name ?? 'N/A' }}</td>
                                                        <td>{{ $registration->subject->credit_hours ?? 'N/A' }}</td>
                                                        <td>{{ $registration->registered_at->format('M d, Y') }}</td>
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
                                                                    Approved on {{ $registration->approved_at->format('M d, Y') }}
                                                                </small>
                                                            @elseif($registration->rejected_at)
                                                                <small class="text-danger">
                                                                    Rejected on {{ $registration->rejected_at->format('M d, Y') }}
                                                                    @if($registration->rejection_reason)
                                                                        <br><em>{{ $registration->rejection_reason }}</em>
                                                                    @endif
                                                                </small>
                                                            @else
                                                                <small class="text-muted">Awaiting approval</small>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.subject-card {
    transition: all 0.3s ease;
    cursor: pointer;
}

.subject-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.subject-card.border-primary {
    background-color: #f8f9ff;
}

.border-left-success {
    border-left: 4px solid #28a745 !important;
}

.border-left-warning {
    border-left: 4px solid #ffc107 !important;
}

.form-check-label {
    cursor: pointer;
}
</style></div>