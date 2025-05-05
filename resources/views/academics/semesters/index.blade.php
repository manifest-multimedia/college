<x-dashboard.default>
    <x-slot name="title">
        Semesters
    </x-slot>
    
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title">
                                <i class="fas fa-clock me-2"></i>Semesters
                            </h5>
                            <div>
                                <a href="{{ route('academics.dashboard') }}" class="btn btn-sm btn-secondary me-2">
                                    <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
                                </a>
                                <a href="{{ route('academics.semesters.create') }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-plus me-1"></i> Add New Semester
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
                        
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Academic Year</th>
                                        <th>Duration</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($semesters as $semester)
                                        <tr>
                                            <td>{{ $semester->name }}</td>
                                            <td>
                                                @if($semester->academicYear)
                                                    <a href="{{ route('academics.academic-years.show', $semester->academicYear) }}">
                                                        {{ $semester->academicYear->name }}
                                                    </a>
                                                @else
                                                    <span class="text-muted">No academic year assigned</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($semester->start_date && $semester->end_date)
                                                    {{ $semester->start_date->format('M d, Y') }} - {{ $semester->end_date->format('M d, Y') }}
                                                @else
                                                    <span class="text-muted">No dates set</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($semester->is_current)
                                                    <span class="badge bg-success">Current</span>
                                                @else
                                                    <span class="badge bg-secondary">Inactive</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="{{ route('academics.semesters.show', $semester) }}" class="btn btn-info" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('academics.semesters.edit', $semester) }}" class="btn btn-warning" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <!-- Toggle Active Status Button -->
                                                    <form action="{{ route('academics.semesters.toggle-active', $semester) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="btn {{ $semester->is_current ? 'btn-secondary' : 'btn-success' }}" 
                                                                title="{{ $semester->is_current ? 'Deactivate' : 'Set as Current' }}">
                                                            <i class="fas {{ $semester->is_current ? 'fa-toggle-off' : 'fa-toggle-on' }}"></i>
                                                        </button>
                                                    </form>
                                                    <button type="button" class="btn btn-danger" title="Delete" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#deleteModal{{ $semester->id }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                                
                                                <!-- Delete Modal -->
                                                <div class="modal fade" id="deleteModal{{ $semester->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $semester->id }}" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header bg-danger text-white">
                                                                <h5 class="modal-title" id="deleteModalLabel{{ $semester->id }}">Confirm Delete</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                Are you sure you want to delete the semester <strong>{{ $semester->name }}</strong>?
                                                                <p class="mt-3">This action cannot be undone. All related data might be affected.</p>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                <form action="{{ route('academics.semesters.destroy', $semester) }}" method="POST">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="btn btn-danger">Delete</button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center">No semesters found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="mt-4">
                            {{ $semesters->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-dashboard.default>