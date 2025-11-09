<div>
    @if(session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <!-- Import Form -->
        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-header">
                    <div class="card-title">
                        <h3 class="card-title fw-bold text-gray-800">
                            <i class="fas fa-file-import me-2"></i>Import Students
                        </h3>
                    </div>
                </div>
                <div class="card-body">
                    <form wire:submit="import">
                        <div class="mb-3">
                            <label for="file" class="form-label">Excel File <span class="text-danger">*</span></label>
                            <input type="file" class="form-control @error('file') is-invalid @enderror" id="file" wire:model="file">
                            @error('file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Accepted formats: Excel (.xlsx, .xls) or CSV</small>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="programId" class="form-label">Program <span class="text-danger">*</span></label>
                                    <select id="programId" class="form-select @error('programId') is-invalid @enderror" wire:model="programId">
                                        <option value="">Select Program</option>
                                        @foreach ($programs as $program)
                                            <option value="{{ $program->id }}">{{ $program->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('programId')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">All imported students will be assigned to this program</small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="cohortId" class="form-label">Cohort <span class="text-danger">*</span></label>
                                    <select id="cohortId" class="form-select @error('cohortId') is-invalid @enderror" wire:model="cohortId">
                                        <option value="">Select Cohort</option>
                                        @foreach ($cohorts as $cohort)
                                            <option value="{{ $cohort->id }}">{{ $cohort->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('cohortId')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">All imported students will be assigned to this cohort</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="syncUsers" wire:model="syncUsers">
                                <label class="form-check-label" for="syncUsers">
                                    Automatically create user accounts for imported students
                                </label>
                            </div>
                            <small class="text-muted">If checked, system accounts will be created for students based on their email address</small>
                        </div>

                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-light" wire:click="resetForm">
                                <i class="fas fa-redo me-1"></i> Reset
                            </button>
                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="import">
                                <span wire:loading.remove wire:target="import">
                                    <i class="fas fa-file-import me-1"></i> Import Students
                                </span>
                                <span wire:loading wire:target="import">
                                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                    Processing...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Column Mapping -->
            <div class="card shadow-sm mt-4">
                <div class="card-header">
                    <div class="card-title">
                        <h3 class="card-title fw-bold text-gray-800">
                            <i class="fas fa-exchange-alt me-2"></i>Column Mapping
                        </h3>
                    </div>
                </div>
                <div class="card-body">
                    <p>The system will attempt to automatically map Excel columns to the database fields. You can adjust the mapping below if needed:</p>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Excel Column</th>
                                    <th>Database Field</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($defaultColumnMapping as $excelColumn => $dbField)
                                    <tr>
                                        <td>{{ $excelColumn }}</td>
                                        <td>
                                            <select class="form-select form-select-sm" wire:model="columnMapping.{{ $excelColumn }}">
                                                <option value="">-- Not Mapped --</option>
                                                @foreach ($availableFields as $fieldKey => $fieldName)
                                                    <option value="{{ $fieldKey }}">{{ $fieldName }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Instructions and Results -->
        <div class="col-lg-5">
            <!-- Import Results -->
            @if ($importResults)
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-success text-white">
                        <div class="card-title">
                            <h3 class="card-title fw-bold">
                                <i class="fas fa-check-circle me-2"></i>Import Results
                            </h3>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-column">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Total Records:</span>
                                <span class="fw-bold">{{ $importResults['total'] }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Successfully Created:</span>
                                <span class="fw-bold text-success">{{ $importResults['created'] }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Successfully Updated:</span>
                                <span class="fw-bold text-primary">{{ $importResults['updated'] }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Failed:</span>
                                <span class="fw-bold {{ $importResults['failed'] > 0 ? 'text-danger' : 'text-muted' }}">{{ $importResults['failed'] }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Skipped:</span>
                                <span class="fw-bold {{ $importResults['skipped'] > 0 ? 'text-warning' : 'text-muted' }}">{{ $importResults['skipped'] }}</span>
                            </div>
                            @if(isset($importResults['ids_generated']) && $importResults['ids_generated'] > 0)
                            <div class="d-flex justify-content-between mb-2">
                                <span>Student IDs Generated:</span>
                                <span class="fw-bold text-info">{{ $importResults['ids_generated'] }}</span>
                            </div>
                            @endif
                        </div>
                        
                        @if(isset($importResults['sync_output']))
                            <div class="mt-3">
                                <h6>User Account Sync Results:</h6>
                                <pre class="bg-light p-2" style="font-size: 0.8rem; max-height: 300px; overflow-y: auto;">{{ $importResults['sync_output'] }}</pre>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Instructions -->
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <div class="card-title">
                        <h3 class="card-title fw-bold text-gray-800">
                            <i class="fas fa-info-circle me-2"></i>Instructions
                        </h3>
                    </div>
                </div>
                <div class="card-body">
                    <h5>File Format Requirements</h5>
                    <ul>
                        <li>Upload an Excel (.xlsx, .xls) or CSV file</li>
                        <li>First row should contain column headers</li>
                        <li>Headers should match the column names in the mapping section</li>
                    </ul>
                    
                    <h5>Expected Excel Columns</h5>
                    <ul>
                        <li><strong>Student ID</strong>: Unique identifier (optional - will be auto-generated if missing)</li>
                        <li><strong>First Name</strong>: Student's first name (required for ID generation)</li>
                        <li><strong>Last Name</strong>: Student's last name (required for ID generation)</li>
                        <li><strong>Other Name(s)</strong>: Middle names or other names</li>
                        <li><strong>Email</strong>: Required for user account creation</li>
                        <li><strong>Mobile Number</strong>: Contact number</li>
                        <li>... and other demographic information</li>
                    </ul>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-lightbulb me-2"></i>
                        <strong>Automatic ID Generation:</strong> If Student ID is missing, the system will automatically generate one using the format: PNMTC/DA/[PROGRAM]/[YEAR]/[NUMBER] based on the selected program and alphabetical ordering.
                    </div>
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Existing students will be updated if their Student ID or Email matches records in the database.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
