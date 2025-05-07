<div>
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <h3 class="card-title">
                    <i class="ki-duotone ki-document fs-1 me-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    Memo Management
                </h3>
            </div>
            <div class="card-toolbar">
                <a href="{{ route('memo.create') }}" class="btn btn-sm btn-primary">
                    <i class="ki-duotone ki-plus fs-2"></i>
                    New Memo
                </a>
            </div>
        </div>
        
        <div class="card-body">
            <!-- Filters Section -->
            <div class="mb-5">
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <div class="me-4">
                        <select wire:model.live="viewType" class="form-select form-select-sm">
                            <option value="all">All Memos</option>
                            <option value="created_by_me">Created by Me</option>
                            <option value="to_me">Sent to Me</option>
                            <option value="to_my_department">Sent to My Department</option>
                            <option value="pending_approval">Pending Approval</option>
                            <option value="needs_action">Needs My Action</option>
                            <option value="recent_activity">Recent Activity</option>
                        </select>
                    </div>

                    <div class="me-4">
                        <select wire:model.live="statusFilter" class="form-select form-select-sm">
                            <option value="">All Statuses</option>
                            <option value="draft">Draft</option>
                            <option value="pending">Pending</option>
                            <option value="forwarded">Forwarded</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>

                    <div class="me-4">
                        <select wire:model.live="priorityFilter" class="form-select form-select-sm">
                            <option value="">All Priorities</option>
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>

                    <div>
                        <button class="btn btn-sm btn-light" type="button" data-bs-toggle="collapse" data-bs-target="#advancedFilters" aria-expanded="false" aria-controls="advancedFilters">
                            Advanced Filters
                            <i class="ki-duotone ki-down fs-5 ms-1"></i>
                        </button>
                    </div>

                    <div class="ms-auto">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">
                                <i class="ki-duotone ki-magnifier fs-3"></i>
                            </span>
                            <input type="text" wire:model.live.debounce.300ms="searchTerm" class="form-control" placeholder="Search memos...">
                        </div>
                    </div>
                </div>

                <div class="collapse" id="advancedFilters">
                    <div class="card card-body mb-3">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Date From</label>
                                <input type="date" wire:model.live="dateFrom" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Date To</label>
                                <input type="date" wire:model.live="dateTo" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Department</label>
                                <select wire:model.live="selectedDepartment" class="form-select form-select-sm">
                                    <option value="">All Departments</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">User</label>
                                <select wire:model.live="selectedUser" class="form-select form-select-sm">
                                    <option value="">All Users</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <button wire:click="resetFilters" class="btn btn-sm btn-secondary float-end">
                                    Reset Filters
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Memos List -->
            @if($memos->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Ref Number</th>
                                <th>Title</th>
                                <th>Status</th>
                                <th>Priority</th>
                                <th>From</th>
                                <th>To</th>
                                <th>Date</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($memos as $memo)
                                <tr>
                                    <td>{{ $memo->reference_number }}</td>
                                    <td>{{ $memo->title }}</td>
                                    <td>
                                        @php
                                            $statusClass = [
                                                'draft' => 'badge-light-dark',
                                                'pending' => 'badge-light-warning',
                                                'forwarded' => 'badge-light-info',
                                                'approved' => 'badge-light-success',
                                                'rejected' => 'badge-light-danger',
                                                'completed' => 'badge-light-primary',
                                            ][$memo->status] ?? 'badge-light';
                                        @endphp
                                        <span class="badge {{ $statusClass }}">{{ ucfirst($memo->status) }}</span>
                                    </td>
                                    <td>
                                        @php
                                            $priorityClass = [
                                                'low' => 'badge-light-success',
                                                'medium' => 'badge-light-warning',
                                                'high' => 'badge-light-danger',
                                            ][$memo->priority] ?? 'badge-light';
                                        @endphp
                                        <span class="badge {{ $priorityClass }}">{{ ucfirst($memo->priority) }}</span>
                                    </td>
                                    <td>{{ $memo->user->name ?? 'N/A' }}</td>
                                    <td>
                                        @if($memo->recipient)
                                            {{ $memo->recipient->name }}
                                        @elseif($memo->recipientDepartment)
                                            {{ $memo->recipientDepartment->name }} Dept.
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>{{ $memo->created_at->format('M d, Y') }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('memo.view', $memo->id) }}" class="btn btn-sm btn-icon btn-light-primary me-1" title="View">
                                            <i class="ki-duotone ki-eye fs-2">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                                <span class="path3"></span>
                                            </i>
                                        </a>
                                        
                                        @if($memo->user_id == auth()->id() && $memo->status == 'draft')
                                            <a href="{{ route('memo.edit', $memo->id) }}" class="btn btn-sm btn-icon btn-light-warning me-1" title="Edit">
                                                <i class="ki-duotone ki-pencil fs-2">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="d-flex justify-content-end mt-4">
                    {{ $memos->links() }}
                </div>
            @else
                <div class="text-center py-10">
                    <i class="ki-duotone ki-document fs-3x text-muted mb-3">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    <p class="text-gray-600 fw-semibold fs-5 mb-2">No memos found</p>
                    <p class="text-gray-500">Try adjusting your search or filter to find what you're looking for.</p>
                </div>
            @endif
        </div>
    </div>
</div>
