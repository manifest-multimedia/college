<x-dashboard.default>
    <x-slot name="title">
        Courses Management
    </x-slot>
    
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title">
                                <i class="fas fa-book me-2"></i>Courses
                            </h5>
                            <div>
                                <a href="{{ route('academics.dashboard') }}" class="btn btn-sm btn-secondary me-2">
                                    <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
                                </a>
                                <a href="{{ route('academics.courses.create') }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-plus me-1"></i> Add New Course
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
                        
                        <!-- Search and Filter -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <form action="{{ route('academics.courses.index') }}" method="GET" class="d-flex">
                                    <input type="text" name="search" class="form-control me-2" placeholder="Search courses..." value="{{ request('search') }}">
                                    <button type="submit" class="btn btn-primary">Search</button>
                                </form>
                            </div>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Course Code</th>
                                        <th>Name</th>
                                        <th>Description</th>
                                        <th>Active Classes</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($courses as $course)
                                        <tr>
                                            <td>{{ $course->course_code }}</td>
                                            <td>{{ $course->name }}</td>
                                            <td>{{ \Illuminate\Support\Str::limit($course->description, 50) }}</td>
                                            <td>
                                                @if($course->collegeClasses && $course->collegeClasses->count() > 0)
                                                    <span class="badge bg-info">{{ $course->collegeClasses->count() }}</span>
                                                @else
                                                    <span class="badge bg-secondary">0</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="{{ route('academics.courses.show', $course) }}" class="btn btn-info" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('academics.courses.edit', $course) }}" class="btn btn-warning" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-danger" title="Delete" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#deleteModal{{ $course->id }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                                
                                                <!-- Delete Modal -->
                                                <div class="modal fade" id="deleteModal{{ $course->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $course->id }}" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header bg-danger text-white">
                                                                <h5 class="modal-title" id="deleteModalLabel{{ $course->id }}">Confirm Delete</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                Are you sure you want to delete the course <strong>{{ $course->name }}</strong> ({{ $course->course_code }})?
                                                                <p class="mt-3">This action cannot be undone. All related classes and data might be affected.</p>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                <form action="{{ route('academics.courses.destroy', $course) }}" method="POST">
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
                                            <td colspan="5" class="text-center">No courses found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="mt-4">
                            {{ $courses->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-dashboard.default>