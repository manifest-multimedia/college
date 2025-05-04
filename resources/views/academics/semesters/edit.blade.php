<x-dashboard.default>
    <x-slot name="title">
        Edit Semester
    </x-slot>
    
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title">
                                <i class="fas fa-edit me-2"></i>Edit Semester
                            </h5>
                            <a href="{{ route('academics.semesters.index') }}" class="btn btn-sm btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Back to Semesters
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8 offset-md-2">
                                <form action="{{ route('academics.semesters.update', $semester) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Semester Name</label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $semester->name) }}" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $semester->description) }}</textarea>
                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="academic_year_id" class="form-label">Academic Year</label>
                                        <select class="form-select @error('academic_year_id') is-invalid @enderror" id="academic_year_id" name="academic_year_id" required>
                                            <option value="">Select Academic Year</option>
                                            @foreach($academicYears as $academicYear)
                                                <option value="{{ $academicYear->id }}" {{ (old('academic_year_id', $semester->academic_year_id) == $academicYear->id) ? 'selected' : '' }}>
                                                    {{ $academicYear->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('academic_year_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        @if($errors->has('date_range'))
                                            <div class="text-danger mt-2">{{ $errors->first('date_range') }}</div>
                                        @endif
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="start_date" class="form-label">Start Date</label>
                                                <input type="date" class="form-control @error('start_date') is-invalid @enderror" id="start_date" name="start_date" value="{{ old('start_date', $semester->start_date ? $semester->start_date->format('Y-m-d') : '') }}" required>
                                                @error('start_date')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="end_date" class="form-label">End Date</label>
                                                <input type="date" class="form-control @error('end_date') is-invalid @enderror" id="end_date" name="end_date" value="{{ old('end_date', $semester->end_date ? $semester->end_date->format('Y-m-d') : '') }}" required>
                                                @error('end_date')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="d-grid gap-2 mt-4">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-1"></i> Update Semester
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