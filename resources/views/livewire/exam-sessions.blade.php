{{-- 
    DEPRECATED: This view is deprecated. 
    Individual exam sessions are now handled by the Exam Audit Tool.
    This originally displayed a list of exams, not actual student exam sessions.
--}}
<div>
    <div class="alert alert-warning mb-4">
        <i class="bi bi-exclamation-triangle me-2"></i>
        <strong>Deprecated:</strong> This page is deprecated. For student exam session tracking, please use the <strong>Exam Audit Tool</strong>.
    </div>
    
    <div class="card shadow-sm mb-4">
        <div class="card-header py-3">
            <div class="card-title">
                <h3 class="card-title">
                    <i class="bi bi-calendar-check me-2"></i>
                    Exam Sessions (Deprecated)
                </h3>
            </div>
        </div>
        
        <div class="card-body">
            <!-- Search and filters row -->
            <div class="row mb-4">
                <div class="col-md-6 mb-3 mb-md-0">
                    <div class="input-group">
                        <span class="input-group-text bg-light">
                            <i class="bi bi-search"></i>
                        </span>
                        <input 
                            wire:model.live.debounce.300ms="search" 
                            type="text" 
                            class="form-control" 
                            placeholder="Search exam by course name..."
                        >
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="d-flex justify-content-md-end">
                        <select wire:model.live="perPage" class="form-select w-auto">
                            <option value="10">10 per page</option>
                            <option value="25">25 per page</option>
                            <option value="50">50 per page</option>
                            <option value="100">100 per page</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Exams table -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th wire:click="sortBy('title')" class="cursor-pointer">
                                Title 
                                @if($sortField === 'title')
                                    <i class="bi bi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                @endif
                            </th>
                            <th>Course</th>
                            <th wire:click="sortBy('date')" class="cursor-pointer">
                                Date 
                                @if($sortField === 'date')
                                    <i class="bi bi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                @endif
                            </th>
                            <th>Duration</th>
                            <th wire:click="sortBy('status')" class="cursor-pointer">
                                Status 
                                @if($sortField === 'status')
                                    <i class="bi bi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                @endif
                            </th>
                            <th>Active Sessions</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($examSessions as $exam)
                            <tr>
                                <td class="fw-medium">{{ $exam->title }}</td>
                                <td>{{ $exam->course->name }}</td>
                                <td>{{ $exam->date ? date('M d, Y', strtotime($exam->date)) : 'Not scheduled' }}</td>
                                <td>{{ $exam->duration }} minutes</td>
                                <td>
                                    @if($exam->status === 'active')
                                        <span class="badge bg-success">Active</span>
                                    @elseif($exam->status === 'pending')
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    @elseif($exam->status === 'completed')
                                        <span class="badge bg-secondary">Completed</span>
                                    @elseif($exam->status === 'canceled')
                                        <span class="badge bg-danger">Canceled</span>
                                    @else
                                        <span class="badge bg-info">{{ $exam->status }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-primary">{{ $exam->sessions()->where('completed_at', null)->count() }}</span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('exams.show', $exam->slug ?? $exam->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> View Sessions
                                        </a>
                                        <a href="{{ route('exams.edit', $exam->slug ?? $exam->id) }}" class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="d-flex flex-column align-items-center">
                                        <div class="mb-3">
                                            <i class="bi bi-calendar-x fs-1 text-muted"></i>
                                        </div>
                                        <h5 class="text-muted mb-3">No exam sessions found</h5>
                                        @if($search)
                                            <p class="text-muted">Try adjusting your search criteria</p>
                                            <button wire:click="$set('search', '')" class="btn btn-outline-secondary btn-sm mt-2">
                                                <i class="bi bi-x me-1"></i> Clear search
                                            </button>
                                        @else
                                            <p class="text-muted">No exam sessions have been created yet</p>
                                            <a href="{{ route('admin.exams.create') }}" class="btn btn-primary btn-sm mt-2">
                                                <i class="bi bi-plus me-1"></i> Create Exam
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="mt-4">
                {{ $examSessions->links() }}
            </div>
        </div>
    </div>

    <style>
        .cursor-pointer {
            cursor: pointer;
        }
        
        .table th {
            font-weight: 600;
        }
    </style>
</div>