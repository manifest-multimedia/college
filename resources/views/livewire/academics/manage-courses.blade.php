<x-dashboard.content-card>
    <div class="container-fluid">
        <!-- Search and Create Button -->
        <div class="row mb-4">
            <div class="col-md-6">
                <input wire:model.debounce.300ms="searchTerm" type="text" class="form-control" placeholder="Search courses...">
            </div>
            <div class="col-md-6 text-end">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createCourseModal">
                    <i class="fas fa-plus-circle me-1"></i> Add New Course
                </button>
            </div>
        </div>

        <!-- Courses Table -->
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Course Code</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($courses as $course)
                        <tr>
                            <td>{{ $course->course_code }}</td>
                            <td>{{ $course->name }}</td>
                            <td>{{ $course->description }}</td>
                            <td>
                                <button wire:click="edit({{ $course->id }})" class="btn btn-sm btn-info me-2" data-bs-toggle="modal" data-bs-target="#editCourseModal">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button wire:click="delete({{ $course->id }})" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this course?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">No courses found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-end">
            {{ $courses->links() }}
        </div>
    </div>

    <!-- Create Course Modal -->
    <div wire:ignore.self class="modal fade" id="createCourseModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Course</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form wire:submit.prevent="create">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="course_code" class="form-label">Course Code</label>
                            <input wire:model="course_code" type="text" class="form-control @error('course_code') is-invalid @enderror" id="course_code">
                            @error('course_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="name" class="form-label">Course Name</label>
                            <input wire:model="name" type="text" class="form-control @error('name') is-invalid @enderror" id="name">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea wire:model="description" class="form-control @error('description') is-invalid @enderror" id="description"></textarea>
                            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Course</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Course Modal -->
    <div wire:ignore.self class="modal fade" id="editCourseModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Course</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form wire:submit.prevent="update">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_course_code" class="form-label">Course Code</label>
                            <input wire:model="course_code" type="text" class="form-control @error('course_code') is-invalid @enderror" id="edit_course_code">
                            @error('course_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="edit_name" class="form-label">Course Name</label>
                            <input wire:model="name" type="text" class="form-control @error('name') is-invalid @enderror" id="edit_name">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="edit_description" class="form-label">Description</label>
                            <textarea wire:model="description" class="form-control @error('description') is-invalid @enderror" id="edit_description"></textarea>
                            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" wire:click="cancel">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Course</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-dashboard.content-card>