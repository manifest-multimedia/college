<x-dashboard.default title="Student Dashboard">
    <div class="row">
        <div class="col-12">
            <!-- Welcome Header -->
            <div class="card mb-5">
                <div class="card-body p-8" style="background: linear-gradient(90deg, #20AA3E 0%, #03A588 100%);">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="text-white">
                                <h2 class="text-white fw-bold mb-2">Welcome, {{ Auth::user()->name }}!</h2>
                                <p class="mb-3 opacity-75">Student Portal - Access your academic information, view grades, and manage your studies</p>
                                @if($student)
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="text-white-50 fw-semibold">Student ID</div>
                                            <div class="text-white fw-bold">{{ $student->student_id ?? 'Not Assigned' }}</div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="text-white-50 fw-semibold">Class</div>
                                            <div class="text-white fw-bold">{{ $student->collegeClass->name ?? 'Not Assigned' }}</div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="text-white-50 fw-semibold">Status</div>
                                            <div class="text-white fw-bold">{{ ucfirst($student->status ?? 'Active') }}</div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-4 text-center">
                            <i class="fas fa-user-graduate text-white" style="font-size: 4rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="row g-5 mb-5">
                <div class="col-xl-3 col-md-6">
                    <div class="card card-stretch">
                        <div class="card-header border-0 bg-primary text-white">
                            <h3 class="card-title fw-bold text-white">Enrolled Courses</h3>
                        </div>
                        <div class="card-body text-center">
                            <h1 class="text-primary fw-bold">{{ $enrolledCourses }}</h1>
                            <div class="text-muted">This Semester</div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6">
                    <div class="card card-stretch">
                        <div class="card-header border-0 bg-success text-white">
                            <h3 class="card-title fw-bold text-white">Fee Payment</h3>
                        </div>
                        <div class="card-body text-center">
                            <h1 class="text-success fw-bold">{{ number_format($paymentPercentage, 1) }}%</h1>
                            <div class="text-muted">Current Semester</div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6">
                    <div class="card card-stretch">
                        <div class="card-header border-0 bg-info text-white">
                            <h3 class="card-title fw-bold text-white">Exams Taken</h3>
                        </div>
                        <div class="card-body text-center">
                            <h1 class="text-info fw-bold">{{ $examsTaken }}</h1>
                            <div class="text-muted">This Semester</div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6">
                    <div class="card card-stretch">
                        <div class="card-header border-0 bg-warning text-white">
                            <h3 class="card-title fw-bold text-white">Outstanding Balance</h3>
                        </div>
                        <div class="card-body text-center">
                            @if(($balanceDisplayType ?? 'zero') === 'credit')
                                <h1 class="fw-bold text-success">(GH₵{{ number_format($balanceDisplayAmount ?? 0, 2) }})</h1>
                                <div class="text-muted small">Credit in your favor</div>
                            @elseif(($balanceDisplayType ?? 'zero') === 'debit')
                                <h1 class="fw-bold text-danger">GH₵{{ number_format($balanceDisplayAmount ?? 0, 2) }}</h1>
                                <div class="text-muted">Current Semester</div>
                            @else
                                <h1 class="fw-bold text-body">GH₵{{ number_format($balanceDisplayAmount ?? 0, 2) }}</h1>
                                <div class="text-muted">Current Semester</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row g-5 mb-5">
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-book-open me-2"></i>Course Registration
                            </h3>
                        </div>
                        <div class="card-body">
                            @if($paymentPercentage >= 60)
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle me-2"></i>
                                    You are eligible for course registration ({{ number_format($paymentPercentage, 1) }}% fees paid)@if(($balanceDisplayType ?? 'zero') === 'credit') — credit (GH₵{{ number_format($balanceDisplayAmount ?? 0, 2) }})@endif
                                </div>
                                <a href="{{ route('courseregistration') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Register for Courses
                                </a>
                            @else
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    You need to pay at least 60% of your fees to register for courses. 
                                    Current payment: {{ number_format($paymentPercentage, 1) }}%
                                </div>
                                <p class="text-muted">Please visit the Finance Department to make payments.</p>
                            @endif
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-file-invoice-dollar me-2"></i>Financial Status
                            </h3>
                        </div>
                        <div class="card-body">
                            @if($paymentPercentage >= 100)
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle me-2"></i>
                                    All fees paid - You're eligible for exams
                                </div>
                            @elseif($paymentPercentage >= 60)
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Partial payment completed - Course registration available
                                </div>
                            @else
                                <div class="alert alert-danger">
                                    <i class="fas fa-times-circle me-2"></i>
                                    Payment below 60% - Limited access to services
                                </div>
                            @endif
                            
                            @if(($balanceDisplayType ?? 'zero') === 'debit')
                                <p><strong>Outstanding Balance:</strong> <span class="text-danger fw-bold">GH₵{{ number_format($balanceDisplayAmount ?? 0, 2) }}</span></p>
                            @elseif(($balanceDisplayType ?? 'zero') === 'credit')
                                <p><strong>Credit balance:</strong> <span class="text-success fw-bold">(GH₵{{ number_format($balanceDisplayAmount ?? 0, 2) }})</span></p>
                            @else
                                <p><strong>Outstanding Balance:</strong> <span class="text-body">GH₵0.00</span></p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity & Information -->
            <div class="row g-5">
                <!-- Registered Courses -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-graduation-cap me-2"></i>My Courses This Semester
                            </h3>
                        </div>
                        <div class="card-body">
                            @if($registeredCourses->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Course Code</th>
                                                <th>Course Title</th>
                                                <th>Credit Hours</th>
                                                <th>Registration Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($registeredCourses as $registration)
                                                <tr>
                                                    <td>{{ $registration->subject->course_code ?? 'N/A' }}</td>
                                                    <td>{{ $registration->subject->name ?? 'N/A' }}</td>
                                                    <td>{{ $registration->subject->credit_hours ?? 'N/A' }}</td>
                                                    <td>{{ $registration->registered_at->format('M d, Y') }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <i class="fas fa-book-open text-muted" style="font-size: 3rem;"></i>
                                    <h5 class="text-muted mt-3">No courses registered yet</h5>
                                    <p class="text-muted">Register for courses to see them here</p>
                                    @if($paymentPercentage >= 60)
                                        <a href="{{ route('courseregistration') }}" class="btn btn-primary">Register Now</a>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Academic Info & Quick Links -->
                <div class="col-lg-4">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-user me-2"></i>Academic Information
                            </h3>
                        </div>
                        <div class="card-body">
                            @if($student)
                                <div class="mb-3">
                                    <span class="text-muted">Student ID:</span>
                                    <div class="fw-bold">{{ $student->student_id ?? 'Not Assigned' }}</div>
                                </div>
                                <div class="mb-3">
                                    <span class="text-muted">Class:</span>
                                    <div class="fw-bold">{{ $student->collegeClass->name ?? 'Not Assigned' }}</div>
                                </div>
                                <div class="mb-3">
                                    <span class="text-muted">Email:</span>
                                    <div class="fw-bold">{{ $student->email }}</div>
                                </div>
                                <div class="mb-3">
                                    <span class="text-muted">Status:</span>
                                    <span class="badge badge-light-{{ $student->status === 'active' ? 'success' : 'warning' }}">
                                        {{ ucfirst($student->status ?? 'Active') }}
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Quick Links -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-external-link-alt me-2"></i>Quick Links
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="{{ route('courseregistration') }}" class="btn btn-light-primary">
                                    <i class="fas fa-book me-2"></i>Course Registration
                                </a>
                                <a href="{{ route('courseregistration.history') }}" class="btn btn-light-info">
                                    <i class="fas fa-history me-2"></i>Registration History
                                </a>
                                @if(Route::has('student.grades') && auth()->user()->can('view grades'))
                                    <a href="{{ route('student.grades') }}" class="btn btn-light-success">
                                        <i class="fas fa-star me-2"></i>View Grades
                                    </a>
                                @endif
                                @if(Route::has('student.transcripts') && auth()->user()->can('view transcripts'))
                                    <a href="{{ route('student.transcripts') }}" class="btn btn-light-warning">
                                        <i class="fas fa-file-alt me-2"></i>Academic Transcript
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-dashboard.default>
