<div>
    <div class="row">
        <div class="col-12">
            <!-- Student Information Card -->
            @if($student)
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user-graduate me-2"></i>Student Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Student ID:</strong> {{ $student->student_id }}</p>
                            <p><strong>Name:</strong> {{ $student->first_name }} {{ $student->last_name }}</p>
                            <p><strong>Class:</strong> {{ $student->collegeClass->name ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Academic Year:</strong> {{ $currentAcademicYear->name ?? 'N/A' }}</p>
                            <p><strong>Semester:</strong> {{ $currentSemester->name ?? 'N/A' }}</p>
                            <p><strong>Status:</strong> 
                                <span class="badge badge-{{ $registrationMessageType == 'success' ? 'success' : ($registrationMessageType == 'warning' ? 'warning' : 'danger') }}">
                                    {{ $student->status ?? 'Active' }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Registration Eligibility Status -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="alert alert-{{ $registrationMessageType }} mb-0">
                        <i class="fas fa-{{ $registrationMessageType == 'success' ? 'check-circle' : ($registrationMessageType == 'warning' ? 'exclamation-triangle' : 'times-circle') }} me-2"></i>
                        {{ $registrationMessage }}
                    </div>
                </div>
            </div>

            @if($registrationAllowed && $availableSubjects->isNotEmpty())
                <!-- Course Selection Form -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-book me-2"></i>Available Courses for Registration
                        </h5>
                    </div>
                    <div class="card-body">
                        <form wire:submit.prevent="submitRegistration">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="5%">Select</th>
                                            <th width="15%">Course Code</th>
                                            <th width="45%">Course Title</th>
                                            <th width="10%">Credits</th>
                                            <th width="25%">Description</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($availableSubjects as $subject)
                                            <tr>
                                                <td class="text-center">
                                                    <input type="checkbox" 
                                                           wire:model="selectedSubjects" 
                                                           value="{{ $subject->id }}" 
                                                           class="form-check-input" 
                                                           id="subject-{{ $subject->id }}">
                                                </td>
                                                <td>
                                                    <label for="subject-{{ $subject->id }}" class="form-label mb-0 fw-bold">
                                                        {{ $subject->course_code }}
                                                    </label>
                                                </td>
                                                <td>
                                                    <label for="subject-{{ $subject->id }}" class="form-label mb-0">
                                                        {{ $subject->name }}
                                                    </label>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge badge-info">{{ $subject->credit_hours ?? 3 }}</span>
                                                </td>
                                                <td>
                                                    <small class="text-muted">
                                                        {{ Str::limit($subject->description ?? 'Course Description', 50) }}
                                                    </small>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            @error('selectedSubjects')
                                <div class="alert alert-danger mt-3">
                                    <i class="fas fa-exclamation-triangle me-2"></i>{{ $message }}
                                </div>
                            @enderror

                            <!-- Registration Summary -->
                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6 class="card-title">Registration Summary</h6>
                                            <p class="mb-1"><strong>Selected Courses:</strong> {{ count($selectedSubjects) }}</p>
                                            <p class="mb-0"><strong>Total Credits:</strong> 
                                                <span class="badge badge-primary">{{ $totalCredits }}</span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 d-flex align-items-end">
                                    <div class="w-100">
                                        <button type="submit" 
                                                class="btn btn-success btn-lg w-100" 
                                                {{ count($selectedSubjects) > 0 ? '' : 'disabled' }}>
                                            <i class="fas fa-paper-plane me-2"></i>
                                            Submit Course Registration
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            @elseif($registrationAllowed && $availableSubjects->isEmpty())
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No Courses Available</h5>
                        <p class="text-muted">There are no courses available for registration in the current semester. Please contact the academic office.</p>
                    </div>
                </div>
            @endif

            <!-- Current Registrations -->
            @if($currentRegistrations->isNotEmpty())
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-list me-2"></i>Your Current Course Registrations
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Course Code</th>
                                        <th>Course Title</th>
                                        <th>Credits</th>
                                        <th>Registration Date</th>
                                        <th>Status</th>
                                        <th>Approval Info</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($currentRegistrations as $registration)
                                        <tr>
                                            <td>{{ $registration->subject->course_code ?? 'N/A' }}</td>
                                            <td>{{ $registration->subject->name ?? 'N/A' }}</td>
                                            <td class="text-center">
                                                <span class="badge badge-info">{{ $registration->subject->credit_hours ?? 3 }}</span>
                                            </td>
                                            <td>{{ $registration->registered_at->format('M d, Y') }}</td>
                                            <td>
                                                @if($registration->is_approved)
                                                    <span class="badge badge-success">
                                                        <i class="fas fa-check me-1"></i>Approved
                                                    </span>
                                                @elseif($registration->rejected_at)
                                                    <span class="badge badge-danger">
                                                        <i class="fas fa-times me-1"></i>Rejected
                                                    </span>
                                                @else
                                                    <span class="badge badge-warning">
                                                        <i class="fas fa-clock me-1"></i>Pending
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($registration->is_approved && $registration->approved_at)
                                                    <small class="text-success">
                                                        <i class="fas fa-check-circle me-1"></i>
                                                        Approved on {{ $registration->approved_at->format('M d, Y') }}
                                                    </small>
                                                @elseif($registration->rejected_at)
                                                    <small class="text-danger">
                                                        <i class="fas fa-times-circle me-1"></i>
                                                        Rejected on {{ $registration->rejected_at->format('M d, Y') }}
                                                        @if($registration->rejection_reason)
                                                            <br><em class="text-muted">{{ $registration->rejection_reason }}</em>
                                                        @endif
                                                    </small>
                                                @else
                                                    <small class="text-muted">
                                                        <i class="fas fa-hourglass-half me-1"></i>
                                                        Awaiting Finance Department approval
                                                    </small>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Total Registered Courses:</strong> {{ $currentRegistrations->count() }}
                                    <br>
                                    <strong>Total Credits:</strong> {{ $currentRegistrations->sum(fn($reg) => $reg->subject->credit_hours ?? 3) }}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Note:</strong> Course registrations require approval from the Finance Department. 
                                    You will be notified once your registration is processed.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Flash Messages -->
    @if(session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
            <i class="fas fa-times-circle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
</div>