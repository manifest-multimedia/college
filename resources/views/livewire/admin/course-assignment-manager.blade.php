<div>
    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-person-badge"></i> Course Assignments for Lecturers
                    </h5>
                </div>
                <div class="card-body">
                        <!-- Search -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" class="form-control" placeholder="Search lecturers by name or email..." 
                                           wire:model.live.debounce.300ms="searchLecturer">
                                </div>
                            </div>
                        </div>

                        <!-- Lecturers Table -->
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Lecturer</th>
                                        <th>Email</th>
                                        <th>Assigned Courses</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($lecturers as $lecturer)
                                        <tr>
                                            <td>{{ $lecturer->name }}</td>
                                            <td>{{ $lecturer->email }}</td>
                                            <td>
                                                @if($lecturer->assignedCourses->count() > 0)
                                                    <div class="d-flex flex-wrap gap-1">
                                                        @foreach($lecturer->assignedCourses as $course)
                                                            <span class="badge bg-primary position-relative">
                                                                {{ $course->course_code }}
                                                                <button type="button" 
                                                                        class="btn-close btn-close-white position-absolute top-0 start-100 translate-middle" 
                                                                        style="font-size: 0.6rem; padding: 0.15rem;"
                                                                        wire:click="removeCourseAssignment({{ $lecturer->id }}, {{ $course->id }})"
                                                                        wire:confirm="Are you sure you want to remove this course assignment?">
                                                                </button>
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <span class="text-muted">No courses assigned</span>
                                                @endif
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-primary" 
                                                        wire:click="openAssignModal({{ $lecturer->id }})">
                                                    <i class="bi bi-pencil"></i> Manage Courses
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">
                                                No lecturers found
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                    <!-- Pagination -->
                    <div class="mt-3">
                        {{ $lecturers->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Assignment Modal -->
    @if($showAssignModal)
        <div class="modal fade show" tabindex="-1" style="display: block; background-color: rgba(0,0,0,0.5);" aria-modal="true" role="dialog">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Assign Courses to Lecturer</h5>
                        <button type="button" class="btn-close" wire:click="closeAssignModal"></button>
                    </div>
                    <div class="modal-body">
                        @if($assignLecturerId)
                            @php
                                $lecturer = $lecturers->firstWhere('id', $assignLecturerId);
                            @endphp
                            <div class="alert alert-info mb-3">
                                <strong>Lecturer:</strong> {{ $lecturer->name ?? 'Unknown' }} ({{ $lecturer->email ?? 'N/A' }})
                            </div>
                        @endif

                        <div class="mb-3">
                            <label class="form-label fw-bold">Select Courses:</label>
                            <div class="row">
                                @foreach($allCourses as $course)
                                    <div class="col-md-6 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" 
                                                   type="checkbox" 
                                                   id="course-{{ $course->id }}" 
                                                   value="{{ $course->id }}" 
                                                   wire:model="assignCourseIds">
                                            <label class="form-check-label" for="course-{{ $course->id }}">
                                                <strong>{{ $course->course_code }}</strong> - {{ $course->name }}
                                                @if($course->collegeClass)
                                                    <br><small class="text-muted">Class: {{ $course->collegeClass->name }}</small>
                                                @endif
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @error('assignCourseIds') 
                                <span class="text-danger">{{ $message }}</span> 
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeAssignModal">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="assignCourses">
                            <i class="bi bi-check-circle"></i> Save Assignments
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
