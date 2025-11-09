<x-dashboard.default>
    <x-slot name="title">
        Create Academic Program
    </x-slot>
    
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title">
                                <i class="fas fa-plus-circle me-2"></i>Create Academic Program
                            </h5>
                            <a href="{{ route('academics.classes.index') }}" class="btn btn-sm btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Back to Programs
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8 offset-md-2">
                                <form action="{{ route('academics.classes.store') }}" method="POST">
                                    @csrf
                                    
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Program Name</label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" placeholder="e.g., Registered General Nursing, Computer Science Program" required>
                                        <small class="form-text text-muted">A descriptive name for the academic program</small>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="short_name" class="form-label">Short Name (Program Code)</label>
                                        <input type="text" class="form-control @error('short_name') is-invalid @enderror" id="short_name" name="short_name" value="{{ old('short_name') }}" placeholder="e.g., RGN, CS, RM" maxlength="10">
                                        <small class="form-text text-muted">
                                            Max 10 characters. Leave blank to auto-generate from program name.
                                        </small>
                                        @error('short_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" placeholder="Describe the program's objectives, structure, and outcomes...">{{ old('description') }}</textarea>
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
                                            <i class="fas fa-save me-1"></i> Create Program
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
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const nameInput = document.getElementById('name');
            const shortNameInput = document.getElementById('short_name');
            
            function generateShortName(programName) {
                if (!programName) return '';
                
                // Common program mappings (similar to backend logic)
                const mappings = {
                    'registered general nursing': 'RGN',
                    'general nursing': 'RGN',
                    'nursing': 'RGN',
                    'registered midwifery': 'RM',
                    'midwifery': 'RM',
                    'community health nursing': 'CHN',
                    'community health': 'CHN',
                    'psychiatric nursing': 'PN',
                    'mental health nursing': 'PN',
                    'psychiatric': 'PN',
                    'computer science': 'CS',
                    'information technology': 'IT',
                    'business administration': 'BA',
                    'accounting': 'ACC'
                };
                
                const lowerName = programName.toLowerCase().trim();
                
                // Check for exact matches
                for (const [key, value] of Object.entries(mappings)) {
                    if (lowerName.includes(key)) {
                        return value;
                    }
                }
                
                // Generate from initials
                const words = lowerName.split(' ');
                const skipWords = ['and', 'of', 'in', 'the', 'for', 'with', 'to', 'a', 'an'];
                let shortName = '';
                
                for (const word of words) {
                    if (!skipWords.includes(word) && word.length > 2) {
                        shortName += word.charAt(0).toUpperCase();
                        if (shortName.length >= 5) break;
                    }
                }
                
                return shortName || 'PROG';
            }
            
            nameInput.addEventListener('input', function() {
                if (!shortNameInput.value) { // Only auto-generate if short name is empty
                    const generated = generateShortName(this.value);
                    shortNameInput.placeholder = `Auto-generated: ${generated}`;
                }
            });
        });
    </script>
</x-dashboard.default>