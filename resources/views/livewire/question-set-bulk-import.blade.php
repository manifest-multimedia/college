<div>
    <!-- Success/Error Messages -->
    @if(session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(!$questionSet)
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>Error:</strong> Question set not found.
            <a href="{{ route('question.sets') }}" class="btn btn-primary btn-sm ms-2">
                <i class="bi bi-arrow-left me-1"></i>Back to Question Sets
            </a>
        </div>
    @else
        <!-- Question Set Info -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="bg-primary text-white rounded-circle p-2 me-3">
                        <i class="bi bi-collection fs-4"></i>
                    </div>
                    <div>
                        <h5 class="mb-1">{{ $questionSet->name }}</h5>
                        <p class="text-muted mb-0">
                            <i class="bi bi-book me-1"></i>{{ $questionSet->course->name ?? 'N/A' }} • 
                            <i class="bi bi-question-circle me-1"></i>{{ $questionsCount }} existing questions
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($questionSet)
    <div class="row">
        <!-- Upload Form -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-cloud-upload me-2"></i>Upload Import File
                    </h5>
                </div>
                <div class="card-body">
                    @if(!$showPreview)
                        <form wire:submit="preview">
                            <!-- File Upload -->
                            <div class="mb-4">
                                <label for="file" class="form-label">Select File <span class="text-danger">*</span></label>
                                <input type="file" class="form-control @error('file') is-invalid @enderror" 
                                       id="file" wire:model="file" accept=".xlsx,.xls,.csv,.txt">
                                @error('file')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">
                                    Supported formats: Excel (.xlsx, .xls), CSV (.csv), Aiken (.txt). Max size: 10MB
                                </small>
                            </div>

                            <!-- Format Selection -->
                            <div class="mb-4">
                                <label class="form-label">Import Format <span class="text-danger">*</span></label>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="importFormat" 
                                                   id="formatExcel" value="excel" wire:model="importFormat">
                                            <label class="form-check-label" for="formatExcel">
                                                <i class="bi bi-file-earmark-spreadsheet text-success me-2"></i>
                                                <strong>Excel/CSV Format</strong>
                                                <small class="d-block text-muted">Structured spreadsheet with columns</small>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="importFormat" 
                                                   id="formatAiken" value="aiken" wire:model="importFormat">
                                            <label class="form-check-label" for="formatAiken">
                                                <i class="bi bi-file-text text-info me-2"></i>
                                                <strong>Aiken Format</strong>
                                                <small class="d-block text-muted">Plain text with structured format</small>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                @error('importFormat')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-flex justify-content-between">
                                <button type="button" class="btn btn-outline-secondary" wire:click="resetForm">
                                    <i class="bi bi-arrow-clockwise me-1"></i>Reset
                                </button>
                                <button type="submit" class="btn btn-primary" 
                                        wire:loading.attr="disabled" wire:target="preview">
                                    <span wire:loading.remove wire:target="preview">
                                        <i class="bi bi-eye me-1"></i>Preview Import
                                    </span>
                                    <span wire:loading wire:target="preview">
                                        <i class="bi bi-hourglass-split me-1"></i>Processing...
                                    </span>
                                </button>
                            </div>
                        </form>
                    @endif

                    <!-- Preview Section -->
                    @if($showPreview)
                        <div class="border rounded p-3 mb-4" style="background-color: #f8f9fa;">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">
                                    <i class="bi bi-eye me-2"></i>Import Preview
                                    <span class="badge bg-info ms-2">{{ count($previewData) }} questions</span>
                                </h6>
                                <button type="button" class="btn btn-outline-secondary btn-sm" wire:click="resetPreview">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>

                            <!-- Validation Errors -->
                            @if(!empty($validationErrors))
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    <strong>Validation Issues Found:</strong>
                                    <ul class="mb-0 mt-2">
                                        @foreach($validationErrors as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <!-- Preview Table -->
                            @if(!empty($previewData))
                                <div class="table-responsive" style="max-height: 400px;">
                                    <table class="table table-sm">
                                        <thead class="table-light sticky-top">
                                            <tr>
                                                <th>Line</th>
                                                <th>Question</th>
                                                <th>Options</th>
                                                <th>Correct</th>
                                                <th>Marks</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($previewData as $question)
                                                <tr class="{{ $question['is_valid'] ? '' : 'table-warning' }}">
                                                    <td class="fw-bold">{{ $question['line'] }}</td>
                                                    <td>
                                                        <div class="text-truncate" style="max-width: 200px;" 
                                                             title="{{ $question['question_text'] }}">
                                                            {{ $question['question_text'] }}
                                                        </div>
                                                    </td>
                                                    <td>
                                                        @foreach($question['options'] as $index => $option)
                                                            <span class="badge {{ $index === $question['correct_option'] ? 'bg-success' : 'bg-light text-dark' }} me-1">
                                                                {{ $option['label'] ?? chr(65 + $index) }}: {{ Str::limit($option['text'], 20) }}
                                                            </span>
                                                        @endforeach
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-success">
                                                            {{ $question['options'][$question['correct_option']]['label'] ?? chr(65 + $question['correct_option']) }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $question['marks'] }}</td>
                                                    <td>
                                                        @if($question['is_valid'])
                                                            <i class="bi bi-check-circle text-success"></i>
                                                        @else
                                                            <i class="bi bi-exclamation-triangle text-warning"></i>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                @if(count($previewData) === 20)
                                    <div class="text-muted small mt-2">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Showing first 20 questions. All questions will be imported when confirmed.
                                    </div>
                                @endif
                            @endif

                            <!-- Import Actions -->
                            <div class="d-flex justify-content-between mt-3">
                                <button type="button" class="btn btn-outline-secondary" wire:click="resetPreview">
                                    <i class="bi bi-arrow-left me-1"></i>Back to Upload
                                </button>
                                
                                @if(!empty($previewData) && empty($validationErrors))
                                    <button type="button" class="btn btn-success" 
                                            wire:click="import" wire:loading.attr="disabled" wire:target="import">
                                        <span wire:loading.remove wire:target="import">
                                            <i class="bi bi-check-lg me-1"></i>Confirm Import
                                        </span>
                                        <span wire:loading wire:target="import">
                                            <i class="bi bi-hourglass-split me-1"></i>Importing...
                                        </span>
                                    </button>
                                @else
                                    <button type="button" class="btn btn-success" disabled>
                                        <i class="bi bi-exclamation-triangle me-1"></i>Fix Errors First
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Import Results -->
                    @if($importResults)
                        <div class="alert alert-success">
                            <h6 class="alert-heading">
                                <i class="bi bi-check-circle me-2"></i>Import Completed
                            </h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-check-circle text-success me-2"></i>
                                        <span><strong>{{ $importResults['imported'] }}</strong> questions imported</span>
                                    </div>
                                </div>
                                @if($importResults['failed'] > 0)
                                    <div class="col-md-4">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-x-circle text-danger me-2"></i>
                                            <span><strong>{{ $importResults['failed'] }}</strong> questions failed</span>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            
                            @if(!empty($importResults['errors']))
                                <hr>
                                <h6>Import Errors:</h6>
                                <ul class="mb-0">
                                    @foreach($importResults['errors'] as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Import Guide -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-lightbulb me-2"></i>Quick Guide
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="fw-bold">Steps to Import:</h6>
                        <ol class="small">
                            <li>Select your file (Excel, CSV, or Aiken format)</li>
                            <li>Choose the appropriate format</li>
                            <li>Click "Preview Import" to validate</li>
                            <li>Review the preview for errors</li>
                            <li>Click "Confirm Import" to proceed</li>
                        </ol>
                    </div>

                    <div class="mb-3">
                        <h6 class="fw-bold">File Requirements:</h6>
                        <ul class="small">
                            <li>Maximum file size: 10MB</li>
                            <li>Questions must have at least 2 options</li>
                            <li>Correct answer must be specified</li>
                            <li>Question text cannot be empty</li>
                        </ul>
                    </div>

                    <div class="alert alert-info small">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Need help?</strong> Check the format guide below for detailed examples and requirements.
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-lightning me-2"></i>Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('question.sets.questions.create', $questionSetId) }}" class="btn btn-outline-primary">
                            <i class="bi bi-plus-circle me-1"></i>Add Single Question
                        </a>
                        <a href="{{ route('question.sets.questions', $questionSetId) }}" class="btn btn-outline-secondary">
                            <i class="bi bi-list me-1"></i>View All Questions
                        </a>
                        <a href="{{ route('question.sets.show', $questionSetId) }}" class="btn btn-outline-info">
                            <i class="bi bi-eye me-1"></i>Question Set Details
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div wire:loading wire:target="preview,import" class="position-fixed top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center" 
         style="background-color: rgba(0,0,0,0.5); z-index: 9999;">
        <div class="bg-white p-4 rounded shadow">
            <div class="text-center">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <div>
                    <span wire:loading wire:target="preview">Processing file...</span>
                    <span wire:loading wire:target="import">Importing questions...</span>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>