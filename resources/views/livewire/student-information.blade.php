<div>
    @if($student)
        <div class="row">
            <!-- Student Personal Information -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="card-title">
                                <i class="ki-duotone ki-profile-circle fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                </i>
                                Personal Information
                            </h3>
                            <button wire:click="toggleEdit" class="btn btn-sm btn-primary">
                                {{ $isEditing ? 'Cancel' : 'Edit' }}
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Student ID</label>
                                    <p class="form-control-plaintext">{{ $student->student_id }}</p>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Full Name</label>
                                    <p class="form-control-plaintext">{{ $student->first_name }} {{ $student->last_name }}</p>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Email Address</label>
                                    <p class="form-control-plaintext">{{ $student->email }}</p>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Phone Number</label>
                                    <p class="form-control-plaintext">{{ $student->mobile_number ?? 'Not provided' }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Date of Birth</label>
                                    <p class="form-control-plaintext">{{ $student->date_of_birth ? $student->date_of_birth->format('F j, Y') : 'Not provided' }}</p>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Gender</label>
                                    <p class="form-control-plaintext">{{ $student->gender ?? 'Not provided' }}</p>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Program</label>
                                    <p class="form-control-plaintext">{{ $student->program ?? 'Not assigned' }}</p>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Level</label>
                                    <p class="form-control-plaintext">{{ $student->level ?? 'Not assigned' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Student Statistics -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="ki-duotone ki-chart fs-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            Quick Stats
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-40px me-3">
                                    <span class="symbol-label bg-light-primary">
                                        <i class="ki-duotone ki-book fs-2 text-primary">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                    </span>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-bold">{{ $courseRegistrations->count() }}</div>
                                    <div class="text-muted fs-7">Registered Courses</div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-4">
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-40px me-3">
                                    <span class="symbol-label bg-light-success">
                                        <i class="ki-duotone ki-calendar fs-2 text-success">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                    </span>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-bold">{{ $student->created_at->format('Y') }}</div>
                                    <div class="text-muted fs-7">Year Enrolled</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Course Registrations -->
        @if($courseRegistrations->count() > 0)
            <div class="card mt-6">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="ki-duotone ki-book fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        Registered Courses
                    </h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped gy-7 gs-7">
                            <thead>
                                <tr class="fw-bold fs-6 text-gray-800 border-bottom-2 border-gray-200">
                                    <th>Course Code</th>
                                    <th>Course Name</th>
                                    <th>Academic Year</th>
                                    <th>Semester</th>
                                    <th>Registration Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($courseRegistrations as $registration)
                                    <tr>
                                        <td>{{ $registration->subject->subject_code ?? 'N/A' }}</td>
                                        <td>{{ $registration->subject->subject_name ?? 'N/A' }}</td>
                                        <td>{{ $registration->academicYear->year ?? 'N/A' }}</td>
                                        <td>{{ $registration->semester->name ?? 'N/A' }}</td>
                                        <td>{{ $registration->created_at->format('M j, Y') }}</td>
                                        <td>
                                            <span class="badge badge-light-success">Active</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    @else
        <div class="card">
            <div class="card-body text-center py-12">
                <i class="ki-duotone ki-information fs-3x text-muted mb-4">
                    <span class="path1"></span>
                    <span class="path2"></span>
                    <span class="path3"></span>
                </i>
                <h3 class="text-gray-800 mb-4">Student Record Not Found</h3>
                <p class="text-muted fs-6 mb-6">
                    We couldn't find a student record associated with your account. 
                    Please contact the administration office for assistance.
                </p>
                <a href="{{ route('supportcenter') }}" class="btn btn-primary" target="_blank">
                    Contact Support
                </a>
            </div>
        </div>
    @endif
</div>
