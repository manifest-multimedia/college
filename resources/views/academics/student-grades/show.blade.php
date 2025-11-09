<x-dashboard.default>
    <x-slot name="title">
        Student Grade Details
    </x-slot>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h1 class="card-title">
                                <i class="fas fa-graduation-cap me-2"></i>Student Grade Details
                            </h1>
                            <div>
                                <a href="{{ route('academics.student-grades.edit', $studentGrade) }}" class="btn btn-primary me-2">
                                    <i class="fas fa-edit me-1"></i> Edit Grade
                                </a>
                                <a href="{{ route('academics.student-grades.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-1"></i> Back to Grades
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

                        <div class="row">
                            <div class="col-md-6">
                                <h4>Student Information</h4>
                                <table class="table table-bordered">
                                    <tr>
                                        <th width="30%">Student ID</th>
                                        <td>{{ $studentGrade->student->student_id }}</td>
                                    </tr>
                                    <tr>
                                        <th>Name</th>
                                        <td>{{ $studentGrade->student->first_name }} {{ $studentGrade->student->last_name }}</td>
                                    </tr>
                                    <tr>
                                        <th>Program</th>
                                        <td>{{ $studentGrade->student && $studentGrade->student->program ? $studentGrade->student->program->name : 'N/A' }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h4>Program Information</h4>
                                <table class="table table-bordered">
                                    <tr>
                                        <th width="30%">Program</th>
                                        <td>{{ $studentGrade->collegeClass ? $studentGrade->collegeClass->name : 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Program Code</th>
                                        <td>{{ $studentGrade->collegeClass ? $studentGrade->collegeClass->getProgramCode() : 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Short Name</th>
                                        <td>{{ $studentGrade->collegeClass && $studentGrade->collegeClass->short_name ? $studentGrade->collegeClass->short_name : 'N/A' }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-6">
                                <h4>Grade Information</h4>
                                <table class="table table-bordered">
                                    <tr>
                                        <th width="30%">Letter Grade</th>
                                        <td>
                                            @if($studentGrade->grade)
                                                <span class="badge bg-info fs-6">{{ $studentGrade->grade->letter }}</span>
                                            @else
                                                <span class="badge bg-secondary fs-6">N/A</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Grade Value</th>
                                        <td>{{ $studentGrade->grade ? $studentGrade->grade->value : 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Comments</th>
                                        <td>{{ $studentGrade->comments ?: 'No comments' }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h4>Grading Information</h4>
                                <table class="table table-bordered">
                                    <tr>
                                        <th width="30%">Graded By</th>
                                        <td>{{ $studentGrade->gradedBy?->name ?? 'System' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Graded On</th>
                                        <td>{{ $studentGrade->updated_at->format('M d, Y h:i A') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Created On</th>
                                        <td>{{ $studentGrade->created_at->format('M d, Y h:i A') }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="button" class="btn btn-danger" 
                                    onclick="event.preventDefault(); 
                                             if(confirm('Are you sure you want to delete this grade record? This action cannot be undone.')) { 
                                                document.getElementById('delete-form').submit(); 
                                             }">
                                <i class="fas fa-trash me-1"></i> Delete Grade
                            </button>
                            <form id="delete-form" action="{{ route('academics.student-grades.destroy', $studentGrade) }}" method="POST" style="display: none;">
                                @csrf
                                @method('DELETE')
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-dashboard.default>