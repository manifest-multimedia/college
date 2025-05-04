<x-dashboard.default>
    <x-slot name="title">
        Batch Assign Grades
    </x-slot>
    
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title">
                                <i class="fas fa-star-half-alt me-2"></i>Batch Assign Grades
                            </h5>
                            <a href="{{ route('academics.classes.show', $class) }}" class="btn btn-sm btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Back to Class
                            </a>
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
                        
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white">
                                <h5 class="card-title mb-0">Class Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Class Name:</strong> {{ $class->name }}</p>
                                        <p><strong>Course:</strong> {{ optional($class->course)->name ?? 'No course assigned' }}</p>
                                        <p><strong>Instructor:</strong> {{ optional($class->instructor)->name ?? 'No instructor assigned' }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p>
                                            <strong>Semester:</strong> 
                                            @if($class->semester)
                                                {{ $class->semester->name }}
                                                ({{ optional($class->semester->academicYear)->name ?? 'No Academic Year' }})
                                            @else
                                                <span class="text-muted">No semester assigned</span>
                                            @endif
                                        </p>
                                        <p><strong>Total Students:</strong> {{ $class->students->count() }}</p>
                                        <p><strong>Students Graded:</strong> {{ $studentGrades->count() }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i> Use this form to assign grades to multiple students at once. You can set different grades for each student.
                        </div>
                        
                        @if($class->students->count() > 0)
                            <form action="{{ route('academics.classes.batch-grades.store', $class) }}" method="POST">
                                @csrf
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Student</th>
                                                <th>Current Grade</th>
                                                <th>New Grade</th>
                                                <th>Comments</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($class->students as $student)
                                                @php
                                                    $currentGrade = $studentGrades->where('student_id', $student->id)->first();
                                                @endphp
                                                <tr>
                                                    <td>
                                                        {{ $student->name }}
                                                        <br>
                                                        <small class="text-muted">{{ $student->student_id ?? 'No ID' }}</small>
                                                    </td>
                                                    <td>
                                                        @if($currentGrade)
                                                            <span class="badge bg-success">{{ optional($currentGrade->grade)->name ?? 'Unknown' }}</span>
                                                            @if($currentGrade->comments)
                                                                <button type="button" class="btn btn-sm btn-link p-0" 
                                                                        data-bs-toggle="tooltip" 
                                                                        title="{{ $currentGrade->comments }}">
                                                                    <i class="fas fa-comment-dots"></i>
                                                                </button>
                                                            @endif
                                                        @else
                                                            <span class="badge bg-warning">Not Graded</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <input type="hidden" name="student_ids[]" value="{{ $student->id }}">
                                                        <select class="form-select form-select-sm" name="grade_ids[]">
                                                            <option value="">No Change</option>
                                                            @foreach($grades as $grade)
                                                                <option value="{{ $grade->id }}" {{ (old('grade_ids.' . $loop->parent->index) == $grade->id) ? 'selected' : '' }}>
                                                                    {{ $grade->name }} ({{ $grade->description }})
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <textarea class="form-control form-control-sm" name="comments[]" rows="1" 
                                                                  placeholder="Optional comments">{{ old('comments.' . $loop->index) }}</textarea>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                
                                <div class="d-flex justify-content-end mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i> Save All Grades
                                    </button>
                                </div>
                            </form>
                        @else
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-circle me-2"></i> There are no students enrolled in this class. Please add students before assigning grades.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Bootstrap tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
    @endpush
</x-dashboard.default>