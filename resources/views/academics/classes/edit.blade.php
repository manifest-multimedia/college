<x-dashboard.default>
    <x-slot name="title">
        Edit College Class
    </x-slot>
    
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title">
                                <i class="fas fa-edit me-2"></i>Edit College Class
                            </h5>
                            <a href="{{ route('academics.classes.index') }}" class="btn btn-sm btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Back to Classes
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8 offset-md-2">
                                <form action="{{ route('academics.classes.update', $class) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Class Name</label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $class->name) }}" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $class->description) }}</textarea>
                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="semester_id" class="form-label">Semester</label>
                                        <select class="form-select @error('semester_id') is-invalid @enderror" id="semester_id" name="semester_id" required>
                                            <option value="">Select Semester</option>
                                            @foreach($semesters as $semester)
                                                <option value="{{ $semester->id }}" {{ (old('semester_id', $class->semester_id) == $semester->id) ? 'selected' : '' }}>
                                                    {{ $semester->name }} ({{ optional($semester->academicYear)->name ?? 'No Academic Year' }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('semester_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    
                                    <div class="mb-3">
                                        <label for="instructor_id" class="form-label">Instructor</label>
                                        <select class="form-select @error('instructor_id') is-invalid @enderror" id="instructor_id" name="instructor_id">
                                            <option value="">Select Instructor (Optional)</option>
                                            @foreach($instructors as $instructor)
                                                <option value="{{ $instructor->id }}" {{ (old('instructor_id', $class->instructor_id) == $instructor->id) ? 'selected' : '' }}>
                                                    {{ $instructor->name }} ({{ $instructor->email }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('instructor_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="d-grid gap-2 mt-4">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-1"></i> Update Class
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
</x-dashboard.default>