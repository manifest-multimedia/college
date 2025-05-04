<x-dashboard.default>
    <x-slot name="title">
        Semester Details
    </x-slot>
    
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title">
                                <i class="fas fa-clock me-2"></i>Semester Details
                            </h5>
                            <div>
                                <a href="{{ route('academics.semesters.index') }}" class="btn btn-sm btn-secondary me-2">
                                    <i class="fas fa-arrow-left me-1"></i> Back to Semesters
                                </a>
                                <a href="{{ route('academics.semesters.edit', $semester) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit me-1"></i> Edit
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8 offset-md-2">
                                <table class="table table-striped">
                                    <tbody>
                                        <tr>
                                            <th style="width: 30%">Name</th>
                                            <td>{{ $semester->name }}</td>
                                        </tr>
                                        <tr>
                                            <th>Description</th>
                                            <td>{{ $semester->description ?: 'No description' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Academic Year</th>
                                            <td>
                                                @if($semester->academicYear)
                                                    <a href="{{ route('academics.academic-years.show', $semester->academicYear) }}">
                                                        {{ $semester->academicYear->name }}
                                                    </a>
                                                @else
                                                    <span class="text-muted">No academic year assigned</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Start Date</th>
                                            <td>{{ $semester->start_date ? $semester->start_date->format('F d, Y') : 'Not set' }}</td>
                                        </tr>
                                        <tr>
                                            <th>End Date</th>
                                            <td>{{ $semester->end_date ? $semester->end_date->format('F d, Y') : 'Not set' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Status</th>
                                            <td>
                                                @if($semester->is_current)
                                                    <span class="badge bg-success">Current Semester</span>
                                                @else
                                                    <span class="badge bg-secondary">Inactive</span>
                                                @endif
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                
                                <!-- Classes -->
                                <div class="mt-4">
                                    <h4>College Classes</h4>
                                    <hr>
                                    
                                    @if($semester->collegeClasses && $semester->collegeClasses->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Name</th>
                                                        <th>Course</th>
                                                        <th>Instructor</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($semester->collegeClasses as $class)
                                                        <tr>
                                                            <td>{{ $class->name }}</td>
                                                            <td>{{ optional($class->course)->name ?? 'No course assigned' }}</td>
                                                            <td>{{ optional($class->instructor)->name ?? 'No instructor assigned' }}</td>
                                                            <td>
                                                                <a href="{{ route('academics.classes.show', $class) }}" class="btn btn-sm btn-info">
                                                                    <i class="fas fa-eye"></i> View
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="alert alert-info">
                                            No classes have been created for this semester.
                                        </div>
                                    @endif
                                    
                                    <a href="{{ route('academics.classes.create') }}" class="btn btn-primary mt-2">
                                        <i class="fas fa-plus me-1"></i> Add Class
                                    </a>
                                </div>
                                
                                <!-- Set as Current Semester -->
                                @if(!$semester->is_current)
                                    <div class="mt-4">
                                        <form action="{{ route('academics.settings.update') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="academic_year_id" value="{{ $semester->academic_year_id }}">
                                            <input type="hidden" name="semester_id" value="{{ $semester->id }}">
                                            <button type="submit" class="btn btn-success" onclick="return confirm('Set {{ $semester->name }} as the current semester? This will update system-wide defaults.')">
                                                <i class="fas fa-check-circle me-1"></i> Set as Current Semester
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-dashboard.default>