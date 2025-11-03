<x-dashboard.default>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Office Management</h4>
            <button wire:click="openCreateModal" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Add New Office
            </button>
        </div>
    </x-slot>

    <div>
        @if (session()->has('message'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('message') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Filters -->
        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Search</label>
                        <input type="text" wire:model.live="search" class="form-control" placeholder="Search offices...">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Department</label>
                        <select wire:model.live="filterDepartment" class="form-select">
                            <option value="">All Departments</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select wire:model.live="filterStatus" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Offices Table -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Department</th>
                                <th>Location</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($offices as $office)
                                <tr>
                                    <td>{{ $office->code }}</td>
                                    <td>{{ $office->name }}</td>
                                    <td>{{ $office->department->name }}</td>
                                    <td>{{ $office->location }}</td>
                                    <td>{{ $office->phone }}</td>
                                    <td>{{ $office->email }}</td>
                                    <td>
                                        <span class="badge {{ $office->is_active ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $office->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button wire:click="openEditModal({{ $office->id }})" 
                                                    class="btn btn-outline-primary" 
                                                    title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button wire:click="toggleStatus({{ $office->id }})" 
                                                    class="btn btn-outline-warning" 
                                                    title="Toggle Status">
                                                <i class="bi bi-toggle-{{ $office->is_active ? 'on' : 'off' }}"></i>
                                            </button>
                                            <button wire:click="delete({{ $office->id }})" 
                                                    class="btn btn-outline-danger" 
                                                    title="Delete"
                                                    onclick="return confirm('Are you sure you want to delete this office?')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">No offices found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $offices->links() }}
                </div>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        @if($showModal)
            <div class="modal fade show" style="display: block;" tabindex="-1" role="dialog">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ $editMode ? 'Edit Office' : 'Create New Office' }}</h5>
                            <button type="button" class="btn-close" wire:click="closeModal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form wire:submit.prevent="save">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Department <span class="text-danger">*</span></label>
                                        <select wire:model="department_id" class="form-select @error('department_id') is-invalid @enderror">
                                            <option value="">Select Department</option>
                                            @foreach($departments as $dept)
                                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('department_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Office Name <span class="text-danger">*</span></label>
                                        <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror">
                                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Office Code</label>
                                        <input type="text" wire:model="code" class="form-control @error('code') is-invalid @enderror">
                                        @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Location</label>
                                        <input type="text" wire:model="location" class="form-control @error('location') is-invalid @enderror">
                                        @error('location') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Phone</label>
                                        <input type="text" wire:model="phone" class="form-control @error('phone') is-invalid @enderror">
                                        @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Email</label>
                                        <input type="email" wire:model="email" class="form-control @error('email') is-invalid @enderror">
                                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">Description</label>
                                        <textarea wire:model="description" class="form-control @error('description') is-invalid @enderror" rows="3"></textarea>
                                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-12">
                                        <div class="form-check">
                                            <input type="checkbox" wire:model="is_active" class="form-check-input" id="is_active">
                                            <label class="form-check-label" for="is_active">Active</label>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="closeModal">Cancel</button>
                            <button type="button" class="btn btn-primary" wire:click="save">
                                {{ $editMode ? 'Update Office' : 'Create Office' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-backdrop fade show"></div>
        @endif
    </div>
</x-dashboard.default>
