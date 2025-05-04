<x-dashboard.default>
    <x-slot name="title">
        Course Details
    </x-slot>
    
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title">
                                <i class="fas fa-book me-2"></i>Course Details
                            </h5>
                            <div>
                                <a href="{{ route('academics.courses.index') }}" class="btn btn-sm btn-secondary me-2">
                                    <i class="fas fa-arrow-left me-1"></i> Back to Courses
                                </a>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('academics.courses.edit', $course) }}" class="btn btn-warning">
                                        <i class="fas fa-edit me-1"></i> Edit
                                    </a>
                                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteCourseModal">
                                        <i class="fas fa-trash me-1"></i> Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                        
                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="card mb-4">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="card-title mb-0">Basic Information</h5>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-striped">
                                            <tbody>
                                                <tr>
                                                    <th style="width: 200px;">Course Code</th>
                                                    <td>{{ $course->course_code }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Name</th>
                                                    <td>{{ $course->name }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Description</th>
                                                    <td>{{ $course->description ?: 'No description provided' }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Created By</th>
                                                    <td>{{ optional($course->creator)->name ?? 'System' }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Created At</th>
                                                    <td>{{ $course->created_at->format('F d, Y h:i A') }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Last Updated</th>
                                                    <td>{{ $course->updated_at->format('F d, Y h:i A') }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="card mb-4">
                                    <div class="card-header bg-info text-white">
                                        <h5 class="card-title mb-0">Stats</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-6 mb-3">
                                                <div class="text-center">
                                                    <h5>Active Classes</h5>
                                                    <h1 class="display-5">{{ $activeClasses->count() }}</h1>
                                                </div>
                                            </div>
                                            <div class="col-6 mb-3">
                                                <div class="text-center">
                                                    <h5>Total Students</h5>
                                                    <h1 class="display-5">{{ $totalStudents }}</h1>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-grid gap-2">
                                            <a href="{{ route('academics.classes.create', ['course_id' => $course->id]) }}" class="btn btn-outline-primary">
                                                <i class="fas fa-plus-circle me-1"></i> Create New Class
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Active Classes -->
                        <div class="card mt-4">
                            <div class="card-header bg-success text-white">
                                <h5 class="card-title mb-0">Active Classes</h5>
                            </div>
                            <div class="card-body">
                                @if($activeClasses->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Class Name</th>
                                                    <th>Semester</th>
                                                    <th>Instructor</th>
                                                    <th>Students</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($activeClasses as $class)
                                                    <tr>
                                                        <td>{{ $class->name }}</td>
                                                        <td>
                                                            @if($class->semester)
                                                                {{ $class->semester->name }} 
                                                                ({{ optional($class->semester->academicYear)->name ?? 'No Academic Year' }})
                                                            @else
                                                                <span class="text-muted">No semester assigned</span>
                                                            @endif
                                                        </td>
                                                        <td>{{ optional($class->instructor)->name ?? 'No instructor assigned' }}</td>
                                                        <td>
                                                            <span class="badge bg-info">{{ $class->students->count() }}</span>
                                                        </td>
                                                        <td>
                                                            <a href="{{ route('academics.classes.show', $class) }}" class="btn btn-sm btn-primary">
                                                                <i class="fas fa-eye me-1"></i> View
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i> There are no active classes for this course.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Delete Course Modal -->
    <div class="modal fade" id="deleteCourseModal" tabindex="-1" aria-labelledby="deleteCourseModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteCourseModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete the course <strong>{{ $course->name }}</strong> ({{ $course->course_code }})?
                    
                    @if($activeClasses->count() > 0)
                        <div class="alert alert-warning mt-3">
                            <i class="fas fa-exclamation-triangle me-2"></i> This course has {{ $activeClasses->count() }} active 
                            {{ \Illuminate\Support\Str::plural('class', $activeClasses->count()) }}. 
                            Deleting this course will affect these classes and their students.
                        </div>
                    @endif
                    
                    <p class="mt-3">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form action="{{ route('academics.courses.destroy', $course) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete Course</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-dashboard.default>