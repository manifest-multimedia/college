<x-dashboard.default>
    <x-slot name="title">
        Create Academic Year
    </x-slot>
    
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title">
                                <i class="fas fa-plus-circle me-2"></i>Create Academic Year
                            </h5>
                            <a href="{{ route('academics.academic-years.index') }}" class="btn btn-sm btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Back to Academic Years
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8 offset-md-2">
                                <form action="{{ route('academics.academic-years.store') }}" method="POST">
                                    @csrf
                                    
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Academic Year Name</label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" placeholder="e.g., 2024-2025" required>
                                        <small class="form-text text-muted">A descriptive name for the academic year (e.g., "2024-2025")</small>
                                        @error('name')
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
                                    
                                    <div class="d-grid gap-2 mt-4">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-1"></i> Create Academic Year
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
            // Auto-populate year based on start date
            const startDateInput = document.getElementById('start_date');
            const nameInput = document.getElementById('name');
            
            startDateInput.addEventListener('change', function() {
                if (nameInput.value === '') {
                    const startDate = new Date(this.value);
                    const endYear = startDate.getFullYear() + 1;
                    nameInput.value = `${startDate.getFullYear()}-${endYear}`;
                }
            });
        });
    </script>
    @endpush
</x-dashboard.default>