<div>
    <div class="d-flex justify-content-between mb-4">
        <div class="input-group" style="width: 300px;">
            <span class="input-group-text">
                <i class="bi bi-search"></i>
            </span>
            <input type="text" class="form-control" wire:model.debounce.300ms="search" placeholder="Search exam sessions...">
        </div>
        
        <a href="{{ route('exams.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>Create New Session
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-row-bordered table-hover">
            <thead>
                <tr class="fw-bold fs-6 text-gray-800">
                    <th wire:click="sortBy('title')" class="cursor-pointer">
                        Title
                        @if ($sortField === 'title')
                            <i class="bi bi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                        @endif
                    </th>
                    <th wire:click="sortBy('starts_at')" class="cursor-pointer">
                        Start Time
                        @if ($sortField === 'starts_at')
                            <i class="bi bi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                        @endif
                    </th>
                    <th wire:click="sortBy('duration')" class="cursor-pointer">
                        Duration
                        @if ($sortField === 'duration')
                            <i class="bi bi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                        @endif
                    </th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($examSessions as $exam)
                    <tr>
                        <td>{{ $exam->title }}</td>
                        <td>{{ $exam->starts_at ? $exam->starts_at->format('M d, Y h:i A') : 'Not scheduled' }}</td>
                        <td>{{ $exam->duration ?? 'N/A' }} minutes</td>
                        <td>
                            @if($exam->status == 'active')
                                <span class="badge badge-light-success">Active</span>
                            @elseif($exam->status == 'pending')
                                <span class="badge badge-light-warning">Pending</span>
                            @elseif($exam->status == 'completed')
                                <span class="badge badge-light-info">Completed</span>
                            @else
                                <span class="badge badge-light-dark">{{ $exam->status }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="{{ route('exams.edit', $exam->slug) }}" class="btn btn-sm btn-icon btn-light-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="#" class="btn btn-sm btn-icon btn-light-info" title="View Results">
                                    <i class="bi bi-bar-chart"></i>
                                </a>
                                <button class="btn btn-sm btn-icon btn-light-danger" title="Delete Session" 
                                        onclick="confirm('Are you sure you want to delete this session?') || event.stopImmediatePropagation()">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4">
                            <div class="text-muted">No exam sessions found</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-4">
        <div>
            <select wire:model="perPage" class="form-select form-select-sm" style="width: 75px;">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
        </div>
        <div>
            {{ $examSessions->links() }}
        </div>
    </div>
</div>