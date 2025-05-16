<div>    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                        <h1 class="card-title">
                            <i class="fas fa-book me-2"></i> Manage Courses
                        </h1>
                        </div>
                        <div class="d-flex">
                            <div class="input-group me-2">
                                <input wire:model.live.debounce.300ms="search" class="form-control" placeholder="Search courses...">
                                <button class="btn btn-outline-secondary" type="button">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                            <button wire:click="openModal" class="btn btn-primary">
                                <i class="fas fa-plus-circle me-1"></i> Add New Course
                            </button>
                        </div>
                    
                </div>
                <div class="card-body">
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

                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Course Code</th>
                                    <th>Name</th>
                                    <th>Program</th>
                                    <th>Year</th>
                                    <th>Semester</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($subjects as $subject)
                                    <tr>
                                        <td>{{ $subject->course_code }}</td>
                                        <td>{{ $subject->name }}</td>
                                        <td>{{ $subject->collegeClass->name ?? 'N/A' }}</td>
                                        <td>{{ $subject->year->name ?? 'N/A' }}</td>
                                        <td>{{ $subject->semester->name ?? 'N/A' }}</td>
                                        <td>
                                            <button wire:click="edit({{ $subject->id }})" class="btn btn-sm btn-info">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button wire:click="openDeleteModal({{ $subject->id }})" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No courses found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $subjects->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create/Edit Modal -->
    @if($isOpen)
    <div class="modal show" style="display: block; background-color: rgba(0,0,0,0.5);" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $editMode ? 'Edit Course' : 'Add New Course' }}</h5>
                    <button type="button" class="btn-close" wire:click="closeModal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="store">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="course_code" class="form-label">Course Code</label>
                                <input type="text" wire:model="course_code" id="course_code" class="form-control @error('course_code') is-invalid @enderror" placeholder="e.g., CS101">
                                @error('course_code') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="name" class="form-label">Course Name</label>
                                <input type="text" wire:model="name" id="name" class="form-control @error('name') is-invalid @enderror" placeholder="e.g., Introduction to Programming">
                                @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="college_class_id" class="form-label">Program</label>
                                <select wire:model="college_class_id" id="college_class_id" class="form-select @error('college_class_id') is-invalid @enderror">
                                    <option value="">Select Program</option>
                                    @foreach($collegeClasses as $class)
                                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                                    @endforeach
                                </select>
                                @error('college_class_id') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="year_id" class="form-label">Year</label>
                                <select wire:model="year_id" id="year_id" class="form-select @error('year_id') is-invalid @enderror">
                                    <option value="">Select Year</option>
                                    @foreach($years as $year)
                                        <option value="{{ $year->id }}">{{ $year->name }}</option>
                                    @endforeach
                                </select>
                                @error('year_id') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="semester_id" class="form-label">Semester</label>
                                <select wire:model="semester_id" id="semester_id" class="form-select @error('semester_id') is-invalid @enderror">
                                    <option value="">Select Semester</option>
                                    @foreach($semesters as $semester)
                                        <option value="{{ $semester->id }}">{{ $semester->name }}</option>
                                    @endforeach
                                </select>
                                @error('semester_id') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea wire:model="description" id="description" class="form-control" rows="3" placeholder="Course description..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeModal">Cancel</button>
                    <button type="button" class="btn btn-primary" wire:click="store">{{ $editMode ? 'Update' : 'Save' }}</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Delete Confirmation Modal -->
    @if($isDeleteModalOpen)
    <div class="modal show" style="display: block; background-color: rgba(0,0,0,0.5);" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" wire:click="closeDeleteModal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this course? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeDeleteModal">Cancel</button>
                    <button type="button" class="btn btn-danger" wire:click="delete">Delete</button>
                </div>
            </div>
        </div>
    </div>
    @endif
