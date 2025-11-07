<div>
    <!-- Search and Filter Section -->
    <div class="row mb-4">
        <div class="col-md-6">
            <input type="text" 
                   wire:model.live.debounce.300ms="search" 
                   class="form-control" 
                   placeholder="Search elections by name or description...">
        </div>
        <div class="col-md-3">
            <select wire:model.live="statusFilter" class="form-select">
                <option value="">All Statuses</option>
                <option value="inactive">Inactive</option>
                <option value="upcoming">Upcoming</option>
                <option value="active">Active</option>
                <option value="completed">Completed</option>
            </select>
        </div>
    </div>

    <!-- Elections Table -->
    <div class="table-responsive">
        <table class="table table-row-bordered table-row-gray-300 align-middle gs-0 gy-4">
            <thead>
                <tr class="fw-bold text-gray-700 bg-light">
                    <th class="min-w-200px">Name</th>
                    <th class="min-w-250px">Description</th>
                    <th class="min-w-120px">Start Time</th>
                    <th class="min-w-120px">End Time</th>
                    <th class="min-w-100px">Status</th>
                    <th class="min-w-100px text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($elections as $election)
                    <tr>
                        <td>
                            <span class="text-gray-800 fw-bold">{{ $election->name }}</span>
                        </td>
                        <td>
                            <span class="text-gray-600">{{ Str::limit($election->description ?? 'N/A', 80) }}</span>
                        </td>
                        <td>
                            <span class="text-gray-600">{{ $election->start_time->format('M d, Y H:i') }}</span>
                        </td>
                        <td>
                            <span class="text-gray-600">{{ $election->end_time->format('M d, Y H:i') }}</span>
                        </td>
                        <td>
                            @php
                                $statusColors = [
                                    'inactive' => 'secondary',
                                    'upcoming' => 'info',
                                    'active' => 'success',
                                    'completed' => 'primary',
                                ];
                                $color = $statusColors[$election->computed_status] ?? 'secondary';
                            @endphp
                            <span class="badge badge-{{ $color }}">
                                {{ ucfirst($election->computed_status) }}
                            </span>
                        </td>
                        <td class="text-end">
                            @if(in_array($election->computed_status, ['completed', 'active']))
                                <a href="{{ route('election.results', $election->id) }}" 
                                   class="btn btn-sm btn-primary">
                                    <i class="ki-duotone ki-chart-pie-3 fs-4">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                    </i>
                                    View Results
                                </a>
                            @else
                                <span class="text-muted small">No results available</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <div class="text-gray-600">
                                <i class="ki-duotone ki-information-5 fs-3x mb-3">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                </i>
                                <p class="mb-0">No elections found.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-between align-items-center mt-4">
        <div class="text-gray-600">
            Showing {{ $elections->firstItem() ?? 0 }} to {{ $elections->lastItem() ?? 0 }} of {{ $elections->total() }} elections
        </div>
        <div>
            {{ $elections->links() }}
        </div>
    </div>
</div>
