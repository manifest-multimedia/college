<x-dashboard.default>
    <x-slot name="title">
        Cohort Details
    </x-slot>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h1 class="card-title">
                                <i class="fas fa-users me-2"></i>Cohort: {{ $cohort->name }}
                            </h1>
                            <div>
                                <a href="{{ route('academics.cohorts.edit', $cohort) }}" class="btn btn-primary me-2">
                                    <i class="fas fa-edit me-1"></i> Edit
                                </a>
                                <a href="{{ route('academics.cohorts.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-1"></i> Back to Cohorts
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
                                <table class="table table-striped">
                                    <tbody>
                                        <tr>
                                            <th style="width: 30%;">Name</th>
                                            <td>{{ $cohort->name }}</td>
                                        </tr>
                                        <tr>
                                            <th>Start Date</th>
                                            <td>{{ $cohort->start_date ? $cohort->start_date->format('F j, Y') : 'Not specified' }}</td>
                                        </tr>
                                        <tr>
                                            <th>End Date</th>
                                            <td>{{ $cohort->end_date ? $cohort->end_date->format('F j, Y') : 'Not specified' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Status</th>
                                            <td>
                                                @if($cohort->is_active)
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-secondary">Inactive</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Description</th>
                                            <td>{{ $cohort->description ?? 'No description available' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Created At</th>
                                            <td>{{ $cohort->created_at->format('F j, Y, g:i a') }}</td>
                                        </tr>
                                        <tr>
                                            <th>Last Updated</th>
                                            <td>{{ $cohort->updated_at->format('F j, Y, g:i a') }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="mt-4">
                            <h3>Students in this Cohort</h3>
                            
                            @if($cohort->students->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>Student ID</th>
                                                <th>Name</th>
                                                <th>Program</th>
                                                <th>Class</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($cohort->students as $student)
                                                <tr>
                                                    <td>{{ $student->student_id }}</td>
                                                    <td>{{ $student->first_name }} {{ $student->last_name }}</td>
                                                    <td>{{ $student->program->name ?? 'N/A' }}</td>
                                                    <td>{{ $student->collegeClass->name ?? 'N/A' }}</td>
                                                    <td>
                                                        @if($student->is_active)
                                                            <span class="badge bg-success">Active</span>
                                                        @else
                                                            <span class="badge bg-secondary">Inactive</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="alert alert-info">
                                    No students are currently assigned to this cohort.
                                </div>
                            @endif
                            
                            <button type="button" class="btn btn-danger mt-3" 
                                    onclick="event.preventDefault(); 
                                             if(confirm('Are you sure you want to delete this cohort? This action cannot be undone.')) { 
                                                document.getElementById('delete-form').submit(); 
                                             }">
                                <i class="fas fa-trash me-1"></i> Delete Cohort
                            </button>
                            <form id="delete-form" action="{{ route('academics.cohorts.destroy', $cohort) }}" method="POST" style="display: none;">
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