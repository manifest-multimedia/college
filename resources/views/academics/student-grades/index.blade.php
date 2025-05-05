<x-dashboard.default>
    <x-slot name="title">
        Student Grades Management
    </x-slot>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h1 class="card-title">
                                <i class="fas fa-graduation-cap me-2"></i>Student Grades
                            </h1>
                            <div>
                                <a href="{{ route('academics.student-grades.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus-circle me-1"></i> New Grade Entry
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

                        <!-- Semester filter -->
                        <div class="mb-4">
                            <form action="{{ route('academics.student-grades.filter') }}" method="GET" class="row g-3">
                                <div class="col-md-4">
                                    <label for="semester_id" class="form-label">Filter by Semester</label>
                                    <select name="semester_id" id="semester_id" class="form-select">
                                        @foreach($semesters as $semester)
                                            <option value="{{ $semester->id }}" 
                                                {{ $currentSemester && $currentSemester->id == $semester->id ? 'selected' : '' }}>
                                                {{ $semester->name }} ({{ $semester->academicYear->name }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary mb-3">
                                        <i class="fas fa-filter me-1"></i> Filter
                                    </button>
                                </div>
                            </form>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Course</th>
                                        <th>Grade</th>
                                        <th>Comments</th>
                                        <th>Graded By</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($grades as $grade)
                                        <tr>
                                            <td>
                                                {{ $grade->student->first_name }} {{ $grade->student->last_name }}<br>
                                                <small class="text-muted">{{ $grade->student->student_id }}</small>
                                            </td>
                                            <td>
                                                {{ $grade->collegeClass->course->title }}<br>
                                                <small class="text-muted">{{ $grade->collegeClass->name }}</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ $grade->grade->letter }}</span>
                                                <small>({{ $grade->grade->value }})</small>
                                            </td>
                                            <td>{{ Str::limit($grade->comments, 30) ?: 'N/A' }}</td>
                                            <td>{{ $grade->gradedBy->name ?? 'System' }}</td>
                                            <td>{{ $grade->updated_at->format('M d, Y') }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('academics.student-grades.show', $grade) }}" class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('academics.student-grades.edit', $grade) }}" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-danger" 
                                                            onclick="if(confirm('Are you sure you want to delete this grade?')) { 
                                                                document.getElementById('delete-grade-{{ $grade->id }}').submit(); 
                                                            }">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                    <form id="delete-grade-{{ $grade->id }}" 
                                                          action="{{ route('academics.student-grades.destroy', $grade) }}" 
                                                          method="POST" 
                                                          style="display: none;">
                                                        @csrf
                                                        @method('DELETE')
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center">
                                                No grades found for the selected semester.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-center mt-4">
                            {{ $grades->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-dashboard.default>