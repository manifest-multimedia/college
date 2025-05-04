<x-dashboard.default>
    <x-slot name="title">
        Academic Settings
    </x-slot>
    
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title">
                                <i class="fas fa-cogs me-2"></i>Academic Settings
                            </h5>
                            <a href="{{ route('academics.dashboard') }}" class="btn btn-sm btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                        
                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                        
                        <div class="row">
                            <div class="col-md-8 offset-md-2">
                                <div class="card">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-calendar-check me-1"></i> Set Current Academic Year and Semester
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <form action="{{ route('academics.settings.update') }}" method="POST">
                                            @csrf
                                            
                                            <div class="mb-3">
                                                <label for="academic_year_id" class="form-label">Current Academic Year</label>
                                                <select class="form-select @error('academic_year_id') is-invalid @enderror" id="academic_year_id" name="academic_year_id" required>
                                                    <option value="">Select Academic Year</option>
                                                    @foreach($academicYears as $year)
                                                        <option value="{{ $year->id }}" {{ (old('academic_year_id') == $year->id || ($currentAcademicYear && $currentAcademicYear->id == $year->id)) ? 'selected' : '' }}>
                                                            {{ $year->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('academic_year_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="semester_id" class="form-label">Current Semester</label>
                                                <select class="form-select @error('semester_id') is-invalid @enderror" id="semester_id" name="semester_id" required>
                                                    <option value="">Select Semester</option>
                                                    @foreach($semesters as $semester)
                                                        <option value="{{ $semester->id }}" 
                                                            {{ (old('semester_id') == $semester->id || ($currentSemester && $currentSemester->id == $semester->id)) ? 'selected' : '' }}
                                                            data-academic-year-id="{{ $semester->academic_year_id }}">
                                                            {{ $semester->name }} ({{ optional($semester->academicYear)->name ?? 'No Academic Year' }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('semester_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            
                                            <div class="alert alert-warning">
                                                <i class="fas fa-exclamation-triangle me-1"></i> Setting the current academic year and semester will affect system-wide defaults for forms, filters, and reports.
                                            </div>
                                            
                                            <div class="d-grid gap-2">
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fas fa-save me-1"></i> Save Settings
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                
                                <!-- Migration tool for admins only -->
                                @if(auth()->user()->hasRole('admin'))
                                <div class="card mt-4">
                                    <div class="card-header bg-warning text-dark">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-database me-1"></i> Data Migration Tools
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="alert alert-danger">
                                            <i class="fas fa-exclamation-circle me-1"></i> <strong>Warning:</strong> The following operations affect system data. Use with caution.
                                        </div>
                                        
                                        <form action="{{ route('academics.migrate-year-data') }}" method="POST" class="mt-3" onsubmit="return confirm('Are you sure you want to migrate data from years to academic_years? This operation cannot be undone.');">
                                            @csrf
                                            <div class="d-grid gap-2">
                                                <button type="submit" class="btn btn-warning">
                                                    <i class="fas fa-sync me-1"></i> Migrate Year Data to Academic Years
                                                </button>
                                            </div>
                                            <small class="form-text text-muted">
                                                This will copy data from the legacy 'years' table to the new 'academic_years' table and update all foreign key references.
                                            </small>
                                        </form>
                                    </div>
                                </div>
                                @endif
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
            const semesterSelect = document.getElementById('semester_id');
            
            // Filter semesters based on selected academic year
            function filterSemesters() {
                const selectedAcademicYearId = academicYearSelect.value;
                
                // Show/hide semester options based on academic year
                Array.from(semesterSelect.options).forEach(option => {
                    if (option.value === '') return; // Skip the placeholder option
                    
                    const academicYearId = option.getAttribute('data-academic-year-id');
                    
                    if (selectedAcademicYearId === '' || academicYearId === selectedAcademicYearId) {
                        option.style.display = '';
                    } else {
                        option.style.display = 'none';
                    }
                });
                
                // Reset semester selection if current selection doesn't match academic year
                const currentSemesterOption = semesterSelect.querySelector(`option[value="${semesterSelect.value}"]`);
                if (currentSemesterOption && 
                    currentSemesterOption.style.display === 'none') {
                    semesterSelect.value = '';
                }
            }
            
            academicYearSelect.addEventListener('change', filterSemesters);
            
            // Initial filter
            filterSemesters();
        });
    </script>
    @endpush
</x-dashboard.default>