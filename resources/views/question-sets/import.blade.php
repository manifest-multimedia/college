<x-dashboard.default>
    <x-slot name="title">
        Import Questions
    </x-slot>

    <div class="container-fluid">
        <!-- Question Set Info -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="mb-1">{{ $questionSet->name }}</h4>
                                <p class="text-muted mb-0">
                                    Course: {{ $questionSet->course->name }} | 
                                    Current Questions: {{ $questionsCount }}
                                </p>
                            </div>
                            <div>
                                <a href="{{ route('question.sets') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Back to Question Sets
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Breadcrumb Navigation -->
        <div class="row">
            <div class="col-12">
                <nav aria-label="breadcrumb" class="mb-4">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}" class="text-decoration-none">
                                <i class="bi bi-house-door me-1"></i>Dashboard
                            </a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('question.sets') }}" class="text-decoration-none">
                                <i class="bi bi-collection me-1"></i>Question Sets
                            </a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">
                            <i class="bi bi-upload me-1"></i>Import Questions
                        </li>
                    </ol>
                </nav>
            </div>
        </div>

        <!-- Page Header -->
        <div class="row">
            <div class="col-12">
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="h3 mb-0 text-gray-800">
                                <i class="bi bi-upload me-2"></i>Import Questions
                            </h1>
                            <p class="text-muted mb-0">Bulk import questions from Excel/CSV or Aiken format files</p>
                        </div>
                        <div>
                            <a href="{{ route('question.sets') }}" class="btn btn-outline-secondary me-2">
                                <i class="bi bi-arrow-left me-1"></i>Back to Question Sets
                            </a>
                            <a href="{{ route('question.sets.questions.create', $questionSetId) }}" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-1"></i>Add Single Question
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Import Component -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-upload me-2"></i>Import Questions
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Import Form -->
                        <form id="importForm" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="import_file" class="form-label">Select File</label>
                                        <input type="file" 
                                               class="form-control" 
                                               id="import_file" 
                                               name="import_file" 
                                               accept=".xlsx,.xls,.csv,.txt" 
                                               required>
                                        <div class="form-text">
                                            Supported formats: Excel (.xlsx, .xls), CSV (.csv), Aiken (.txt)
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="format" class="form-label">File Format</label>
                                        <select class="form-control" id="format" name="format" required>
                                            <option value="">Select format</option>
                                            <option value="excel">Excel/CSV</option>
                                            <option value="aiken">Aiken Format</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-12">
                                    <button type="button" id="preview-btn" class="btn btn-info me-2">
                                        <i class="bi bi-eye me-1"></i> Preview Import
                                    </button>
                                    <button type="button" id="import-btn" class="btn btn-success" style="display: none;">
                                        <i class="bi bi-upload me-1"></i> Import Questions
                                    </button>
                                </div>
                            </div>
                        </form>
                        
                        <!-- Loading Indicator -->
                        <div id="loadingIndicator" class="text-center mt-4" style="display: none;">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Processing file...</p>
                        </div>
                        
                        <!-- Status Messages -->
                        <div id="statusContainer" class="mt-3" style="display: none;">
                            <div class="alert alert-info">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <span id="statusMessage">Ready to upload</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Column Mapping (Excel only) -->
                        <div id="columnMappingContainer" class="mt-4" style="display: none;">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">
                                        <i class="fas fa-columns me-2"></i>Column Mapping
                                    </h6>
                                    <small class="text-muted">Map your Excel columns to the required question fields</small>
                                </div>
                                <div class="card-body">
                                    <div id="columnMappingContent">
                                        <!-- Column mapping interface will be loaded here -->
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Error Messages -->
                        <div id="errorContainer" class="alert alert-danger mt-4" style="display: none;">
                            <ul id="errorList" class="mb-0"></ul>
                        </div>
                        
                        <!-- Success Messages -->
                        <div id="successContainer" class="alert alert-success mt-4" style="display: none;">
                            <p id="successMessage" class="mb-0"></p>
                        </div>
                        
                        <!-- Preview Container -->
                        <div id="previewContainer" class="mt-4" style="display: none;">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Import Preview</h6>
                                </div>
                                <div class="card-body">
                                    <div id="previewContent">
                                        <!-- Preview content will be loaded here -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Format Guide -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-info-circle me-2"></i>Supported Import Formats
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Excel/CSV Format -->
                            <div class="col-md-6">
                                <h6 class="fw-bold">Excel/CSV Format</h6>
                                <p class="text-muted mb-2">Upload Excel (.xlsx, .xls) or CSV files with the following columns:</p>
                                <ul class="small">
                                    <li><strong>question</strong> - The question text</li>
                                    <li><strong>option_one</strong> - First option (A)</li>
                                    <li><strong>option_two</strong> - Second option (B)</li>
                                    <li><strong>option_three</strong> - Third option (C)</li>
                                    <li><strong>option_four</strong> - Fourth option (D)</li>
                                    <li><strong>correct_option</strong> - Correct answer (option_one, option_two, etc.)</li>
                                    <li><strong>marks</strong> - Points for the question (optional, default: 1)</li>
                                    <li><strong>explanation</strong> - Answer explanation (optional)</li>
                                    <li><strong>exam_section</strong> - Section/category (optional)</li>
                                </ul>
                            </div>
                            
                            <!-- Aiken Format -->
                            <div class="col-md-6">
                                <h6 class="fw-bold">Aiken Format</h6>
                                <p class="text-muted mb-2">Upload plain text files (.txt) with Aiken format:</p>
                                <div class="bg-light p-3 rounded">
                                    <pre class="mb-0 small">What is the capital of France?
