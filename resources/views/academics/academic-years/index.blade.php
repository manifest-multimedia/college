<x-dashboard.default>
    <x-slot name="title">
        Academic Years
    </x-slot>
    
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title">
                                <i class="fas fa-calendar-alt me-2"></i>Academic Years
                            </h5>
                            <div>
                                <a href="{{ route('academics.dashboard') }}" class="btn btn-sm btn-secondary me-2">
                                    <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
                                </a>
                                <a href="{{ route('academics.academic-years.create') }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-plus me-1"></i> Add New Academic Year
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
                                        <th>Year</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($academicYears as $academicYear)
                                        <tr>
                                            <td>{{ $academicYear->name }}</td>
                                            <td>{{ $academicYear->year }}</td>
                                            <td>{{ $academicYear->start_date->format('M d, Y') }}</td>
                                            <td>{{ $academicYear->end_date->format('M d, Y') }}</td>
                                            <td>
                                                @if($academicYear->is_current)
                                                    <span class="badge bg-success">Current</span>
                                                @else
                                                    <span class="badge bg-secondary">Inactive</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="{{ route('academics.academic-years.show', $academicYear) }}" class="btn btn-info" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('academics.academic-years.edit', $academicYear) }}" class="btn btn-warning" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-danger" title="Delete" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#deleteModal{{ $academicYear->id }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                                
                                                <!-- Delete Modal -->
                                                <div class="modal fade" id="deleteModal{{ $academicYear->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $academicYear->id }}" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header bg-danger text-white">
                                                                <h5 class="modal-title" id="deleteModalLabel{{ $academicYear->id }}">Confirm Delete</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                Are you sure you want to delete the academic year <strong>{{ $academicYear->name }}</strong>?
                                                                <p class="mt-3">This action cannot be undone. All related data might be affected.</p>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                <form action="{{ route('academics.academic-years.destroy', $academicYear) }}" method="POST">
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
                                            <td colspan="6" class="text-center">No academic years found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="mt-4">
                            {{ $academicYears->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-dashboard.default>