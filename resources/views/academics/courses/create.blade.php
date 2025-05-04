<x-dashboard.default>
    <x-slot name="title">
        Create Course
    </x-slot>
    
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title">
                                <i class="fas fa-plus-circle me-2"></i>Create Course
                            </h5>
                            <a href="{{ route('academics.courses.index') }}" class="btn btn-sm btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Back to Courses
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8 offset-md-2">
                                <form action="{{ route('academics.courses.store') }}" method="POST">
                                    @csrf
                                    
                                    <div class="mb-3">
                                        <label for="course_code" class="form-label">Course Code</label>
                                        <input type="text" class="form-control @error('course_code') is-invalid @enderror" id="course_code" name="course_code" value="{{ old('course_code') }}" placeholder="e.g., CS101" required>
                                        <small class="form-text text-muted">A unique identifier for the course</small>
                                        @error('course_code')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Course Name</label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" placeholder="e.g., Introduction to Computer Science" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4">{{ old('description') }}</textarea>
                                        <small class="form-text text-muted">Provide a detailed description of the course contents and objectives</small>
                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="slug" class="form-label">Slug</label>
                                        <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" value="{{ old('slug') }}" placeholder="e.g., intro-to-computer-science">
                                        <small class="form-text text-muted">URL-friendly version of the course name. Leave blank to generate automatically.</small>
                                        @error('slug')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="d-grid gap-2 mt-4">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-1"></i> Create Course
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const nameInput = document.getElementById('name');
            const slugInput = document.getElementById('slug');
            
            // Generate slug from name if slug is empty
            nameInput.addEventListener('blur', function() {
                if (slugInput.value === '') {
                    // Create slug from name - lowercase, replace spaces with hyphens, remove special chars
                    let slug = this.value.toLowerCase()
                        .replace(/\s+/g, '-')           // Replace spaces with -
                        .replace(/[^\w\-]+/g, '')       // Remove all non-word chars
                        .replace(/\-\-+/g, '-')         // Replace multiple - with single -
                        .replace(/^-+/, '')             // Trim - from start of text
                        .replace(/-+$/, '');            // Trim - from end of text
                    
                    slugInput.value = slug;
                }
            });
        });
    </script>
    @endpush
</x-dashboard.default>