A. London
B. Paris
C. Berlin
D. Madrid
ANSWER: B
FEEDBACK: Paris is the capital city of France.

What is 2 + 2?
A. 3
B. 4
C. 5
D. 6
ANSWER: B</pre>
                                </div>
                                <ul class="small mt-2">
                                    <li>Question text on first line</li>
                                    <li>Options labeled A., B., C., D.</li>
                                    <li>ANSWER: [letter] indicates correct answer</li>
                                    <li>FEEDBACK: [text] is optional</li>
                                    <li>Blank line separates questions</li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="alert alert-info mt-3">
                            <i class="bi bi-lightbulb me-2"></i>
                            <strong>Tips:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Ensure your file has a header row (Excel/CSV) or proper format (Aiken)</li>
                                <li>Preview your import before confirming to check for errors</li>
                                <li>Maximum file size: 10MB</li>
                                <li>For large imports, consider breaking them into smaller files</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <!-- Ensure jQuery is loaded -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    // Ensure jQuery is available
    if (typeof $ === 'undefined') {
        console.error('jQuery is not loaded!');
        alert('jQuery is required for this page to function. Please refresh the page.');
    } else {
        $(document).ready(function() {
        const importForm = $('#importForm');
        const previewBtn = $('#preview-btn');
        const importBtn = $('#import-btn');
        const loadingIndicator = $('#loadingIndicator');
        const errorContainer = $('#errorContainer');
        const successContainer = $('#successContainer');
        const previewContainer = $('#previewContainer');
        
        // Debug message
        console.log('Question import page loaded successfully');
        showStatusMessage('Ready to import questions. Please select a file and format.', 'info');
        
        // Real-time validation feedback
        $('#import_file').on('change', function() {
            const file = this.files[0];
            const format = $('#format').val();
            
            if (file) {
                const fileSize = (file.size / (1024 * 1024)).toFixed(2);
                const statusMsg = `File selected: ${file.name} (${fileSize} MB)`;
                showStatusMessage(statusMsg, 'info');
                
                // Remove previous validation classes
                $(this).removeClass('is-invalid is-valid');
                
                // Validate file
                if (file.size > 10 * 1024 * 1024) {
                    $(this).addClass('is-invalid');
                    showError(['File size exceeds 10MB limit']);
                } else {
                    $(this).addClass('is-valid');
                    hideMessages();
                    
                    // Show column mapping for Excel files
                    if (format === 'excel' && (file.name.toLowerCase().endsWith('.xlsx') || file.name.toLowerCase().endsWith('.xls') || file.name.toLowerCase().endsWith('.csv'))) {
                        detectColumns();
                    } else {
                        $('#columnMappingContainer').hide();
                    }
                }
            } else {
                hideStatusMessage();
                $(this).removeClass('is-invalid is-valid');
                $('#columnMappingContainer').hide();
            }
        });
        
        $('#format').on('change', function() {
            const format = $(this).val();
            const file = $('#import_file')[0].files[0];
            
            if (format) {
                $(this).removeClass('is-invalid').addClass('is-valid');
                showStatusMessage(`Format selected: ${format.toUpperCase()}`, 'info');
                
                // Show column mapping for Excel files when format changes
                if (format === 'excel' && file && (file.name.toLowerCase().endsWith('.xlsx') || file.name.toLowerCase().endsWith('.xls') || file.name.toLowerCase().endsWith('.csv'))) {
                    detectColumns();
                } else {
                    $('#columnMappingContainer').hide();
                }
            } else {
                $(this).removeClass('is-invalid is-valid');
                hideStatusMessage();
                $('#columnMappingContainer').hide();
            }
        });
        
                // Preview functionality
        $('#preview-btn').on('click', function(e) {
            e.preventDefault();
            
            console.log('Preview button clicked');
            
            if (!validateForm()) {
                console.log('Form validation failed');
                showError(['Please fix the validation errors above.']);
                return false;
            }

            console.log('Form validation passed');
            
            const format = $('#format').val();
            
            // For Excel files, require column mapping first
            if (format === 'excel') {
                if (!$('#columnMappingContainer').is(':visible')) {
                    console.log('Excel file detected, showing column mapping first');
                    detectColumns();
                    showStatusMessage('Please map the Excel columns below, then click "Apply Mapping & Preview Questions".', 'info');
                    return false;
                }
                
                if (!window.currentMapping) {
                    showError(['Please map the Excel columns first by clicking "Apply Mapping & Preview Questions".']);
                    return false;
                }
            }
            
            // Continue with normal preview for Aiken files or mapped Excel files

            const formData = new FormData();
            const fileInput = document.getElementById('import_file');
            formData.append('import_file', fileInput.files[0]);
            formData.append('format', $('#format').val());
            formData.append('_token', '{{ csrf_token() }}');

            console.log('FormData prepared:', {
                file: fileInput.files[0] ? fileInput.files[0].name : 'No file',
                format: $('#format').val(),
                url: '{{ route('question.sets.import.preview', $questionSetId) }}'
            });

            $.ajax({
                url: '{{ route('question.sets.import.preview', $questionSetId) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    console.log('AJAX request starting...');
                    hideMessages(); // Clear any previous messages
                    $('#preview-btn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Previewing...');
                    $('#previewContainer').hide(); // Hide previous preview
                },
                success: function(response) {
                    console.log('AJAX success:', response);
                    if (response.success) {
                        if (response.preview && response.preview.length > 0) {
                            // Structure data for displayPreview function
                            const previewData = {
                                questions: response.preview,
                                errors: response.errors || [],
                                total_questions: response.total || response.preview.length,
                                valid_questions: response.preview.length,
                                error_count: (response.errors || []).length
                            };
                            
                            displayPreview(previewData);
                            $('#import-btn').prop('disabled', false).show();
                            showSuccess('Preview generated successfully! ' + response.total + ' questions found.');
                        } else {
                            showError(['No questions found in the file. Please check the file format and content.']);
                        }
                        
                        // Show any parsing errors (but don't override success message)
                        if (response.errors && response.errors.length > 0) {
                            // Show errors in a warning instead of error alert
                            const errorHtml = '<div class="alert alert-warning mt-3"><h6>Validation Issues Found:</h6><ul class="mb-0">' +
                                response.errors.slice(0, 15).map(error => '<li>' + error + '</li>').join('') +
                                (response.errors.length > 15 ? '<li>... and ' + (response.errors.length - 15) + ' more issues</li>' : '') +
                                '</ul><small class="text-muted">Only valid questions will be imported.</small></div>';
                            $('#previewContainer .card-body').append(errorHtml);
                        }
                    } else {
                        showError([response.message || 'Preview failed for unknown reason']);
                    }
                },
                error: function(xhr) {
                    console.log('AJAX error:', xhr);
                    let errorMessage = 'An error occurred';
                    
                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.error) {
                            errorMessage = xhr.responseJSON.error;
                        } else if (xhr.responseJSON.errors) {
                            // Handle Laravel validation errors
                            const errors = xhr.responseJSON.errors;
                            const errorMessages = [];
                            Object.keys(errors).forEach(function(field) {
                                errors[field].forEach(function(message) {
                                    errorMessages.push(message);
                                });
                            });
                            showError(errorMessages);
                            return;
                        } else if (xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                    } else if (xhr.status === 500) {
                        errorMessage = 'Server error occurred. Please check the server logs.';
                    } else if (xhr.status === 404) {
                        errorMessage = 'Request endpoint not found.';
                    } else if (xhr.status === 422) {
                        errorMessage = 'Validation failed. Please check your file and format.';
                    }
                    
                    showError([errorMessage]);
                },
                complete: function() {
                    console.log('AJAX request completed');
                    $('#preview-btn').prop('disabled', false).html('<i class="fas fa-eye"></i> Preview');
                }
            });
        });
        
        // Import button click
        importBtn.click(function(e) {
            e.preventDefault();
            
            if (!validateForm()) {
                return;
            }
            
            const formData = new FormData(importForm[0]);
            
            showLoading();
            hideMessages();
            
            $.ajax({
                url: '{{ route('question.sets.import.process', $questionSetId) }}',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    hideLoading();
                    
                    if (response.success) {
                        showSuccess(response.message);
                        
                        // Reset form and hide preview
                        importForm[0].reset();
                        previewContainer.hide();
                        importBtn.hide();
                        
                        // Optional: Redirect after delay
                        setTimeout(function() {
                            window.location.href = '{{ route('question.sets') }}';
                        }, 2000);
                    } else {
                        showError([response.message || 'Import failed']);
                    }
                },
                error: function(xhr) {
                    hideLoading();
                    
                    if (xhr.status === 422) {
                        // Validation errors
                        const errors = xhr.responseJSON.errors;
                        const errorMessages = [];
                        
                        Object.keys(errors).forEach(function(key) {
                            errors[key].forEach(function(message) {
                                errorMessages.push(message);
                            });
                        });
                        
                        showError(errorMessages);
                    } else {
                        showError(['An error occurred during import']);
                    }
                }
            });
        });
        
        // Apply column mapping and preview
        $(document).on('click', '#apply-mapping-btn', function(e) {
            e.preventDefault();
            
            // Get column mapping
            const mapping = {};
            $('.column-mapping').each(function() {
                const field = $(this).data('field');
                const columnIndex = $(this).val();
                if (columnIndex !== '') {
                    mapping[field] = parseInt(columnIndex);
                }
            });
            
            // Validate required fields
            const requiredFields = ['question', 'option_one', 'option_two', 'correct_option'];
            const missingFields = requiredFields.filter(field => !mapping[field] && mapping[field] !== 0);
            
            if (missingFields.length > 0) {
                showError(['Please map the required fields: ' + missingFields.join(', ')]);
                return;
            }
            
            // Store mapping and preview with mapping
            window.currentMapping = mapping;
            previewWithMapping(mapping);
        });
        
        function previewWithMapping(mapping) {
            console.log('Column mapping being sent:', mapping);
            
            const formData = new FormData();
            const fileInput = document.getElementById('import_file');
            formData.append('import_file', fileInput.files[0]);
            formData.append('format', $('#format').val());
            formData.append('column_mapping', JSON.stringify(mapping));
            formData.append('_token', '{{ csrf_token() }}');

            $.ajax({
                url: '{{ route('question.sets.import.preview', $questionSetId) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    $('#apply-mapping-btn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Previewing...');
                },
                success: function(response) {
                    if (response.success) {
                        const previewData = {
                            questions: response.preview,
                            errors: response.errors || [],
                            total_questions: response.total || response.preview.length,
                            valid_questions: response.preview.length,
                            error_count: (response.errors || []).length
                        };
                        
                        displayPreview(previewData);
                        showSuccess('Preview generated successfully with column mapping! ' + response.total + ' questions found.');
                    } else {
                        showError([response.message || 'Preview failed']);
                    }
                },
                error: function(xhr) {
                    console.log('Mapping preview error:', xhr);
                    let errorMessage = 'An error occurred';
                    
                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.error) {
                            errorMessage = xhr.responseJSON.error;
                        } else if (xhr.responseJSON.errors) {
                            const errors = xhr.responseJSON.errors;
                            const errorMessages = [];
                            Object.keys(errors).forEach(function(field) {
                                errors[field].forEach(function(message) {
                                    errorMessages.push(message);
                                });
                            });
                            showError(errorMessages);
                            return;
                        }
                    }
                    
                    showError([errorMessage]);
                },
                complete: function() {
                    $('#apply-mapping-btn').prop('disabled', false).html('<i class="fas fa-check"></i> Apply Mapping & Preview Questions');
                }
            });
        }
        
        // Dynamic import button handler (for button in preview)
        $(document).on('click', '#proceed-import-btn', function(e) {
            e.preventDefault();
            
            console.log('Proceed import button clicked');
            
            if (!validateForm()) {
                return;
            }
            
            const formData = new FormData(importForm[0]);
            
            // Add column mapping if available
            if (window.currentMapping) {
                formData.append('column_mapping', JSON.stringify(window.currentMapping));
            }
            
            showLoading();
            hideMessages();
            $('#proceed-import-btn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Importing...');
            
            $.ajax({
                url: '{{ route('question.sets.import.process', $questionSetId) }}',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    hideLoading();
                    
                    if (response.success) {
                        const message = `Successfully imported ${response.imported} questions! ${response.failed > 0 ? `(${response.failed} failed)` : ''}`;
                        showSuccess(message);
                        
                        // Reset form and hide preview
                        importForm[0].reset();
                        previewContainer.hide();
                        $('#proceed-import-btn').remove();
                        
                        // Optional: Redirect after delay
                        setTimeout(function() {
                            window.location.href = '{{ route('question.sets') }}';
                        }, 3000);
                    } else {
                        showError([response.message || 'Import failed']);
                        $('#proceed-import-btn').prop('disabled', false).html('<i class="fas fa-upload"></i> Import These Questions');
                    }
                },
                error: function(xhr) {
                    hideLoading();
                    $('#proceed-import-btn').prop('disabled', false).html('<i class="fas fa-upload"></i> Import These Questions');
                    
                    if (xhr.status === 422) {
                        // Validation errors
                        const errors = xhr.responseJSON.errors;
                        const errorMessages = [];
                        
                        Object.keys(errors).forEach(function(key) {
                            errors[key].forEach(function(message) {
                                errorMessages.push(message);
                            });
                        });
                        
                        showError(errorMessages);
                    } else {
                        showError(['An error occurred during import']);
                    }
                }
            });
        });
        
        function validateForm() {
            const file = $('#import_file')[0].files[0];
            const format = $('#format').val();
            const errors = [];
            
            // Clear previous status
            hideMessages();
            
            if (!file) {
                errors.push('Please select a file to import');
                $('#import_file').addClass('is-invalid');
            } else {
                $('#import_file').removeClass('is-invalid').addClass('is-valid');
                
                // Validate file size (10MB limit)
                if (file.size > 10 * 1024 * 1024) {
                    errors.push('File size must be less than 10MB');
                    $('#import_file').addClass('is-invalid');
                }
                
                // Validate file type
                const validExtensions = ['.xlsx', '.xls', '.csv', '.txt'];
                const fileName = file.name.toLowerCase();
                const isValidType = validExtensions.some(ext => fileName.endsWith(ext));
                
                if (!isValidType) {
                    errors.push('Invalid file type. Please select Excel (.xlsx, .xls), CSV (.csv), or Aiken (.txt) files only');
                    $('#import_file').addClass('is-invalid');
                }
            }
            
            if (!format) {
                errors.push('Please select a file format');
                $('#format').addClass('is-invalid');
            } else {
                $('#format').removeClass('is-invalid').addClass('is-valid');
            }
            
            if (errors.length > 0) {
                showError(errors);
                return false;
            }
            
            return true;
        }
        
        function showLoading() {
            loadingIndicator.show();
            previewBtn.prop('disabled', true);
            importBtn.prop('disabled', true);
        }
        
        function hideLoading() {
            loadingIndicator.hide();
            previewBtn.prop('disabled', false);
            importBtn.prop('disabled', false);
        }
        
        function showError(messages) {
            const errorList = $('#errorList');
            errorList.empty();
            
            messages.forEach(function(message) {
                errorList.append('<li>' + message + '</li>');
            });
            
            errorContainer.show();
            successContainer.hide();
        }
        
        function showSuccess(message) {
            $('#successMessage').text(message);
            successContainer.show();
            errorContainer.hide();
        }
        
        function hideMessages() {
            errorContainer.hide();
            successContainer.hide();
        }
        
        function showStatusMessage(message, type = 'info') {
            const statusContainer = $('#statusContainer');
            const statusMessage = $('#statusMessage');
            const alertClass = type === 'info' ? 'alert-info' : (type === 'warning' ? 'alert-warning' : 'alert-success');
            
            statusContainer.find('.alert').removeClass('alert-info alert-warning alert-success').addClass(alertClass);
            statusMessage.text(message);
            statusContainer.show();
        }
        
        function hideStatusMessage() {
            $('#statusContainer').hide();
        }
        
        function detectColumns() {
            const file = $('#import_file')[0].files[0];
            if (!file) {
                console.log('No file selected for column detection');
                return;
            }
            
            console.log('Detecting columns for file:', file.name);
            
            const formData = new FormData();
            formData.append('import_file', file);
            formData.append('_token', '{{ csrf_token() }}');
            
            // Show loading state
            showStatusMessage('Detecting Excel columns...', 'info');
            
            $.ajax({
                url: '{{ route('question.sets.import.columns', $questionSetId) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    console.log('Column detection AJAX request starting...');
                },
                success: function(response) {
                    console.log('Column detection response:', response);
                    if (response.success) {
                        displayColumnMapping(response.columns, response.sample_data);
                        showStatusMessage('Columns detected successfully. Please map the fields below.', 'info');
                    } else {
                        showError(['Failed to detect columns: ' + (response.message || 'Unknown error')]);
                    }
                },
                error: function(xhr) {
                    console.log('Column detection error:', xhr);
                    const errorMsg = xhr.responseJSON ? 
                        (xhr.responseJSON.error || xhr.responseJSON.message || 'Unknown server error') :
                        'Could not detect columns in the file';
                    showError([errorMsg]);
                }
            });
        }
        
        function displayColumnMapping(columns, sampleData) {
            const requiredFields = [
                { key: 'question', label: 'Question Text', required: true, description: 'The main question text' },
                { key: 'option_one', label: 'Option A', required: true, description: 'First answer choice' },
                { key: 'option_two', label: 'Option B', required: true, description: 'Second answer choice' },
                { key: 'option_three', label: 'Option C', required: false, description: 'Third answer choice (optional)' },
                { key: 'option_four', label: 'Option D', required: false, description: 'Fourth answer choice (optional)' },
                { key: 'correct_option', label: 'Correct Answer', required: true, description: 'The correct option (A, B, C, D or option text)' },
                { key: 'marks', label: 'Marks/Points', required: false, description: 'Points for this question (default: 1)' },
                { key: 'explanation', label: 'Explanation', required: false, description: 'Answer explanation (optional)' },
                { key: 'exam_section', label: 'Section/Category', required: false, description: 'Question category (optional)' }
            ];
            
            let html = '<div class="row">';
            html += '<div class="col-md-8">';
            html += '<h6 class="mb-3">Map Excel Columns to Question Fields</h6>';
            html += '<div class="table-responsive">';
            html += '<table class="table table-sm">';
            html += '<thead><tr><th>Required Field</th><th>Excel Column</th><th>Sample Data</th></tr></thead>';
            html += '<tbody>';
            
            requiredFields.forEach(function(field) {
                const requiredBadge = field.required ? '<span class="badge bg-danger ms-1">Required</span>' : '<span class="badge bg-secondary ms-1">Optional</span>';
                
                html += '<tr>';
                html += '<td><strong>' + field.label + '</strong>' + requiredBadge + '<br><small class="text-muted">' + field.description + '</small></td>';
                html += '<td><select class="form-select form-select-sm column-mapping" data-field="' + field.key + '">';
                html += '<option value="">-- Select Column --</option>';
                
                columns.forEach(function(column) {
                    const selected = autoMapColumn(field.key, column.name) ? 'selected' : '';
                    html += '<option value="' + column.index + '" ' + selected + '>' + column.name + '</option>';
                });
                
                html += '</select></td>';
                html += '<td><div id="sample-' + field.key + '" class="text-muted"><small>Select a column to see samples</small></div></td>';
                html += '</tr>';
            });
            
            html += '</tbody></table>';
            html += '</div></div>';
            
            // Sample data preview
            html += '<div class="col-md-4">';
            html += '<h6 class="mb-3">File Preview (First 3 Rows)</h6>';
            html += '<div class="table-responsive">';
            html += '<table class="table table-sm table-bordered">';
            html += '<thead><tr>';
            columns.forEach(function(col) {
                html += '<th class="text-truncate" style="max-width: 100px;">' + col.name + '</th>';
            });
            html += '</tr></thead><tbody>';
            
            sampleData.slice(0, 3).forEach(function(row) {
                html += '<tr>';
                columns.forEach(function(col) {
                    const cellValue = row[col.index] || '';
                    html += '<td class="text-truncate" style="max-width: 100px;" title="' + cellValue + '">' + cellValue + '</td>';
                });
                html += '</tr>';
            });
            
            html += '</tbody></table></div></div>';
            html += '</div>';
            
            html += '<div class="mt-3 text-center">';
            html += '<button type="button" id="apply-mapping-btn" class="btn btn-primary">';
            html += '<i class="fas fa-check me-2"></i>Apply Mapping & Preview Questions';
            html += '</button>';
            html += '</div>';
            
            $('#columnMappingContent').html(html);
            $('#columnMappingContainer').show();
            
            // Store columns data for later use
            window.columnsData = columns;
            
            // Add event listeners for column mapping changes
            $('.column-mapping').on('change', function() {
                updateSamplePreview($(this).data('field'), $(this).val());
            });
            
            // Auto-update sample previews
            $('.column-mapping').each(function() {
                if ($(this).val()) {
                    updateSamplePreview($(this).data('field'), $(this).val());
                }
            });
        }
        
        function autoMapColumn(fieldKey, columnName) {
            const mapping = {
                'question': ['question', 'question_text', 'q', 'text'],
                'option_one': ['option_one', 'option1', 'a', 'choice_a', 'option_a'],
                'option_two': ['option_two', 'option2', 'b', 'choice_b', 'option_b'],
                'option_three': ['option_three', 'option3', 'c', 'choice_c', 'option_c'],
                'option_four': ['option_four', 'option4', 'd', 'choice_d', 'option_d'],
                'correct_option': ['correct_option', 'correct', 'answer', 'correct_answer'],
                'marks': ['marks', 'points', 'score', 'weight'],
                'explanation': ['explanation', 'feedback', 'reason'],
                'exam_section': ['section', 'category', 'topic', 'exam_section']
            };
            
            const keywords = mapping[fieldKey] || [];
            const columnLower = columnName.toLowerCase();
            
            return keywords.some(keyword => columnLower.includes(keyword));
        }
        
        function updateSamplePreview(fieldKey, columnIndex) {
            if (!columnIndex || !window.columnsData) return;
            
            const column = window.columnsData.find(col => col.index == columnIndex);
            if (column && column.samples) {
                const samplesHtml = column.samples.map(sample => 
                    '<div class="border rounded p-1 mb-1 bg-light"><small>' + sample + '</small></div>'
                ).join('');
                $('#sample-' + fieldKey).html(samplesHtml || '<small class="text-muted">No samples</small>');
            }
        }
        
        function displayPreview(data) {
            let html = '<div class="row">';
            
            // Summary
            html += '<div class="col-md-4">';
            html += '<div class="card">';
            html += '<div class="card-body">';
            html += '<h6 class="card-title">Import Summary</h6>';
            html += '<p><strong>Total Questions:</strong> ' + (data.total_questions || 0) + '</p>';
            html += '<p><strong>Valid Questions:</strong> ' + (data.valid_questions || 0) + '</p>';
            if (data.errors && data.errors.length > 0) {
                html += '<p class="text-warning"><strong>Errors:</strong> ' + data.errors.length + '</p>';
            } else {
                html += '<p class="text-success"><strong>Errors:</strong> 0</p>';
            }
            html += '</div></div></div>';
            
            // Sample Questions
            html += '<div class="col-md-8">';
            html += '<div class="card">';
            html += '<div class="card-body">';
            html += '<h6 class="card-title">Sample Questions (First 5)</h6>';
            
            if (data.questions && data.questions.length > 0) {
                data.questions.slice(0, 5).forEach(function(question, index) {
                    html += '<div class="mb-3 p-3 border rounded">';
                    html += '<strong>Q' + (index + 1) + ':</strong> ' + question.question_text;
                    
                    if (question.options && question.options.length > 0) {
                        html += '<ul class="mt-2 mb-1">';
                        question.options.forEach(function(option, optIndex) {
                            const isCorrect = (optIndex === question.correct_option) ? ' <span class="badge bg-success ms-2">Correct Answer</span>' : '';
                            html += '<li class="mb-1"><strong>' + option.label + '.</strong> ' + option.text + isCorrect + '</li>';
                        });
                        html += '</ul>';
                    }
                    
                    if (question.marks) {
                        html += '<small class="text-muted">Marks: ' + question.marks + '</small>';
                    }
                    html += '</div>';
                });
            } else {
                html += '<div class="text-center text-muted py-4">';
                html += '<i class="fas fa-question-circle fa-2x mb-2 d-block"></i>';
                html += '<p>No valid questions found in the file.</p>';
                html += '</div>';
            }
            
            html += '</div></div></div>';
            html += '</div>';
            
            // Show errors if any
            if (data.errors && data.errors.length > 0) {
                html += '<div class="mt-3">';
                html += '<div class="alert alert-warning">';
                html += '<h6>Import Warnings:</h6>';
                html += '<ul>';
                data.errors.slice(0, 5).forEach(function(error) {
                    html += '<li>' + error + '</li>';
                });
                if (data.errors.length > 5) {
                    html += '<li>... and ' + (data.errors.length - 5) + ' more issues</li>';
                }
                html += '</ul>';
                html += '</div></div>';
            }
            
            // Show column mapping summary if available
            if (window.currentMapping) {
                html += '<div class="mt-3">';
                html += '<div class="alert alert-info">';
                html += '<h6><i class="fas fa-info-circle me-2"></i>Column Mapping Applied</h6>';
                html += '<div class="row"><div class="col-md-6">';
                Object.keys(window.currentMapping).forEach(function(field) {
                    const columnIndex = window.currentMapping[field];
                    const columnName = window.columnsData ? (window.columnsData.find(col => col.index == columnIndex) || {}).name || 'Column ' + (columnIndex + 1) : 'Column ' + (columnIndex + 1);
                    html += '<small class="d-block"><strong>' + field.replace('_', ' ').toUpperCase() + ':</strong> ' + columnName + '</small>';
                });
                html += '</div></div></div></div>';
            }
            
            // Add Import Questions button to the preview
            html += '<div class="mt-4 text-center">';
            html += '<button type="button" id="proceed-import-btn" class="btn btn-success btn-lg">';
            html += '<i class="fas fa-upload me-2"></i>Import These Questions';
            html += '</button>';
            html += '</div>';
            
            $('#previewContent').html(html);
            previewContainer.show();
            
            // Enable the proceed import button
            $('#proceed-import-btn').prop('disabled', false);
        }
    });
    }
    </script>
    @endpush
</x-dashboard.default>