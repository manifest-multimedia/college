<div>
    @if(session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($student)
    <div class="d-flex justify-content-end mb-5">
        <a href="{{ route('students.edit', $student->id) }}" class="btn btn-primary">
            <i class="fas fa-edit me-2"></i>Edit Student
        </a>
    </div>

    <div class="row">
        <!-- Student Basic Information -->
        <div class="col-lg-6 mb-5">
            <div class="card shadow-sm">
                <div class="card-header">
                    <div class="card-title">
                        <h3 class="card-title fw-bold text-gray-800">
                            <i class="fas fa-user me-2"></i>Personal Information
                        </h3>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-5 text-center">
                        @if($student->profile_photo_url)
                            <img class="rounded-circle mb-3 shadow-sm" src="{{ $student->profile_photo_url }}" alt="{{ $student->first_name }}" width="150" height="150">
                        @else
                            <div class="bg-light rounded-circle d-inline-flex justify-content-center align-items-center mb-3" style="width: 150px; height: 150px;">
                                <span class="fs-1 fw-bold text-primary">{{ substr($student->first_name, 0, 1) }}{{ substr($student->last_name, 0, 1) }}</span>
                            </div>
                        @endif
                        <h3 class="fw-bold">{{ $student->first_name }} {{ $student->other_name }} {{ $student->last_name }}</h3>
                        <span class="badge badge-light-primary px-3 py-2 fs-6">{{ $student->student_id }}</span>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-row-bordered">
                            <tbody>
                                <tr>
                                    <th class="w-25 bg-light">Email</th>
                                    <td>{{ $student->email }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Phone</th>
                                    <td>{{ $student->mobile_number ?? 'Not provided' }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Status</th>
                                    <td>
                                        @if($student->status == 'Active')
                                            <span class="badge badge-light-success">{{ $student->status }}</span>
                                        @elseif($student->status == 'Inactive')
                                            <span class="badge badge-light-danger">{{ $student->status }}</span>
                                        @else
                                            <span class="badge badge-light-warning">{{ $student->status }}</span>
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Academic Information -->
        <div class="col-lg-6 mb-5">
            <div class="card shadow-sm">
                <div class="card-header">
                    <div class="card-title">
                        <h3 class="card-title fw-bold text-gray-800">
                            <i class="fas fa-graduation-cap me-2"></i>Academic Information
                        </h3>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-row-bordered">
                            <tbody>
                                <tr>
                                    <th class="w-25 bg-light">Program</th>
                                    <td>{{ $student->CollegeClass->name ?? 'Not assigned' }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Cohort</th>
                                    <td>{{ $student->Cohort->name ?? 'Not assigned' }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Entry Date</th>
                                    <td>{{ $student->created_at ? $student->created_at->format('F d, Y') : 'Not available' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Additional Student Information Cards -->
            <div class="card shadow-sm mt-5">
                <div class="card-header">
                    <div class="card-title">
                        <h3 class="card-title fw-bold text-gray-800">
                            <i class="fas fa-clipboard-list me-2"></i>Academic Progress
                        </h3>
                    </div>
                </div>
                <div class="card-body">
                    <!-- You can add academic progress indicators here -->
                    <div class="notice d-flex bg-light-info rounded border border-info border-dashed p-6">
                        <span class="svg-icon svg-icon-2tx svg-icon-info me-4">
                            <i class="fas fa-info-circle fs-1 text-info"></i>
                        </span>
                        <div class="d-flex flex-stack flex-grow-1">
                            <div class="fw-semibold">
                                <h4 class="text-gray-900 fw-bold">Academic Information</h4>
                                <div class="fs-6 text-gray-700">
                                    View the student's course registrations, grades, and academic records in the appropriate sections.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Exams Taken -->
    @if(auth()->user()->hasRole(['Super Admin', 'System User']))
    <div class="card shadow-sm mb-5">
        <div class="card-header">
            <div class="card-title">
                <h3 class="card-title fw-bold text-gray-800">
                    <i class="fas fa-laptop-code me-2"></i>Exams Taken
                </h3>
            </div>
        </div>
        <div class="card-body">
            @if($student->examSessions && $student->examSessions->count() > 0)
                <div class="table-responsive">
                    <table class="table table-row-bordered table-row-gray-100 align-middle gs-0 gy-3">
                        <thead>
                            <tr class="fw-bold text-muted">
                                <th class="min-w-150px">Exam</th>
                                <th class="min-w-140px">Started</th>
                                <th class="min-w-140px">Completed</th>
                                <th class="min-w-100px">Score</th>
                                <th class="min-w-100px">Status</th>
                                <th class="min-w-100px text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($student->examSessions as $session)
                                <tr>
                                    <td>
                                        <div class="text-dark fw-bold text-hover-primary fs-6">{{ $session->exam->course->course_code ?? '' }} - {{ $session->exam->type ?? 'Exam' }}</div>
                                        <span class="text-muted fw-semibold text-muted d-block fs-7">{{ $session->exam->slug ?? '' }}</span>
                                    </td>
                                    <td>
                                        <div class="text-dark fw-bold d-block fs-6">{{ $session->started_at ? $session->started_at->format('M d, Y H:i') : 'N/A' }}</div>
                                    </td>
                                    <td>
                                        <div class="text-dark fw-bold d-block fs-6">{{ $session->completed_at ? $session->completed_at->format('M d, Y H:i') : 'N/A' }}</div>
                                    </td>
                                    <td>
                                        <span class="badge badge-light-primary fs-7 fw-bold">{{ $session->score }}</span>
                                    </td>
                                    <td>
                                        @if($session->completed_at)
                                            <span class="badge badge-light-success">Completed</span>
                                        @elseif($session->started_at)
                                            <span class="badge badge-light-warning">In Progress</span>
                                        @else
                                            <span class="badge badge-light-secondary">Not Started</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <button wire:click="deleteExamSession({{ $session->id }})" 
                                                wire:confirm="Are you sure you want to delete this exam session? This action cannot be undone."
                                                class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm">
                                            <i class="fas fa-trash fs-4"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="notice d-flex bg-light-warning rounded border border-warning border-dashed p-6">
                    <span class="svg-icon svg-icon-2tx svg-icon-warning me-4">
                        <i class="fas fa-exclamation-circle fs-1 text-warning"></i>
                    </span>
                    <div class="d-flex flex-stack flex-grow-1">
                        <div class="fw-semibold">
                            <h4 class="text-gray-900 fw-bold">No Exams Found</h4>
                            <div class="fs-6 text-gray-700">
                                This student has not taken any exams yet.
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
    @endif
    
    <!-- Financial Information -->
    <div class="card shadow-sm mb-5">
        <div class="card-header">
            <div class="card-title">
                <h3 class="card-title fw-bold text-gray-800">
                    <i class="fas fa-money-bill me-2"></i>Financial Information
                </h3>
            </div>
        </div>
        <div class="card-body">
            <div class="notice d-flex bg-light-primary rounded border border-primary border-dashed p-6">
                <span class="svg-icon svg-icon-2tx svg-icon-primary me-4">
                    <i class="fas fa-info-circle fs-1 text-primary"></i>
                </span>
                <div class="d-flex flex-stack flex-grow-1">
                    <div class="fw-semibold">
                        <h4 class="text-gray-900 fw-bold">Financial Records</h4>
                        <div class="fs-6 text-gray-700">
                            To view detailed financial records for this student, please visit the Finance module.
                        </div>
                    </div>
                    <a href="{{ route('finance.payments') }}?student={{ $student->id }}" class="btn btn-primary">View Financial Records</a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Activity -->
    <div class="card shadow-sm">
        <div class="card-header">
            <div class="card-title">
                <h3 class="card-title fw-bold text-gray-800">
                    <i class="fas fa-history me-2"></i>Recent Activity
                </h3>
            </div>
        </div>
        <div class="card-body">
            <div class="timeline">
                <!-- This is a placeholder for potential student activities -->
                <div class="timeline-item">
                    <div class="timeline-line w-40px"></div>
                    <div class="timeline-icon symbol symbol-circle symbol-40px">
                        <div class="symbol-label bg-light">
                            <i class="fas fa-edit fs-2 text-gray-500"></i>
                        </div>
                    </div>
                    <div class="timeline-content mb-10 mt-n1">
                        <div class="pe-3 mb-5">
                            <div class="fs-5 fw-bold mb-2">Profile Updated</div>
                            <div class="d-flex align-items-center mt-1 fs-6">
                                <div class="text-muted me-2 fs-7">Updated on</div>
                                <div class="text-muted me-2 fs-7">{{ $student->updated_at->format('F d, Y') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-line w-40px"></div>
                    <div class="timeline-icon symbol symbol-circle symbol-40px">
                        <div class="symbol-label bg-light">
                            <i class="fas fa-user-plus fs-2 text-gray-500"></i>
                        </div>
                    </div>
                    <div class="timeline-content mb-10 mt-n1">
                        <div class="pe-3 mb-5">
                            <div class="fs-5 fw-bold mb-2">Student Created</div>
                            <div class="d-flex align-items-center mt-1 fs-6">
                                <div class="text-muted me-2 fs-7">Created on</div>
                                <div class="text-muted me-2 fs-7">{{ $student->created_at->format('F d, Y') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="card shadow-sm">
        <div class="card-body text-center py-10">
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle fs-2 mb-3"></i>
                <h4>Student not found</h4>
                <p>The student information could not be loaded. Please try again or contact support if the problem persists.</p>
                <a href="{{ route('students') }}" class="btn btn-primary">Back to Students</a>
            </div>
        </div>
    </div>
    @endif
</div>