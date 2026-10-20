<div>
    @if(session()->has('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            {{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
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
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                <label for="file" class="form-label mb-0">Excel File <span class="text-danger">*</span></label>
                                <a href="{{ route('students.import.template') }}" class="btn btn-sm btn-light-primary">
                                    <i class="fas fa-download me-1"></i> Student Import Template
                                </a>
                            </div>
                            <input type="file" class="form-control @error('file') is-invalid @enderror" id="file" wire:model="file">
                            @error('file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Upload an Excel (.xlsx, .xls) or CSV file. After upload, map its actual headings to student fields.</small>
                        </div>

                        @if ($file)
                            <div class="mb-3">
                                <label for="headingRow" class="form-label">Heading Row</label>
                                <select id="headingRow" class="form-select" wire:model.live="headingRow">
                                    @for ($row = 1; $row <= 10; $row++)
                                        <option value="{{ $row }}">Row {{ $row }}</option>
                                    @endfor
                                </select>
                                <small class="text-muted">Choose a later row only when the spreadsheet has a title or notes above its column headings.</small>
                            </div>
                        @endif
                        
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
                        
                        <div class="mb-3">
                            <label for="academicYearId" class="form-label">Academic Year</label>
                            <select id="academicYearId" class="form-select @error('academicYearId') is-invalid @enderror" wire:model="academicYearId">
                                <option value="">Use Current Academic Year</option>
                                @foreach ($academicYears as $academicYear)
                                    <option value="{{ $academicYear->id }}">{{ $academicYear->name }}</option>
                                @endforeach
                            </select>
                            @error('academicYearId')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Select the academic year for student ID generation. If not selected, the current academic year will be used.</small>
                        </div>
                        
                        @if(in_array('date_of_birth', $columnMapping, true))
                            @php
                                $dateParser = new \App\Services\StudentImportDateParser;
                                $dateAnalysis = $dateFormatAnalysis ?: $dateParser->inspect([]);
                                $dateStatus = $dateFormatAnalysis === [] ? 'unavailable' : $dateParser->status($dateAnalysis);
                            @endphp
                            <div class="mb-4">
                                <label for="dateFormat" class="form-label">Date of Birth Format</label>
                                <select id="dateFormat" class="form-select @error('dateFormat') is-invalid @enderror" wire:model.live="dateFormat">
                                    <option value="auto">Detect from the date column</option>
                                    <option value="dmy">Day / Month / Year (DD/MM/YYYY)</option>
                                    <option value="mdy">Month / Day / Year (MM/DD/YYYY)</option>
                                </select>
                                @error('dateFormat')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="alert {{ in_array($dateStatus, ['ambiguous', 'mixed'], true) ? 'alert-warning' : 'alert-info' }} mt-2 mb-2" role="status">
                                    @if($dateStatus === 'unavailable')
                                        Date preview is unavailable. Confirm the mapping or re-upload the file; import will recheck the column before proceeding.
                                    @elseif($dateStatus === 'detected')
                                        Detected <strong>{{ $dateAnalysis['dmy'] > 0 ? 'day/month/year' : 'month/day/year' }}</strong>
                                        from {{ $dateAnalysis['dmy'] + $dateAnalysis['mdy'] }} unambiguous numeric dates in the full column.
                                    @elseif($dateStatus === 'ambiguous')
                                        Both day-first and month-first interpretations are possible. Choose a format using the examples below; automatic import is blocked.
                                    @elseif($dateStatus === 'mixed')
                                        Conflicting day-first and month-first dates were found. Correct the column or choose one format consistently; incompatible rows will be reported as invalid.
                                    @elseif($dateStatus === 'empty')
                                        No dates supplied in the mapped column. Dates of birth will remain optional.
                                    @elseif($dateStatus === 'invalid')
                                        No valid dates were recognized in this column. Check the mapping and source values before importing.
                                    @else
                                        No day/month choice is needed for the recognized year-first, named-month or Excel dates.
                                    @endif
                                    @if($dateFormat !== 'auto')
                                        <div class="mt-1"><strong>Your selection:</strong> {{ $dateFormat === 'dmy' ? 'day/month/year' : 'month/day/year' }}. Numeric dates will use this order consistently.</div>
                                    @endif
                                    @if($dateAnalysis['invalid'] > 0)
                                        <div class="mt-1">{{ $dateAnalysis['invalid'] }} invalid dates found. Review the row errors after import.</div>
                                    @endif
                                </div>
                                @if($dateAnalysis['examples'])
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered mb-1">
                                            <thead><tr><th>File value</th><th>Day-first meaning</th><th>Month-first meaning</th></tr></thead>
                                            <tbody>
                                                @foreach($dateAnalysis['examples'] as $example)
                                                    <tr>
                                                        <td>{{ $example['value'] }}</td>
                                                        <td>{{ $example['dmy'] ? (new \DateTimeImmutable($example['dmy']))->format('j M Y') : 'Invalid' }}</td>
                                                        <td>{{ $example['mdy'] ? (new \DateTimeImmutable($example['mdy']))->format('j M Y') : 'Invalid' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                                <small class="text-muted">The full mapped column is checked before import. Year-first and Excel dates retain their meaning; valid dates are saved as YYYY-MM-DD.</small>
                            </div>
                        @endif

                        <div class="alert alert-info mb-4">
                            <i class="fas fa-user-check me-2"></i>
                            <strong>Student portal accounts are created automatically.</strong>
                            Every imported student must have a unique, valid email address. Their initial password is their Student ID.
                        </div>

                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-light" wire:click="resetForm">
                                <i class="fas fa-redo me-1"></i> Reset
                            </button>
                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="import" @disabled(empty($uploadedHeaders))>
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
            @if ($file)
            <div class="card shadow-sm mt-4">
                <div class="card-header">
                    <div class="card-title">
                        <h3 class="card-title fw-bold text-gray-800">
                            <i class="fas fa-exchange-alt me-2"></i>Column Mapping
                        </h3>
                    </div>
                </div>
                <div class="card-body">
                    @if (empty($uploadedHeaders))
                        <div class="alert alert-light-warning mb-0">
                            <i class="fas fa-circle-notch fa-spin me-2" wire:loading wire:target="file,headingRow"></i>
                            We could not yet identify the headings. Choose the row containing your field names, or upload a file with headings.
                        </div>
                    @else
                    <p>The columns below are from the uploaded file. Suggested matches are selected automatically; confirm or adjust them before import.</p>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Spreadsheet Column</th>
                                    <th>Sample Value</th>
                                    <th>Database Field</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($uploadedHeaders as $header)
                                    <tr>
                                        <td class="fw-semibold">{{ $header['label'] }}</td>
                                        <td class="text-muted text-truncate" style="max-width: 220px;">{{ $header['sample'] ?: '—' }}</td>
                                        <td>
                                            <select class="form-select form-select-sm" wire:model.live="columnMapping.{{ $header['key'] }}">
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
                    @error('columnMapping')
                        <div class="text-danger small mt-2">{{ $message }}</div>
                    @enderror
                    @endif
                </div>
            </div>
            @endif
        </div>
        
        <!-- Instructions and Results -->
        <div class="col-lg-5">
            <!-- Import Results -->
            @if ($importResults)
                <div class="card shadow-sm mb-4">
                    <div class="card-header {{ $importResults['skipped'] > 0 || $importResults['failed'] > 0 ? 'bg-warning text-dark' : 'bg-success text-white' }}">
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
                            @if(isset($importResults['processed']))
                            <div class="d-flex justify-content-between mb-2">
                                <span>Successfully Processed:</span>
                                <span class="fw-bold text-info">{{ $importResults['processed'] }}</span>
                            </div>
                            @endif
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
                            @if(isset($importResults['accounts_created']))
                            <div class="d-flex justify-content-between mb-2">
                                <span>Student Accounts Created:</span>
                                <span class="fw-bold text-success">{{ $importResults['accounts_created'] }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Existing Accounts Linked:</span>
                                <span class="fw-bold text-info">{{ $importResults['accounts_linked'] ?? 0 }}</span>
                            </div>
                            @endif
                        </div>
                        
                        @if(isset($importResults['validation_errors']) && count($importResults['validation_errors']) > 0)
                            <div class="mt-3">
                                <h6>Validation Errors:</h6>
                                <div class="alert alert-warning" style="max-height: 200px; overflow-y: auto;">
                                    @foreach($importResults['validation_errors'] as $error)
                                        <div class="mb-2">
                                            <strong>Row {{ $error['row'] }}:</strong>
                                            <ul class="mb-0">
                                                @foreach($error['errors'] as $err)
                                                    <li>{{ $err }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        
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
                        <li>The file may use its own column names; select the heading row and map the detected columns after upload</li>
                        <li>Every import requires First Name, Last Name, and Email because student portal accounts are created automatically</li>
                    </ul>
                    
                    <h5>Expected Excel Columns</h5>
                    <ul>
                        <li><strong>Student ID</strong>: Unique identifier (optional — the system generates one when it is not supplied)</li>
                        <li><strong>First Name</strong>: Student's first name (required for ID generation)</li>
                        <li><strong>Last Name</strong>: Student's last name (required for ID generation)</li>
                        <li><strong>Other Name(s)</strong>: Middle names or other names</li>
                        <li><strong>Email</strong>: Required and must be unique for student account creation</li>
                        <li><strong>Mobile Number</strong>: Contact number</li>
                        <li><strong>Date of Birth</strong>: The format is detected from the full column. Choose day-first or month-first when ambiguous; YYYY-MM-DD and Excel date cells are also supported.</li>
                        <li>... and other demographic information</li>
                    </ul>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-lightbulb me-2"></i>
                        <strong>Automatic ID Generation:</strong> If Student ID is not mapped or is empty for a valid row, the system automatically generates one using the configured format based on the selected Program and Academic Year.
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
