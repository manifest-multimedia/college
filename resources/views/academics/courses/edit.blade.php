<x-dashboard.default>
    <x-slot name="title">
        Edit Course
    </x-slot>
    
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title">
                                <i class="fas fa-edit me-2"></i>Edit Course
                            </h5>
                            <a href="{{ route('academics.courses.index') }}" class="btn btn-sm btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Back to Courses
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8 offset-md-2">
                                <form action="{{ route('academics.courses.update', $course) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    
                                    <div class="mb-3">
                                        <label for="course_code" class="form-label">Course Code</label>
                                        <input type="text" class="form-control @error('course_code') is-invalid @enderror" id="course_code" name="course_code" value="{{ old('course_code', $course->course_code) }}" placeholder="e.g., CS101" required>
                                        @error('course_code')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Course Name</label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $course->name) }}" placeholder="e.g., Introduction to Computer Science" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4">{{ old('description', $course->description) }}</textarea>
                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="slug" class="form-label">Slug</label>
                                        <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" value="{{ old('slug', $course->slug) }}">
                                        <small class="form-text text-muted">URL-friendly version of the course name.</small>
                                        @error('slug')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="d-grid gap-2 mt-4">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-1"></i> Update Course
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
            const originalSlug = "{{ $course->slug }}";
            
            // Generate slug from name if slug is manually cleared
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