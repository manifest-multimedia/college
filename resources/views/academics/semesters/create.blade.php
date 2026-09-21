<x-dashboard.default>
    <x-slot name="title">
        Create Semester
    </x-slot>
    
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title">
                                <i class="fas fa-plus-circle me-2"></i>Create Semester
                            </h5>
                            <a href="{{ route('academics.semesters.index') }}" class="btn btn-sm btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Back to Semesters
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8 offset-md-2">
                                <form action="{{ route('academics.semesters.store') }}" method="POST">
                                    @csrf
                                    
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Semester Name</label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" placeholder="e.g., First Semester" required>
                                        <small class="form-text text-muted">This name may be reused in another Academic Year (e.g. Semester 1).</small>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                                        <small class="form-text text-muted">Optional description for the semester</small>
                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="academic_year_id" class="form-label">Academic Year</label>
                                        <select class="form-select @error('academic_year_id') is-invalid @enderror" id="academic_year_id" name="academic_year_id" required>
                                            <option value="">Select Academic Year</option>
                                            @foreach($academicYears as $academicYear)
                                                <option value="{{ $academicYear->id }}" {{ old('academic_year_id') == $academicYear->id ? 'selected' : '' }}>
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

                                    <div class="mb-3">
                                        <label for="sequence" class="form-label">Semester Position</label>
                                        <input type="number" min="1" class="form-control @error('sequence') is-invalid @enderror" id="sequence" name="sequence" value="{{ old('sequence') }}" placeholder="e.g., 1" required>
                                        <small class="form-text text-muted">Use 1, 2, 3… to define the order within this Academic Year.</small>
                                        @error('sequence')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="start_date" class="form-label">Start Date</label>
                                                <input type="date" class="form-control @error('start_date') is-invalid @enderror" id="start_date" name="start_date" value="{{ old('start_date') }}" required>
                                                @error('start_date')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="end_date" class="form-label">End Date</label>
                                                <input type="date" class="form-control @error('end_date') is-invalid @enderror" id="end_date" name="end_date" value="{{ old('end_date') }}" required>
                                                @error('end_date')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card bg-light border-0 mb-3 mt-2">
                                        <div class="card-body">
                                            <h6 class="card-title text-primary mb-2">
                                                <i class="fas fa-calendar-alt me-1"></i> Course Registration Window (Optional)
                                            </h6>
                                            <p class="text-muted small mb-3">Set when students can begin registering for courses and the deadline after which registration closes.</p>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="registration_starts_at" class="form-label">Registration Opens At</label>
                                                        <input type="datetime-local" class="form-control @error('registration_starts_at') is-invalid @enderror" id="registration_starts_at" name="registration_starts_at" value="{{ old('registration_starts_at') }}">
                                                        <small class="form-text text-muted">Leave blank for registration to open immediately upon semester start.</small>
                                                        @error('registration_starts_at')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="registration_deadline" class="form-label">Registration Deadline</label>
                                                        <input type="datetime-local" class="form-control @error('registration_deadline') is-invalid @enderror" id="registration_deadline" name="registration_deadline" value="{{ old('registration_deadline') }}">
                                                        <small class="form-text text-muted">Leave blank if there is no deadline cutoff.</small>
                                                        @error('registration_deadline')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="d-grid gap-2 mt-4">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-1"></i> Create Semester
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
            const academicYearSelect = document.getElementById('academic_year_id');
            const startDateInput = document.getElementById('start_date');
            const endDateInput = document.getElementById('end_date');
            
            // When academic year changes, check the date restrictions
            academicYearSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                if (selectedOption.value) {
                    // You can add code here to fetch the academic year dates and set min/max for semester dates
                    // For now, we'll rely on server-side validation
                }
            });
        });
    </script>
    @endpush
</x-dashboard.default>
