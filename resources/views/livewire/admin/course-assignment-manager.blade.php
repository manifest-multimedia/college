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
                        <i class="bi bi-person-badge me-2"></i>Course Assignments for Lecturers
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
                                                    <div class="d-flex flex-wrap gap-2">
                                                        @foreach($lecturer->assignedCourses as $course)
                                                            <span class="badge bg-primary d-inline-flex align-items-center gap-1" style="font-size: 0.875rem; padding: 0.4rem 0.6rem;">
                                                                {{ $course->course_code }}
                                                                <button type="button" 
                                                                        class="rounded-circle d-flex align-items-center justify-content-center"
                                                                        style="background-color: #dc3545; border: none; width: 20px; height: 20px; padding: 0; margin-left: 4px; min-width: 20px; cursor: pointer;"
                                                                        wire:click="removeCourseAssignment({{ $lecturer->id }}, {{ $course->id }})"
                                                                        wire:confirm="Are you sure you want to remove this course assignment?">
                                                                    <i class="bi bi-x text-white" style="font-size: 18px; line-height: 0;"></i>
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

                        <!-- Search Filter -->
                        <div class="mb-3">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" 
                                       class="form-control" 
                                       placeholder="Search courses by code or name..." 
                                       wire:model.live.debounce.300ms="modalSearchCourse">
                                @if($modalSearchCourse)
                                    <button class="btn btn-outline-secondary" 
                                            type="button" 
                                            wire:click="$set('modalSearchCourse', '')">
                                        <i class="bi bi-x"></i>
                                    </button>
                                @endif
                            </div>
                        </div>

                        <!-- Filter Options -->
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold"><i class="bi bi-building"></i> Program</label>
                                <select class="form-select" wire:model.live="modalFilterProgramId">
                                    <option value="">All Programs</option>
                                    @foreach($programs as $program)
                                        <option value="{{ $program->id }}">{{ $program->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold"><i class="bi bi-calendar3"></i> Semester</label>
                                <select class="form-select" wire:model.live="modalFilterSemesterId">
                                    <option value="">All Semesters</option>
                                    @foreach($semesters as $semester)
                                        <option value="{{ $semester->id }}">{{ $semester->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold"><i class="bi bi-calendar"></i> Year</label>
                                <select class="form-select" wire:model.live="modalFilterYearId">
                                    <option value="">All Years</option>
                                    @foreach($years as $year)
                                        <option value="{{ $year->id }}">{{ $year->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Select Courses:</label>
                            
                            @if(count($allCourses) > 0)
                                <div class="border rounded p-3" style="max-height: 400px; overflow-y: auto;">
                                    <div class="row">
                                        @foreach($allCourses as $course)
                                            <div class="col-md-12 mb-3">
                                                <div class="form-check border-bottom pb-2">
                                                    <input class="form-check-input" 
                                                           type="checkbox" 
                                                           id="course-{{ $course->id }}" 
                                                           value="{{ $course->id }}" 
                                                           wire:model="assignCourseIds">
                                                    <label class="form-check-label w-100" for="course-{{ $course->id }}">
                                                        <div class="d-flex justify-content-between align-items-start">
                                                            <div>
                                                                <strong class="text-primary">{{ $course->course_code }}</strong> - {{ $course->name }}
                                                                <br>
                                                                <small class="text-muted">
                                                                    @if($course->collegeClass)
                                                                        <i class="bi bi-building"></i> Program: {{ $course->collegeClass->name }}
                                                                    @endif
                                                                    @if($course->year)
                                                                        | <i class="bi bi-calendar"></i> Year: {{ $course->year->name }}
                                                                    @endif
                                                                    @if($course->semester)
                                                                        | <i class="bi bi-calendar3"></i> Semester: {{ $course->semester->name }}
                                                                    @endif
                                                                </small>
                                                            </div>
                                                        </div>
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-warning mb-0">
                                    <i class="bi bi-info-circle"></i> No courses found matching your search.
                                </div>
                            @endif
                            
                            @error('assignCourseIds') 
                                <span class="text-danger mt-2 d-block">{{ $message }}</span> 
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
