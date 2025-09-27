<div>
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="card-title">
                    <i class="fas fa-book"></i> Course Registration
                </h1>
            </div>
        </div>
        <div class="card-body">
            <!-- Student Selection and Semester Filters -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="studentId">Student</label>
                        <select wire:model.live="studentId" class="form-control @error('studentId') is-invalid @enderror">
                            <option value="">-- Select Student --</option>
                            <!-- Admin can select a student, students will see their own ID pre-selected -->
                            {{-- @if(auth()->user()->hasRole('admin'))
                                @foreach(\App\Models\Student::orderBy('last_name')->get() as $s)
                                    <option value="{{ $s->id }}">{{ $s->student_id }} - {{ $s->full_name }}</option>
                                @endforeach
                            @else
                                <option value="{{ auth()->user()->student->id }}">
                                    {{ auth()->user()->student->student_id }} - {{ auth()->user()->student->full_name }}
                                </option>
                            @endif --}}
                        </select>
                        @error('studentId')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="academicYearId">Academic Year</label>
                        <select wire:model.live="academicYearId" class="form-control @error('academicYearId') is-invalid @enderror">
                            <option value="">-- Select Academic Year --</option>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}">{{ $year->name }}</option>
                            @endforeach
                        </select>
                        @error('academicYearId')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="semesterId">Semester</label>
                        <select wire:model.live="semesterId" class="form-control @error('semesterId') is-invalid @enderror">
                            <option value="">-- Select Semester --</option>
                            @foreach($semesters as $semester)
                                <option value="{{ $semester->id }}">{{ $semester->name }}</option>
                            @endforeach
                        </select>
                        @error('semesterId')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            @if($student)
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h5 class="card-title mb-0">Student Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        @if($student->photo)
                                            <img src="{{ asset('storage/' . $student->photo) }}" 
                                                alt="Student Photo" class="img-fluid rounded mb-3">
                                        @else
                                            <div class="bg-secondary rounded d-flex justify-content-center align-items-center text-white"
                                                style="height: 150px;">
                                                <i class="fas fa-user fa-3x"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="col-md-9">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p><strong>Name:</strong> {{ $student->full_name }}</p>
                                                <p><strong>Student ID:</strong> {{ $student->student_id }}</p>
                                                <p><strong>Program:</strong> {{ $student->program->name ?? 'Not Assigned' }}</p>
                                            </div>
                                            <div class="col-md-6">
                                                <p><strong>Class:</strong> {{ $student->collegeClass->name ?? 'Not Assigned' }}</p>
                                                <p><strong>Level:</strong> {{ $student->level ?? 'Not Assigned' }}</p>
                                                <p><strong>Status:</strong> {{ $student->status }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Fee Payment Status and Registration Eligibility -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="alert alert-{{ $registrationMessageType }} mb-4">
                            <div class="d-flex">
                                <div class="me-3">
                                    @if($registrationMessageType == 'success')
                                        <i class="fas fa-check-circle fa-2x"></i>
                                    @elseif($registrationMessageType == 'warning')
                                        <i class="fas fa-exclamation-triangle fa-2x"></i>
                                    @else
                                        <i class="fas fa-times-circle fa-2x"></i>
                                    @endif
                                </div>
                                <div>
                                    <h5 class="alert-heading">Fee Payment Status</h5>
                                    <p class="mb-0">{{ $registrationMessage }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                @if($registrationAllowed)
                    <!-- Course Selection Form -->
                    <form wire:submit.prevent="registerCourses">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h5 class="card-title mb-0">Available Courses for Registration</h5>
                                    </div>
                                    <div class="card-body">
                                        @if($availableCourses->isEmpty())
                                            <div class="alert alert-info">
                                                <i class="fas fa-info-circle"></i> No courses available for this student's program and semester.
                                            </div>
                                        @else
                                            <div class="table-responsive">
                                                <table class="table table-bordered">
                                                    <thead class="thead-light">
                                                        <tr>
                                                            <th width="5%">Select</th>
                                                            <th width="15%">Code</th>
                                                            <th width="40%">Course Title</th>
                                                            <th width="10%">Credits</th>
                                                            <th width="15%">Type</th>
                                                            <th width="15%">Status</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($availableCourses as $course)
                                                            <tr>
                                                                <td class="text-center">
                                                                    <div class="form-check">
                                                                        <input type="checkbox" 
                                                                            wire:model.live="selectedCourses" 
                                                                            value="{{ $course->id }}" 
                                                                            class="form-check-input" 
                                                                            id="course-{{ $course->id }}">
                                                                    </div>
                                                                </td>
                                                                <td>{{ $course->code }}</td>
                                                                <td>{{ $course->title }}</td>
                                                                <td>{{ $course->credit_hours }}</td>
                                                                <td>{{ $course->is_elective ? 'Elective' : 'Core' }}</td>
                                                                <td>
                                                                    @if(in_array($course->id, $selectedCourses))
                                                                        <span class="badge bg-success">Selected</span>
                                                                    @else
                                                                        <span class="badge bg-secondary">Not Selected</span>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                            
                                            <div class="mt-3 d-flex justify-content-between">
                                                <div>
                                                    <span class="fw-bold">Selected:</span> {{ count($selectedCourses) }} courses
                                                </div>
                                                <div>
                                                    <button type="submit" class="btn btn-primary" {{ count($selectedCourses) ? '' : 'disabled' }}>
                                                        <i class="fas fa-save"></i> Register Selected Courses
                                                    </button>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                @else
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> Course registration is disabled until the payment requirement is met.
                    </div>
                @endif
                
                <!-- Current Registrations -->
                @if($registeredCourses->isNotEmpty())
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h5 class="card-title mb-0">Current Course Registrations</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th width="12%">Code</th>
                                                    <th width="35%">Course Title</th>
                                                    <th width="8%">Credits</th>
                                                    <th width="15%">Registration Date</th>
                                                    <th width="15%">Status</th>
                                                    <th width="15%">Approval Info</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($registeredCourses as $registration)
                                                    <tr>
                                                        <td>{{ $registration->subject->course_code ?? 'N/A' }}</td>
                                                        <td>{{ $registration->subject->name ?? 'N/A' }}</td>
                                                        <td>{{ $registration->subject->credit_hours ?? 'N/A' }}</td>
                                                        <td>{{ $registration->registered_at->format('d M Y') }}</td>
                                                        <td>
                                                            @if($registration->is_approved)
                                                                <span class="badge badge-success">
                                                                    <i class="fas fa-check"></i> Approved
                                                                </span>
                                                            @elseif($registration->rejected_at)
                                                                <span class="badge badge-danger">
                                                                    <i class="fas fa-times"></i> Rejected
                                                                </span>
                                                            @else
                                                                <span class="badge badge-warning">
                                                                    <i class="fas fa-clock"></i> Pending
                                                                </span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($registration->is_approved && $registration->approved_at)
                                                                <small class="text-muted">
                                                                    Approved on<br>{{ $registration->approved_at->format('M d, Y') }}
                                                                </small>
                                                            @elseif($registration->rejected_at)
                                                                <small class="text-muted">
                                                                    Rejected on<br>{{ $registration->rejected_at->format('M d, Y') }}
                                                                    @if($registration->rejection_reason)
                                                                        <br><em>{{ Str::limit($registration->rejection_reason, 30) }}</em>
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
                                    
                                    <div class="mt-3">
                                        <a href="#" onclick="window.print()" class="btn btn-secondary">
                                            <i class="fas fa-print"></i> Print Registration
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endif
            
            <!-- Flash Messages -->
            @if(session()->has('message'))
                <div class="alert alert-success mt-3">
                    <i class="fas fa-check-circle"></i> {{ session('message') }}
                </div>
            @endif
            
            @if(session()->has('error'))
                <div class="alert alert-danger mt-3">
                    <i class="fas fa-times-circle"></i> {{ session('error') }}
                </div>
            @endif
        </div>
    </div>
</div>