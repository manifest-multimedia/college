<x-dashboard.default>
    <x-slot name="title">
        Edit Academic Program
    </x-slot>
    
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title">
                                <i class="fas fa-edit me-2"></i>Edit Academic Program
                            </h5>
                            <a href="{{ route('academics.classes.index') }}" class="btn btn-sm btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Back to Programs
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
                                        <label for="name" class="form-label">Program Name</label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $class->name) }}" placeholder="e.g., Registered General Nursing, Computer Science Program" required>
                                        <small class="form-text text-muted">A descriptive name for the academic program</small>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="short_name" class="form-label">Short Name (Program Code)</label>
                                        <input type="text" class="form-control @error('short_name') is-invalid @enderror" id="short_name" name="short_name" value="{{ old('short_name', $class->short_name) }}" placeholder="e.g., RGN, CS, RM" maxlength="10">
                                        <small class="form-text text-muted">
                                            Max 10 characters. Leave blank to auto-generate from program name.
                                            @if(empty($class->short_name))
                                                <span class="text-info">Currently auto-generated: <strong>{{ $class->getProgramCode() }}</strong></span>
                                            @endif
                                        </small>
                                        @error('short_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" placeholder="Describe the program's objectives, structure, and outcomes...">{{ old('description', $class->description) }}</textarea>
                                        <small class="form-text text-muted">Optional description for the program</small>
                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>Note:</strong> Programs are semester-independent academic offerings that can run across multiple academic periods. Courses within programs can be taught by different instructors as needed.
                                    </div>
                                    
                                    <div class="d-grid gap-2 mt-4">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-1"></i> Update Program
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