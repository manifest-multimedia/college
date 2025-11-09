<x-dashboard.default>
    <x-slot name="title">
        Academic Programs
    </x-slot>
    
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title">
                                <i class="fas fa-graduation-cap me-2"></i>Academic Programs
                            </h5>
                            <div>
                                <a href="{{ route('academics.dashboard') }}" class="btn btn-sm btn-secondary me-2">
                                    <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
                                </a>
                                <a href="{{ route('academics.classes.create') }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-plus me-1"></i> Add New Program
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
                        
                        <!-- Program info -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Academic Programs</strong> are semester-independent educational offerings that define structured pathways for student learning. Programs can run across multiple academic periods and contain various courses taught by different instructors.
                                </div>
                            </div>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Program Name</th>
                                        <th>Short Name</th>
                                        <th>Description</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($classes as $class)
                                        <tr>
                                            <td>
                                                <strong>{{ $class->name }}</strong>
                                                @if($class->slug)
                                                    <br><small class="text-muted">{{ $class->slug }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-primary">{{ $class->getProgramCode() }}</span>
                                                @if(empty($class->short_name))
                                                    <br><small class="text-muted">Auto-generated</small>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $class->description ? Str::limit($class->description, 80) : 'No description provided' }}
                                            </td>
                                            <td>
                                                @if($class->is_active)
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-secondary">Inactive</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="{{ route('academics.classes.show', $class) }}" class="btn btn-info" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('academics.classes.edit', $class) }}" class="btn btn-warning" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-danger" title="Delete" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#deleteModal{{ $class->id }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                                
                                                <!-- Delete Modal -->
                                                <div class="modal fade" id="deleteModal{{ $class->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $class->id }}" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header bg-danger text-white">
                                                                <h5 class="modal-title" id="deleteModalLabel{{ $class->id }}">Confirm Delete</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                Are you sure you want to delete the class <strong>{{ $class->name }}</strong>?
                                                                <p class="mt-3">This action cannot be undone. All related data might be affected.</p>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                <form action="{{ route('academics.classes.destroy', $class) }}" method="POST">
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
                                            <td colspan="5" class="text-center">
                                                <div class="py-4">
                                                    <i class="fas fa-graduation-cap fa-3x text-muted mb-3"></i>
                                                    <h5 class="text-muted">No Programs Found</h5>
                                                    <p class="text-muted">Create your first academic program to get started.</p>
                                                    <a href="{{ route('academics.classes.create') }}" class="btn btn-primary">
                                                        <i class="fas fa-plus me-1"></i> Create Program
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="mt-4">
                            {{ $classes->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-dashboard.default>