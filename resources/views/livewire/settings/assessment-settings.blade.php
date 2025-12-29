<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Assessment Score Weight Configuration</h3>
            <div class="card-toolbar">
                <button wire:click="resetToDefaults" class="btn btn-sm btn-light-warning">
                    <i class="fas fa-undo"></i> Reset to Defaults
                </button>
            </div>
        </div>
        
        <div class="card-body">
            <!-- Flash Messages -->
            @if (session()->has('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session()->has('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session()->has('info'))
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <i class="fas fa-info-circle me-2"></i>
                    {{ session('info') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Description -->
            <div class="alert alert-primary mb-6">
                <div class="d-flex align-items-center">
                    <i class="fas fa-info-circle fs-2 me-3"></i>
                    <div>
                        <h5 class="mb-1">Default Weight Configuration</h5>
                        <p class="mb-0">
                            Configure the default percentage weights for assessment components. These weights will be automatically applied when creating new assessment scoresheets. The total must equal 100%.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Weight Configuration Form -->
            <div class="row g-6 mb-6">
                <!-- Assignment Weight -->
                <div class="col-md-4">
                    <div class="card bg-light-primary h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-4">
                                <i class="fas fa-tasks fs-2x text-primary me-3"></i>
                                <h4 class="mb-0">Assignments</h4>
                            </div>
                            
                            <label class="form-label fw-bold">Weight (%)</label>
                            <div class="input-group input-group-lg">
                                <input type="number" 
                                       wire:model.live="assignment_weight" 
                                       class="form-control form-control-lg" 
                                       min="0" 
                                       max="100" 
                                       step="1">
                                <span class="input-group-text">%</span>
                            </div>
                            
                            <div class="form-text mt-2">
                                Weight for assignment scores (averaged across 3-5 assignments)
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mid-Semester Weight -->
                <div class="col-md-4">
                    <div class="card bg-light-warning h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-4">
                                <i class="fas fa-calendar-check fs-2x text-warning me-3"></i>
                                <h4 class="mb-0">Mid-Semester</h4>
                            </div>
                            
                            <label class="form-label fw-bold">Weight (%)</label>
                            <div class="input-group input-group-lg">
                                <input type="number" 
                                       wire:model.live="mid_semester_weight" 
                                       class="form-control form-control-lg" 
                                       min="0" 
                                       max="100" 
                                       step="1">
                                <span class="input-group-text">%</span>
                            </div>
                            
                            <div class="form-text mt-2">
                                Weight for mid-semester examination score
                            </div>
                        </div>
                    </div>
                </div>

                <!-- End-Semester Weight -->
                <div class="col-md-4">
                    <div class="card bg-light-success h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-4">
                                <i class="fas fa-graduation-cap fs-2x text-success me-3"></i>
                                <h4 class="mb-0">End-Semester</h4>
                            </div>
                            
                            <label class="form-label fw-bold">Weight (%)</label>
                            <div class="input-group input-group-lg">
                                <input type="number" 
                                       wire:model.live="end_semester_weight" 
                                       class="form-control form-control-lg" 
                                       min="0" 
                                       max="100" 
                                       step="1">
                                <span class="input-group-text">%</span>
                            </div>
                            
                            <div class="form-text mt-2">
                                Weight for end-semester/final examination score
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Calculation -->
            <div class="card bg-light mb-6">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="mb-2">Total Weight Percentage</h4>
                            <p class="text-muted mb-0">
                                The sum of all weights must equal 100% for valid configuration
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            @php
                                $total = $assignment_weight + $mid_semester_weight + $end_semester_weight;
                                $isValid = $total == 100;
                            @endphp
                            
                            <div class="d-flex align-items-center justify-content-end">
                                <span class="fs-2x fw-bold {{ $isValid ? 'text-success' : 'text-danger' }} me-2">
                                    {{ $total }}%
                                </span>
                                @if ($isValid)
                                    <i class="fas fa-check-circle text-success fs-2x"></i>
                                @else
                                    <i class="fas fa-times-circle text-danger fs-2x"></i>
                                @endif
                            </div>
                            
                            @if (!$isValid)
                                <small class="text-danger">
                                    {{ $total > 100 ? 'Exceeds 100% by ' . ($total - 100) . '%' : 'Short by ' . (100 - $total) . '%' }}
                                </small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Save Button -->
            <div class="d-flex justify-content-end">
                <button wire:click="saveSettings" 
                        class="btn btn-primary btn-lg"
                        {{ $total != 100 ? 'disabled' : '' }}>
                    <i class="fas fa-save me-2"></i>
                    Save Settings
                </button>
            </div>

            <!-- Information Box -->
            <div class="alert alert-light-info mt-6">
                <div class="d-flex">
                    <i class="fas fa-lightbulb fs-2 text-info me-3 mt-1"></i>
                    <div>
                        <h5 class="mb-2 text-info">How This Works</h5>
                        <ul class="mb-0">
                            <li class="mb-2">
                                <strong>Assignments:</strong> The system averages all assignment scores (3-5 assignments) and applies this weight to the average.
                            </li>
                            <li class="mb-2">
                                <strong>Mid-Semester Exam:</strong> Single exam score weighted by this percentage.
                            </li>
                            <li class="mb-2">
                                <strong>End-Semester Exam:</strong> Final exam score weighted by this percentage.
                            </li>
                            <li class="mb-2">
                                <strong>Total Score:</strong> Sum of all weighted scores, used to determine letter grades (A-E).
                            </li>
                            <li>
                                These default weights will be applied to all new assessment scoresheets. Lecturers can override these weights for individual courses if needed.
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
