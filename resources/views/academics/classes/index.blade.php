<x-dashboard.default>
    <x-slot name="title">
        College Classes
    </x-slot>
    
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title">
                                <i class="fas fa-chalkboard me-2"></i>College Classes
                            </h5>
                            <div>
                                <a href="{{ route('academics.dashboard') }}" class="btn btn-sm btn-secondary me-2">
                                    <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
                                </a>
                                <a href="{{ route('academics.classes.create') }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-plus me-1"></i> Add New Class
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
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body p-3">
                                        <h6 class="mb-3">Filter Classes by Semester</h6>
                                        <form action="{{ route('academics.classes.filter') }}" method="POST" class="d-flex">
                                            @csrf
                                            <select name="semester_id" class="form-select me-2">
                                                <option value="">All Semesters</option>
                                                @foreach(\App\Models\Semester::with('academicYear')->get() as $semester)
                                                    <option value="{{ $semester->id }}" {{ isset($currentSemester) && $currentSemester && $currentSemester->id == $semester->id ? 'selected' : '' }}>
                                                        {{ $semester->name }} ({{ optional($semester->academicYear)->name ?? 'No Academic Year' }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            <button type="submit" class="btn btn-primary">Filter</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            
                            @if(isset($currentSemester) && $currentSemester)
                                <div class="col-md-6">
                                    <div class="alert alert-info mb-0">
                                        <i class="fas fa-filter me-2"></i> 
                                        Showing classes for: <strong>{{ $currentSemester->name }}</strong>
                                        ({{ optional($currentSemester->academicYear)->name ?? 'No Academic Year' }})
                                    </div>
                                </div>
                            @endif
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Class Name</th>
                                        <th>Course</th>
                                        <th>Semester</th>
                                        <th>Instructor</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($classes as $class)
                                        <tr>
                                            <td>{{ $class->name }}</td>
                                            <td>{{ optional($class->course)->name ?? 'No course assigned' }}</td>
                                            <td>
                                                @if($class->semester)
                                                    <a href="{{ route('academics.semesters.show', $class->semester) }}">
                                                        {{ $class->semester->name }}
                                                    </a>
                                                @else
                                                    <span class="text-muted">No semester assigned</span>
                                                @endif
                                            </td>
                                            <td>{{ optional($class->instructor)->name ?? 'No instructor assigned' }}</td>
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
                                            <td colspan="5" class="text-center">No classes found.</td>
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