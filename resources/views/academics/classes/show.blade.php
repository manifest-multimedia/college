<x-dashboard.default>
    <x-slot name="title">
        Class Details
    </x-slot>
    
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title">
                                <i class="fas fa-chalkboard me-2"></i>Class Details
                            </h5>
                            <div>
                                <a href="{{ route('academics.classes.index') }}" class="btn btn-sm btn-secondary me-2">
                                    <i class="fas fa-arrow-left me-1"></i> Back to Classes
                                </a>
                                <a href="{{ route('academics.classes.edit', $class) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit me-1"></i> Edit
                                </a>
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

                        <div class="row mb-4">
                            <div class="col-md-8">
                                <div class="card">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="card-title mb-0">Class Information</h5>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-striped">
                                            <tbody>
                                                <tr>
                                                    <th style="width: 30%">Class Name</th>
                                                    <td>{{ $class->name }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Description</th>
                                                    <td>{{ $class->description ?: 'No description provided' }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Semester</th>
                                                    <td>
                                                        @if($class->semester)
                                                            <a href="{{ route('academics.semesters.show', $class->semester) }}">
                                                                {{ $class->semester->name }}
                                                            </a>
                                                            ({{ optional($class->semester->academicYear)->name ?? 'No Academic Year' }})
                                                        @else
                                                            <span class="text-muted">No semester assigned</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Course</th>
                                                    <td>
                                                        {{ optional($class->course)->name ?? 'No course assigned' }}
                                                        @if($class->course && $class->course->course_code)
                                                            ({{ $class->course->course_code }})
                                                        @endif
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Instructor</th>
                                                    <td>{{ optional($class->instructor)->name ?? 'No instructor assigned' }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header bg-info text-white">
                                        <h5 class="card-title mb-0">Quick Stats</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3 text-center">
                                            <h5>Enrolled Students</h5>
                                            <h1 class="display-5">{{ $class->students->count() }}</h1>
                                        </div>
                                        
                                        <div class="text-center">
                                            <h5>Graded Students</h5>
                                            <h1 class="display-5">{{ $studentGrades->count() }}</h1>
                                        </div>
                                    </div>
                                    <div class="card-footer">
                                        <div class="d-grid gap-2">
                                            <a href="{{ route('academics.classes.batch-grades', $class) }}" class="btn btn-primary">
                                                <i class="fas fa-star me-1"></i> Batch Assign Grades
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Student Roster -->
                        <div class="card mt-4">
                            <div class="card-header bg-secondary text-white">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-user-graduate me-1"></i> Enrolled Students
                                    </h5>
                                    <button type="button" class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#addStudentsModal">
                                        <i class="fas fa-user-plus me-1"></i> Add Students
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                @if($class->students->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Student ID</th>
                                                    <th>Name</th>
                                                    <th>Email</th>
                                                    <th>Current Grade</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($class->students as $student)
                                                    <tr>
                                                        <td>{{ $student->student_id ?? 'No ID' }}</td>
                                                        <td>{{ $student->name }}</td>
                                                        <td>{{ $student->email }}</td>
                                                        <td>
                                                            @php
                                                                $grade = $studentGrades->where('student_id', $student->id)->first();
                                                            @endphp
                                                            @if($grade)
                                                                <span class="badge bg-success">{{ $grade->grade->name ?? 'Unknown Grade' }}</span>
                                                            @else
                                                                <span class="badge bg-warning">Not Graded</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <div class="btn-group btn-group-sm" role="group">
                                                                <!-- Assign Grade Button -->
                                                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" 
                                                                        data-bs-target="#gradeModal{{ $student->id }}">
                                                                    <i class="fas fa-star"></i> Grade
                                                                </button>
                                                                
                                                                <!-- Remove from Class Button -->
                                                                <button type="button" class="btn btn-danger" data-bs-toggle="modal"
                                                                        data-bs-target="#removeStudentModal{{ $student->id }}">
                                                                    <i class="fas fa-user-minus"></i>
                                                                </button>
                                                            </div>
                                                            
                                                            <!-- Grade Modal -->
                                                            <div class="modal fade" id="gradeModal{{ $student->id }}" tabindex="-1" 
                                                                 aria-labelledby="gradeModalLabel{{ $student->id }}" aria-hidden="true">
                                                                <div class="modal-dialog">
                                                                    <div class="modal-content">
                                                                        <div class="modal-header bg-primary text-white">
                                                                            <h5 class="modal-title" id="gradeModalLabel{{ $student->id }}">
                                                                                Assign Grade for {{ $student->name }}
                                                                            </h5>
                                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                        </div>
                                                                        <form action="{{ route('academics.student-grades.store') }}" method="POST">
                                                                            @csrf
                                                                            <div class="modal-body">
                                                                                <input type="hidden" name="student_id" value="{{ $student->id }}">
                                                                                <input type="hidden" name="college_class_id" value="{{ $class->id }}">
                                                                                
                                                                                <div class="mb-3">
                                                                                    <label for="grade_id{{ $student->id }}" class="form-label">Grade</label>
                                                                                    <select class="form-select" id="grade_id{{ $student->id }}" name="grade_id" required>
                                                                                        <option value="">Select Grade</option>
                                                                                        @foreach(\App\Models\Grade::all() as $grade)
                                                                                            <option value="{{ $grade->id }}">{{ $grade->name }} ({{ $grade->description }})</option>
                                                                                        @endforeach
                                                                                    </select>
                                                                                </div>
                                                                                
                                                                                <div class="mb-3">
                                                                                    <label for="comments{{ $student->id }}" class="form-label">Comments (Optional)</label>
                                                                                    <textarea class="form-control" id="comments{{ $student->id }}" name="comments" rows="3"></textarea>
                                                                                </div>
                                                                            </div>
                                                                            <div class="modal-footer">
                                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                                <button type="submit" class="btn btn-primary">Assign Grade</button>
                                                                            </div>
                                                                        </form>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            
                                                            <!-- Remove Student Modal -->
                                                            <div class="modal fade" id="removeStudentModal{{ $student->id }}" tabindex="-1"
                                                                 aria-labelledby="removeStudentModalLabel{{ $student->id }}" aria-hidden="true">
                                                                <div class="modal-dialog">
                                                                    <div class="modal-content">
                                                                        <div class="modal-header bg-danger text-white">
                                                                            <h5 class="modal-title" id="removeStudentModalLabel{{ $student->id }}">
                                                                                Remove Student
                                                                            </h5>
                                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                        </div>
                                                                        <div class="modal-body">
                                                                            Are you sure you want to remove <strong>{{ $student->name }}</strong> from this class?
                                                                        </div>
                                                                        <div class="modal-footer">
                                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                            <form action="{{ route('academics.classes.students.remove', [$class->id, $student->id]) }}" method="POST">
                                                                                @csrf
                                                                                @method('DELETE')
                                                                                <button type="submit" class="btn btn-danger">Remove</button>
                                                                            </form>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="alert alert-info">
                                        No students are currently enrolled in this class. Click "Add Students" to enroll students.
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Add Students Modal -->
                        <div class="modal fade" id="addStudentsModal" tabindex="-1" aria-labelledby="addStudentsModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title" id="addStudentsModalLabel">Add Students to Class</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form action="{{ route('academics.classes.students.add', $class->id) }}" method="POST">
                                        @csrf
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label">Select Students</label>
                                                <div class="alert alert-info">
                                                    Select students to add to this class. Hold Ctrl or ⌘ to select multiple students.
                                                </div>
                                                <select class="form-select" name="student_ids[]" multiple size="10" required>
                                                    @foreach(\App\Models\Student::where('status', 'active')->get() as $student)
                                                        @if(!$class->students->contains($student->id))
                                                            <option value="{{ $student->id }}">
                                                                {{ $student->name }} ({{ $student->student_id ?? 'No ID' }}) - {{ $student->email }}
                                                            </option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary">Add Selected Students</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-dashboard.default>