<div>
    @if(session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="d-flex flex-column flex-md-row justify-content-between mb-4">
        <div class="d-flex flex-column flex-md-row gap-3 mb-3 mb-md-0">
            <div class="d-flex position-relative me-md-2">
                <span class="position-absolute top-50 translate-middle-y ms-3">
                    <i class="fas fa-search text-muted"></i>
                </span>
                <input type="text" class="form-control ps-8" wire:model.live="search" placeholder="Search users...">
            </div>
            <div>
                <select class="form-select" wire:model.live="departmentFilter">
                    <option value="">All Departments</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <h3 class="card-label">User Department Assignments</h3>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-row-bordered table-hover">
                    <thead class="table-light">
                        <tr class="fw-bold fs-6 text-gray-800">
                            <th wire:click="sortBy('name')" style="cursor: pointer;" class="min-w-125px">
                                Name
                                @if($sortField === 'name')
                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                @endif
                            </th>
                            <th wire:click="sortBy('email')" style="cursor: pointer;" class="min-w-125px">
                                Email
                                @if($sortField === 'email')
                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                @endif
                            </th>
                            <th class="min-w-200px">Departments</th>
                            <th class="min-w-125px">Department Head</th>
                            <th class="text-end min-w-100px">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td class="align-middle">
                                    <div class="d-flex align-items-center">
                                        <div class="symbol symbol-circle symbol-40px overflow-hidden me-3">
                                            <div class="symbol-label bg-light-primary text-primary">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </div>
                                        </div>
                                        <div>
                                            <span class="fw-bold d-block">{{ $user->name }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="align-middle">{{ $user->email }}</td>
                                <td class="align-middle">
                                    @forelse($user->departments as $department)
                                        <span class="badge badge-light-primary me-1">{{ $department->name }}</span>
                                    @empty
                                        <span class="text-muted">No departments assigned</span>
                                    @endforelse
                                </td>
                                <td class="align-middle">
                                    @foreach($user->departmentHeadOf as $department)
                                        <span class="badge badge-light-success me-1">{{ $department->name }}</span>
                                    @endforeach
                                </td>
                                <td class="align-middle text-end">
                                    <button type="button" class="btn btn-sm btn-primary" 
                                        wire:click="assignDepartments({{ $user->id }})">
                                        <i class="fas fa-building me-1"></i> Assign
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">No users found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-4">
                {{ $users->links() }}
            </div>
        </div>
    </div>

    <!-- Department Assignment Modal -->
    <div class="modal fade" id="departmentAssignmentModal" tabindex="-1" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Assign Departments to {{ $userName ?? '' }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="departmentAssignmentForm">
                        <!-- Department Selection -->
                        <div class="mb-4">
                            <label class="form-label fw-bold d-block">Select Departments</label>
                            <div class="row">
                                @foreach($departments as $department)
                                    <div class="col-md-6 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" 
                                                wire:model="selectedDepartments" value="{{ $department->id }}" 
                                                id="dept_{{ $department->id }}">
                                            <label class="form-check-label" for="dept_{{ $department->id }}">
                                                {{ $department->name }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Department Head Selection -->
                        <div class="mb-4">
                            <label class="form-label fw-bold d-block">Assign as Department Head</label>
                            <div class="row">
                                @foreach($departments as $department)
                                    <div class="col-md-6 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" 
                                                wire:model="departmentHeadRoles" value="{{ $department->id }}" 
                                                id="head_{{ $department->id }}"
                                                @if(!in_array($department->id, $selectedDepartments)) disabled @endif>
                                            <label class="form-check-label" for="head_{{ $department->id }}">
                                                {{ $department->name }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="text-muted small mt-2">
                                <i class="fas fa-info-circle me-1"></i> 
                                User must be assigned to a department before they can be made department head.
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" wire:click="saveAssignments">
                        <span wire:loading.remove wire:target="saveAssignments">Save</span>
                        <span wire:loading wire:target="saveAssignments">Saving...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Bootstrap modal
            const departmentAssignmentModalEl = document.getElementById('departmentAssignmentModal');
            const departmentAssignmentModal = new bootstrap.Modal(departmentAssignmentModalEl);
            
            // Wait for Livewire to be fully initialized
            document.addEventListener('livewire:initialized', () => {
                // Handle userDepartmentDataLoaded event
                Livewire.on('userDepartmentDataLoaded', () => {
                    console.log('User department data loaded event received');
                    setTimeout(() => {
                        departmentAssignmentModal.show();
                    }, 100);
                });
                
                // Handle modal state changes
                Livewire.on('modalStateChanged', (state) => {
                    if (state.isOpen && !departmentAssignmentModalEl.classList.contains('show')) {
                        setTimeout(() => departmentAssignmentModal.show(), 100);
                    } else if (!state.isOpen) {
                        departmentAssignmentModal.hide();
                    }
                });
                
                // Close modal event handler
                Livewire.on('closeModal', () => {
                    departmentAssignmentModal.hide();
                });
                
                // Modal hidden event - reset component state
                departmentAssignmentModalEl.addEventListener('hidden.bs.modal', () => {
                    Livewire.dispatch('closeModalAction');
                });
            });
            
            // Conditionally enable/disable department head checkboxes based on department selection
            document.addEventListener('livewire:init', () => {
                Livewire.hook('element.updated', ({ el, component, name, value }) => {
                    if (name === 'selectedDepartments') {
                        const headCheckboxes = document.querySelectorAll('[id^="head_"]');
                        headCheckboxes.forEach(checkbox => {
                            const deptId = checkbox.value;
                            checkbox.disabled = !value.includes(deptId);
                            if (checkbox.disabled && checkbox.checked) {
                                checkbox.checked = false;
                                // Manually trigger a change event to update Livewire state
                                checkbox.dispatchEvent(new Event('change', { bubbles: true }));
                            }
                        });
                    }
                });
            });
        });
    </script>
    @endpush
</div>
