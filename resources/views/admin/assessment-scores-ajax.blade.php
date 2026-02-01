<x-dashboard.default title="Assessment Score Management">
    <div class="container-fluid py-4">
        
        {{-- Flash Messages --}}
        <div id="flash-message"></div>

        {{-- Filter Section --}}
        <div class="card mb-4">
            <div class="card-header pb-0">
                <h6 class="card-title">Assessment Score Management</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="program" class="form-label">Program <span class="text-danger">*</span></label>
                        <select id="program" class="form-select">
                            <option value="">Select Program</option>
                            @foreach($collegeClasses as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                        <span class="text-danger text-sm" id="program-error"></span>
                    </div>

                    <div class="col-md-3">
                        <label for="semester" class="form-label">Semester <span class="text-danger">*</span></label>
                        <select id="semester" class="form-select">
                            <option value="">Select Semester</option>
                            @foreach($semesters as $semester)
                                <option value="{{ $semester->id }}" @if($currentSemester && $currentSemester->id == $semester->id) selected @endif>
                                    {{ $semester->name }}
                                </option>
                            @endforeach
                        </select>
                        <span class="text-danger text-sm" id="semester-error"></span>
                    </div>

                    <div class="col-md-3">
                        <label for="course" class="form-label">Course <span class="text-danger">*</span></label>
                        <select id="course" class="form-select" disabled>
                            <option value="">Select program & semester first</option>
                        </select>
                        <span class="text-danger text-sm" id="course-error"></span>
                    </div>

                    <div class="col-md-3">
                        <label for="cohort" class="form-label">Cohort <span class="text-danger">*</span></label>
                        <select id="cohort" class="form-select">
                            <option value="">Select Cohort</option>
                            @foreach($cohorts as $cohort)
                                <option value="{{ $cohort->id }}" @if($currentCohort && $currentCohort->id == $cohort->id) selected @endif>
                                    {{ $cohort->name }}
                                </option>
                            @endforeach
                        </select>
                        <span class="text-danger text-sm" id="cohort-error"></span>
                    </div>
                </div>

                <div class="mt-3">
                    <button id="loadScoresheetBtn" class="btn btn-primary">
                        <i class="fas fa-table me-2"></i>Load Scoresheet
                    </button>

                    <button id="downloadTemplateBtn" class="btn btn-outline-success" disabled>
                        <i class="fas fa-download me-2"></i>Download Excel Template
                    </button>
                </div>
            </div>
        </div>

        {{-- Weight Configuration Display --}}
        <div id="weightConfigSection" class="card mb-4" style="display: none;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">Grading Weights</h6>
                        <div class="d-flex gap-4 flex-wrap">
                            <span class="badge bg-gradient-primary px-3 py-2">
                                Assignments (<span id="assignmentCountDisplay">3</span>): <strong><span id="assignmentWeightDisplay">20</span>%</strong>
                            </span>
                            <span class="badge bg-gradient-info px-3 py-2">
                                Mid-Semester: <strong><span id="midSemesterWeightDisplay">20</span>%</strong>
                            </span>
                            <span class="badge bg-gradient-success px-3 py-2">
                                End-Semester: <strong><span id="endSemesterWeightDisplay">60</span>%</strong>
                            </span>
                        </div>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <div class="btn-group" role="group">
                            <button id="removeAssignmentBtn" class="btn btn-sm btn-outline-danger" disabled title="Remove assignment column">
                                <i class="fas fa-minus"></i>
                            </button>
                            <button id="addAssignmentBtn" class="btn btn-sm btn-outline-success" title="Add assignment column">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                        <button id="configureWeightsBtn" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-cog me-1"></i>Configure Weights
                        </button>
                        <div class="input-group" style="width: auto;">
                            <input type="file" class="form-control form-control-sm" id="importFile" accept=".xlsx,.xls,.csv" style="display: none;">
                            <button id="selectImportFileBtn" class="btn btn-sm btn-outline-info">
                                <i class="fas fa-file-upload me-1"></i>Choose File
                            </button>
                            <button id="importExcelBtn" class="btn btn-sm btn-success" disabled>
                                <i class="fas fa-upload me-1"></i>Import from Excel
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Weight Configuration Form --}}
                <div id="weightConfigForm" class="mt-4 p-4 border rounded bg-light" style="display: none;">
                    <h6 class="mb-3">Configure Grading Weights</h6>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="assignmentWeight" class="form-label">Assignment Weight (%)</label>
                            <input type="number" id="assignmentWeight" class="form-control" min="0" max="100" step="0.01" value="{{ $defaultWeights['assignment'] }}">
                        </div>
                        <div class="col-md-4">
                            <label for="midSemesterWeight" class="form-label">Mid-Semester Weight (%)</label>
                            <input type="number" id="midSemesterWeight" class="form-control" min="0" max="100" step="0.01" value="{{ $defaultWeights['mid_semester'] }}">
                        </div>
                        <div class="col-md-4">
                            <label for="endSemesterWeight" class="form-label">End-Semester Weight (%)</label>
                            <input type="number" id="endSemesterWeight" class="form-control" min="0" max="100" step="0.01" value="{{ $defaultWeights['end_semester'] }}">
                        </div>
                    </div>
                    <div class="mt-3 d-flex gap-2">
                        <button id="saveWeightsBtn" class="btn btn-primary btn-sm">
                            <i class="fas fa-save me-1"></i>Save & Recalculate
                        </button>
                        <button id="cancelWeightsBtn" class="btn btn-secondary btn-sm">
                            <i class="fas fa-times me-1"></i>Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Statistics Summary --}}
        <div id="statsSection" class="row mb-4" style="display: none;">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center">
                            <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md">
                                <i class="fas fa-users text-lg opacity-10"></i>
                            </div>
                            <div class="ms-3">
                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Total Students</p>
                                <h5 class="font-weight-bolder mb-0" id="totalStudents">0</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center">
                            <div class="icon icon-shape bg-gradient-success shadow text-center border-radius-md">
                                <i class="fas fa-check-circle text-lg opacity-10"></i>
                            </div>
                            <div class="ms-3">
                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Passing</p>
                                <h5 class="font-weight-bolder mb-0" id="passingStudents">0</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center">
                            <div class="icon icon-shape bg-gradient-danger shadow text-center border-radius-md">
                                <i class="fas fa-exclamation-circle text-lg opacity-10"></i>
                            </div>
                            <div class="ms-3">
                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Failing</p>
                                <h5 class="font-weight-bolder mb-0" id="failingStudents">0</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center">
                            <div class="icon icon-shape bg-gradient-info shadow text-center border-radius-md">
                                <i class="fas fa-chart-line text-lg opacity-10"></i>
                            </div>
                            <div class="ms-3">
                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Class Average</p>
                                <h5 class="font-weight-bolder mb-0" id="classAverage">0%</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Import Preview Modal --}}
        <div class="modal fade" id="importPreviewModal" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Import Preview</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <strong>Summary:</strong>
                            <span id="importSummary"></span>
                        </div>
                        <div class="table-responsive" style="max-height: 500px;">
                            <table class="table table-bordered table-sm">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th>Student ID</th>
                                        <th>Student Name</th>
                                        <th>Action</th>
                                        <th>Assignment 1</th>
                                        <th>Assignment 2</th>
                                        <th>Assignment 3</th>
                                        <th>Assignment 4</th>
                                        <th>Assignment 5</th>
                                        <th>Mid-Semester</th>
                                        <th>End-Semester</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody id="importPreviewBody">
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-success" id="confirmImportBtn">
                            <i class="fas fa-check me-1"></i>Confirm Import
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Scoresheet Table --}}
        <div id="scoresheetSection" class="card" style="display: none;">
            <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                <h6 class="card-title">Assessment Scoresheet</h6>
                <div class="d-flex gap-2 align-items-center">
                    <label for="perPageSelect" class="mb-0 me-2">Show:</label>
                    <select id="perPageSelect" class="form-select form-select-sm me-3" style="width: auto;">
                        <option value="15" selected>15</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                        <option value="200">All</option>
                    </select>
                    <button id="saveScoresBtn" class="btn btn-success btn-sm">
                        <i class="fas fa-save me-1"></i>Save All Scores
                    </button>
                    <button id="exportExcelBtn" class="btn btn-info btn-sm">
                        <i class="fas fa-file-excel me-1"></i>Export to Excel
                    </button>
                </div>
            </div>
            <div class="card-body">
                <!-- Pagination Info -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div id="paginationInfo" class="text-muted">
                        <small>Showing <span id="recordsFrom">0</span> to <span id="recordsTo">0</span> of <span id="recordsTotal">0</span> students</small>
                    </div>
                    <nav id="paginationNav" aria-label="Students pagination">
                        <ul class="pagination pagination-sm mb-0" id="paginationList">
                            <!-- Pagination buttons will be inserted here -->
                        </ul>
                    </nav>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle" id="scoresheetTable">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th class="text-center" style="min-width: 50px; width: 50px;">#</th>
                                <th style="min-width: 120px; width: 120px;">Index No</th>
                                <th style="min-width: 200px; width: 250px;">Student Name</th>
                                <th class="text-center" style="min-width: 100px; width: 110px;">Assign 1</th>
                                <th class="text-center" style="min-width: 100px; width: 110px;">Assign 2</th>
                                <th class="text-center" style="min-width: 100px; width: 110px;">Assign 3</th>
                                <th class="text-center assignment-4-col" style="min-width: 100px; width: 110px; display: none;">Assign 4</th>
                                <th class="text-center assignment-5-col" style="min-width: 100px; width: 110px; display: none;">Assign 5</th>
                                <th class="text-center" style="min-width: 100px; width: 110px;">Mid-Sem</th>
                                <th class="text-center" style="min-width: 100px; width: 110px;">End-Sem</th>
                                <th class="text-center" style="min-width: 80px; width: 80px;">Total</th>
                                <th class="text-center" style="min-width: 60px; width: 60px;">Grade</th>
                            </tr>
                        </thead>
                        <tbody id="scoresheetBody">
                            <!-- Dynamic rows will be inserted here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    {{-- Loading Spinner Overlay --}}
    <div id="loadingSpinner" style="display: none;">
        <div class="spinner-overlay">
            <div class="spinner-content">
                <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <div class="mt-3 text-white">
                    <strong id="loadingMessage">Processing...</strong>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        .spinner-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
        .spinner-content {
            text-align: center;
        }
        
        /* Score input styling */
        .score-input {
            width: 100%;
            min-width: 80px;
            text-align: center;
            font-size: 0.95rem;
            font-weight: 500;
            padding: 0.5rem 0.75rem;
        }
        
        .score-input:invalid {
            border-color: #dc3545 !important;
        }
        
        .score-input:focus {
            font-size: 1rem;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }
        
        .field-error {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
        
        /* Table responsive with horizontal scroll */
        .table-responsive {
            position: relative;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        #scoresheetTable {
            width: max-content;
            min-width: 100%;
        }
        
        .sticky-top {
            position: sticky;
            top: 0;
            z-index: 10;
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        /* First two columns sticky on horizontal scroll */
        #scoresheetTable thead th:nth-child(1),
        #scoresheetTable thead th:nth-child(2),
        #scoresheetTable thead th:nth-child(3),
        #scoresheetTable tbody td:nth-child(1),
        #scoresheetTable tbody td:nth-child(2),
        #scoresheetTable tbody td:nth-child(3) {
            position: sticky;
            background: white;
            z-index: 5;
        }
        
        #scoresheetTable thead th:nth-child(1),
        #scoresheetTable tbody td:nth-child(1) {
            left: 0;
        }
        
        #scoresheetTable thead th:nth-child(2),
        #scoresheetTable tbody td:nth-child(2) {
            left: 50px;
        }
        
        #scoresheetTable thead th:nth-child(3),
        #scoresheetTable tbody td:nth-child(3) {
            left: 170px;
        }
        
        /* Add shadow to sticky columns */
        #scoresheetTable thead th:nth-child(3)::after,
        #scoresheetTable tbody td:nth-child(3)::after {
            content: '';
            position: absolute;
            top: 0;
            right: -10px;
            bottom: 0;
            width: 10px;
            background: linear-gradient(to right, rgba(0,0,0,0.05), transparent);
        }
        
        /* Alternating row colors for better readability */
        #scoresheetTable tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        #scoresheetTable tbody tr:hover {
            background-color: #e9ecef;
        }
        
        /* Total and grade cells styling */
        .total-cell {
            font-weight: 600;
            color: #0d6efd;
            font-size: 1rem;
        }
        
        .grade-cell {
            font-weight: 700;
            font-size: 1rem;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .score-input {
                font-size: 0.9rem;
                padding: 0.4rem 0.5rem;
            }
        }
    </style>
    @endpush

    @push('scripts')
    <script>
        let studentScores = [];
        let assignmentCount = 3;
        let importPreviewData = [];
        let weights = {
            assignment: {{ $defaultWeights['assignment'] }},
            mid_semester: {{ $defaultWeights['mid_semester'] }},
            end_semester: {{ $defaultWeights['end_semester'] }}
        };
        let filters = {
            class_id: null,
            course_id: null,
            cohort_id: '{{ $currentCohort?->id ?? '' }}',
            semester_id: '{{ $currentSemester?->id ?? '' }}'
        };
        
        // Pagination variables
        let pagination = {
            current_page: 1,
            last_page: 1,
            per_page: 15,
            total: 0,
            from: 0,
            to: 0
        };

        $(document).ready(function() {
            // Initialize
            initializeEventListeners();
            
            // Program change - reset course and check if we can load courses
            $('#program').on('change', function() {
                const classId = $(this).val();
                filters.class_id = classId;
                filters.course_id = null;
                $('#course').val('');
                
                if (classId && filters.semester_id) {
                    loadCourses(classId, filters.semester_id);
                } else {
                    $('#course').prop('disabled', true).html('<option value="">Select program & semester first</option>');
                    clearScoresheet();
                }
                updateTemplateButtonState();
            });

            // Semester change - reload courses if program is selected
            $('#semester').on('change', function() {
                const semesterId = $(this).val();
                filters.semester_id = semesterId;
                filters.course_id = null;
                $('#course').val('');
                
                if (semesterId && filters.class_id) {
                    loadCourses(filters.class_id, semesterId);
                } else if (!semesterId) {
                    $('#course').prop('disabled', true).html('<option value="">Select program & semester first</option>');
                    clearScoresheet();
                }
                updateTemplateButtonState();
            });

            // Course change
            $('#course').on('change', function() {
                filters.course_id = $(this).val();
                updateTemplateButtonState();
            });

            // Cohort change
            $('#cohort').on('change', function() {
                filters.cohort_id = $(this).val();
                updateTemplateButtonState();
            });

            // Load Scoresheet
            $('#loadScoresheetBtn').on('click', function() {
                loadScoresheet();
            });
            
            // Per page change
            $('#perPageSelect').on('change', function() {
                if (studentScores.length > 0) {
                    loadScoresheet(1); // Reload from page 1 with new per_page value
                }
            });

            // Download Template
            $('#downloadTemplateBtn').on('click', function() {
                downloadTemplate();
            });

            // Import functionality
            $('#selectImportFileBtn').on('click', function() {
                $('#importFile').click();
            });

            $('#importFile').on('change', function() {
                const fileName = $(this).val().split('\\').pop();
                if (fileName) {
                    $('#selectImportFileBtn').html(`<i class="fas fa-file-alt me-1"></i>${fileName}`);
                    $('#importExcelBtn').prop('disabled', false);
                } else {
                    $('#selectImportFileBtn').html('<i class="fas fa-file-upload me-1"></i>Choose File');
                    $('#importExcelBtn').prop('disabled', true);
                }
            });

            $('#importExcelBtn').on('click', function() {
                importExcel();
            });

            $('#confirmImportBtn').on('click', function() {
                confirmImport();
            });

            // Save Scores
            $('#saveScoresBtn').on('click', function() {
                saveScores();
            });

            // Export Excel
            $('#exportExcelBtn').on('click', function() {
                exportExcel();
            });

            // Keyboard shortcuts
            $(document).on('keydown', function(e) {
                // Ctrl+S to save
                if (e.ctrlKey && e.key === 's') {
                    e.preventDefault();
                    if (studentScores.length > 0) {
                        saveScores();
                    }
                }
                // Esc to close modals
                if (e.key === 'Escape') {
                    $('#importPreviewModal').modal('hide');
                    $('#weightConfigForm').hide();
                }
            });

            // Enter key navigation in score inputs
            $(document).on('keypress', '.score-input', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const currentRow = $(this).closest('tr');
                    const currentCell = $(this).closest('td');
                    const cellIndex = currentCell.index();
                    const nextRow = currentRow.next('tr');
                    
                    if (nextRow.length) {
                        const nextInput = nextRow.find('td').eq(cellIndex).find('.score-input');
                        if (nextInput.length) {
                            nextInput.focus().select();
                        }
                    }
                }
            });

            // Weight Configuration
            $('#configureWeightsBtn').on('click', function() {
                $('#weightConfigForm').toggle();
            });

            $('#cancelWeightsBtn').on('click', function() {
                $('#weightConfigForm').hide();
            });

            $('#saveWeightsBtn').on('click', function() {
                saveWeights();
            });

            // Assignment column management
            $('#addAssignmentBtn').on('click', function() {
                if (assignmentCount < 5) {
                    assignmentCount++;
                    updateAssignmentColumns();
                    recalculateAllScores();
                    showFlashMessage('Assignment ' + assignmentCount + ' column added', 'info');
                }
            });

            $('#removeAssignmentBtn').on('click', function() {
                if (assignmentCount > 3) {
                    assignmentCount--;
                    updateAssignmentColumns();
                    recalculateAllScores();
                    showFlashMessage('Assignment column removed and scores recalculated', 'info');
                }
            });

            // Track filter changes
            $('#course, #cohort, #semester, #academicYear').on('change', function() {
                const id = $(this).attr('id');
                filters[id === 'academicYear' ? 'academic_year' : id + '_id'] = $(this).val();
                updateTemplateButtonState();
            });
        });

        function initializeEventListeners() {
            // Input change for score calculations
            $(document).on('input', '.score-input', function() {
                const row = $(this).closest('tr');
                const index = row.data('index');
                calculateStudentTotal(index);
            });
        }

        // Loading spinner functions
        function showSpinner(message = 'Processing...') {
            $('#loadingMessage').text(message);
            $('#loadingSpinner').fadeIn(200);
        }

        function hideSpinner() {
            $('#loadingSpinner').fadeOut(200);
        }

        function loadCourses(classId, semesterId) {
            showSpinner('Loading courses...');
            $.ajax({
                url: '{{ route("admin.assessment-scores.get-courses") }}',
                method: 'GET',
                data: { 
                    class_id: classId,
                    semester_id: semesterId
                },
                success: function(response) {
                    if (response.success) {
                        let options = '<option value="">Select Course</option>';
                        response.courses.forEach(function(course) {
                            options += `<option value="${course.id}">${course.name}</option>`;
                        });
                        $('#course').prop('disabled', false).html(options);
                        
                        if (response.courses.length === 0) {
                            showFlashMessage('No courses found for this program and semester', 'info');
                        }
                    }
                },
                error: function(xhr) {
                    showFlashMessage('Failed to load courses', 'danger');
                },
                complete: function() {
                    hideSpinner();
                }
            });
        }

        function loadScoresheet(page = 1) {
            // Validate required fields
            if (!filters.class_id) {
                showFlashMessage('Please select a program', 'danger');
                return;
            }
            if (!filters.course_id) {
                filters.course_id = $('#course').val();
                if (!filters.course_id) {
                    showFlashMessage('Please select a course', 'danger');
                    return;
                }
            }
            if (!filters.cohort_id) {
                filters.cohort_id = $('#cohort').val();
                if (!filters.cohort_id) {
                    showFlashMessage('Please select a cohort', 'danger');
                    return;
                }
            }
            if (!filters.semester_id) {
                filters.semester_id = $('#semester').val();
                if (!filters.semester_id) {
                    showFlashMessage('Please select a semester', 'danger');
                    return;
                }
            }

            showSpinner('Loading scoresheet...');
            const btn = $('#loadScoresheetBtn');
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Loading...');
            
            const perPage = $('#perPageSelect').val() || 15;

            $.ajax({
                url: '{{ route("admin.assessment-scores.load-scoresheet") }}',
                method: 'POST',
                data: {
                    ...filters,
                    page: page,
                    per_page: perPage
                },
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        studentScores = response.students;
                        assignmentCount = response.assignment_count;
                        weights = response.weights;
                        pagination = response.pagination;
                        
                        renderScoresheet();
                        renderPagination();
                        updateWeightDisplay();
                        updateStatistics();
                        
                        $('#weightConfigSection, #statsSection, #scoresheetSection').show();
                        showFlashMessage(response.message, 'success');
                    }
                },
                error: function(xhr) {
                    const errors = xhr.responseJSON?.errors || {};
                    let errorMsg = 'Failed to load scoresheet';
                    if (Object.keys(errors).length > 0) {
                        errorMsg = Object.values(errors).join(', ');
                    } else if (xhr.responseJSON?.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    showFlashMessage(errorMsg, 'danger');
                },
                complete: function() {
                    btn.prop('disabled', false).html('<i class="fas fa-table me-2"></i>Load Scoresheet');
                    hideSpinner();
                }
            });
        }

        function renderScoresheet() {
            let html = '';
            const offset = (pagination.current_page - 1) * pagination.per_page;
            
            studentScores.forEach(function(student, index) {
                const displayIndex = offset + index + 1;
                html += `
                    <tr data-index="${index}" data-student-id="${student.student_id}">
                        <td class="text-center">${displayIndex}</td>
                        <td>${student.student_number}</td>
                        <td>${student.student_name}</td>
                        <td><input type="number" class="form-control form-control-sm score-input" data-field="assignment_1" value="${student.assignment_1 || ''}" min="0" max="100" step="0.01"></td>
                        <td><input type="number" class="form-control form-control-sm score-input" data-field="assignment_2" value="${student.assignment_2 || ''}" min="0" max="100" step="0.01"></td>
                        <td><input type="number" class="form-control form-control-sm score-input" data-field="assignment_3" value="${student.assignment_3 || ''}" min="0" max="100" step="0.01"></td>
                        <td class="assignment-4-col" style="display: ${assignmentCount >= 4 ? 'table-cell' : 'none'};"><input type="number" class="form-control form-control-sm score-input" data-field="assignment_4" value="${student.assignment_4 || ''}" min="0" max="100" step="0.01"></td>
                        <td class="assignment-5-col" style="display: ${assignmentCount >= 5 ? 'table-cell' : 'none'};"><input type="number" class="form-control form-control-sm score-input" data-field="assignment_5" value="${student.assignment_5 || ''}" min="0" max="100" step="0.01"></td>
                        <td><input type="number" class="form-control form-control-sm score-input" data-field="mid_semester" value="${student.mid_semester || ''}" min="0" max="100" step="0.01"></td>
                        <td><input type="number" class="form-control form-control-sm score-input" data-field="end_semester" value="${student.end_semester || ''}" min="0" max="100" step="0.01"></td>
                        <td class="text-center fw-bold total-cell">${student.total}</td>
                        <td class="text-center grade-cell">${student.grade}</td>
                    </tr>
                `;
            });
            $('#scoresheetBody').html(html);
            updateAssignmentColumns();
        }
        
        function renderPagination() {
            // Update pagination info
            $('#recordsFrom').text(pagination.from || 0);
            $('#recordsTo').text(pagination.to || 0);
            $('#recordsTotal').text(pagination.total);
            
            // Build pagination buttons
            let paginationHtml = '';
            
            // Previous button
            paginationHtml += `
                <li class="page-item ${pagination.current_page === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="#" data-page="${pagination.current_page - 1}" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
            `;
            
            // Page numbers
            const maxVisible = 5;
            let startPage = Math.max(1, pagination.current_page - Math.floor(maxVisible / 2));
            let endPage = Math.min(pagination.last_page, startPage + maxVisible - 1);
            
            // Adjust start if we're near the end
            if (endPage - startPage < maxVisible - 1) {
                startPage = Math.max(1, endPage - maxVisible + 1);
            }
            
            // First page
            if (startPage > 1) {
                paginationHtml += `
                    <li class="page-item">
                        <a class="page-link" href="#" data-page="1">1</a>
                    </li>
                `;
                if (startPage > 2) {
                    paginationHtml += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                }
            }
            
            // Page numbers
            for (let i = startPage; i <= endPage; i++) {
                paginationHtml += `
                    <li class="page-item ${i === pagination.current_page ? 'active' : ''}">
                        <a class="page-link" href="#" data-page="${i}">${i}</a>
                    </li>
                `;
            }
            
            // Last page
            if (endPage < pagination.last_page) {
                if (endPage < pagination.last_page - 1) {
                    paginationHtml += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                }
                paginationHtml += `
                    <li class="page-item">
                        <a class="page-link" href="#" data-page="${pagination.last_page}">${pagination.last_page}</a>
                    </li>
                `;
            }
            
            // Next button
            paginationHtml += `
                <li class="page-item ${pagination.current_page === pagination.last_page ? 'disabled' : ''}">
                    <a class="page-link" href="#" data-page="${pagination.current_page + 1}" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            `;
            
            $('#paginationList').html(paginationHtml);
            
            // Attach click handlers
            $('#paginationList a.page-link').on('click', function(e) {
                e.preventDefault();
                const page = parseInt($(this).data('page'));
                if (!isNaN(page) && page !== pagination.current_page) {
                    loadScoresheet(page);
                }
            });
        }

        function calculateStudentTotal(index) {
            const row = $(`tr[data-index="${index}"]`);
            const student = studentScores[index];
            
            // Get all assignment scores
            let assignments = [];
            for (let i = 1; i <= assignmentCount; i++) {
                const value = parseFloat(row.find(`input[data-field="assignment_${i}"]`).val());
                if (!isNaN(value)) {
                    assignments.push(value);
                    student[`assignment_${i}`] = value;
                } else {
                    student[`assignment_${i}`] = null;
                }
            }
            
            // Calculate assignment average
            const assignmentAvg = assignments.length > 0 ? assignments.reduce((a, b) => a + b, 0) / assignments.length : 0;
            const assignmentWeighted = assignmentAvg * (weights.assignment / 100);
            
            // Get other scores
            const midSem = parseFloat(row.find('input[data-field="mid_semester"]').val()) || 0;
            const endSem = parseFloat(row.find('input[data-field="end_semester"]').val()) || 0;
            
            student.mid_semester = midSem || null;
            student.end_semester = endSem || null;
            
            // Calculate weighted scores
            const midWeighted = midSem * (weights.mid_semester / 100);
            const endWeighted = endSem * (weights.end_semester / 100);
            
            // Calculate total
            const total = (assignmentWeighted + midWeighted + endWeighted).toFixed(2);
            const grade = determineGrade(total);
            
            student.total = total;
            student.grade = grade;
            
            // Update display
            row.find('.total-cell').text(total);
            row.find('.grade-cell').text(grade);
            
            updateStatistics();
        }

        function determineGrade(totalScore) {
            totalScore = parseFloat(totalScore);
            if (totalScore >= 80) return 'A';
            if (totalScore >= 75) return 'B+';
            if (totalScore >= 70) return 'B';
            if (totalScore >= 65) return 'C+';
            if (totalScore >= 60) return 'C';
            if (totalScore >= 55) return 'D+';
            if (totalScore >= 50) return 'D';
            return 'E';
        }

        function recalculateAllScores() {
            studentScores.forEach(function(student, index) {
                calculateStudentTotal(index);
            });
        }

        function saveWeights() {
            const assignmentWeight = parseFloat($('#assignmentWeight').val());
            const midSemesterWeight = parseFloat($('#midSemesterWeight').val());
            const endSemesterWeight = parseFloat($('#endSemesterWeight').val());
            
            const total = assignmentWeight + midSemesterWeight + endSemesterWeight;
            if (total !== 100) {
                showFlashMessage(`Weights must sum to 100%. Current total: ${total}%`, 'danger');
                return;
            }
            
            weights.assignment = assignmentWeight;
            weights.mid_semester = midSemesterWeight;
            weights.end_semester = endSemesterWeight;
            
            updateWeightDisplay();
            recalculateAllScores();
            $('#weightConfigForm').hide();
            showFlashMessage('Weight configuration saved and scores recalculated', 'success');
        }

        function updateWeightDisplay() {
            $('#assignmentCountDisplay').text(assignmentCount);
            $('#assignmentWeightDisplay').text(weights.assignment);
            $('#midSemesterWeightDisplay').text(weights.mid_semester);
            $('#endSemesterWeightDisplay').text(weights.end_semester);
            
            $('#assignmentWeight').val(weights.assignment);
            $('#midSemesterWeight').val(weights.mid_semester);
            $('#endSemesterWeight').val(weights.end_semester);
            
            // Update button states
            $('#removeAssignmentBtn').prop('disabled', assignmentCount <= 3);
            $('#addAssignmentBtn').prop('disabled', assignmentCount >= 5);
        }

        function updateAssignmentColumns() {
            $('.assignment-4-col').toggle(assignmentCount >= 4);
            $('.assignment-5-col').toggle(assignmentCount >= 5);
            updateWeightDisplay();
        }

        function updateStatistics() {
            const total = studentScores.length;
            const passing = studentScores.filter(s => parseFloat(s.total) >= 50).length;
            const failing = studentScores.filter(s => parseFloat(s.total) < 50 && parseFloat(s.total) > 0).length;
            
            const totals = studentScores.map(s => parseFloat(s.total)).filter(t => t > 0);
            const average = totals.length > 0 ? (totals.reduce((a, b) => a + b, 0) / totals.length).toFixed(2) : 0;
            
            $('#totalStudents').text(total);
            $('#passingStudents').text(passing);
            $('#failingStudents').text(failing);
            $('#classAverage').text(average + '%');
        }

        function saveScores() {
            showSpinner('Saving scores...');
            const btn = $('#saveScoresBtn');
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');
            
            $.ajax({
                url: '{{ route("admin.assessment-scores.save-scores") }}',
                method: 'POST',
                data: {
                    course_id: filters.course_id,
                    cohort_id: filters.cohort_id,
                    semester_id: filters.semester_id,
                    assignment_weight: weights.assignment,
                    mid_semester_weight: weights.mid_semester,
                    end_semester_weight: weights.end_semester,
                    assignment_count: assignmentCount,
                    scores: studentScores
                },
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        showFlashMessage(response.message, 'success');
                    }
                },
                error: function(xhr) {
                    const errors = xhr.responseJSON?.errors || {};
                    let errorMsg = xhr.responseJSON?.message || 'Failed to save scores';
                    
                    if (Object.keys(errors).length > 0) {
                        errorMsg += ': ' + Object.values(errors).join(', ');
                    }
                    
                    showFlashMessage(errorMsg, 'danger');
                },
                complete: function() {
                    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i>Save All Scores');
                    hideSpinner();
                }
            });
        }

        function importExcel() {
            const fileInput = $('#importFile')[0];
            if (!fileInput.files.length) {
                showFlashMessage('Please select a file to import', 'warning');
                return;
            }

            // Validate filters are selected
            if (!filters.class_id || !filters.course_id || !filters.cohort_id || !filters.semester_id) {
                showFlashMessage('Please select all filters before importing', 'danger');
                return;
            }

            const formData = new FormData();
            formData.append('import_file', fileInput.files[0]);
            formData.append('class_id', filters.class_id);
            formData.append('course_id', filters.course_id);
            formData.append('cohort_id', filters.cohort_id);
            formData.append('semester_id', filters.semester_id);

            showSpinner('Processing import file...');
            const btn = $('#importExcelBtn');
            btn.prop('disabled', true);

            $.ajax({
                url: '{{ route("admin.assessment-scores.import-excel") }}',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        importPreviewData = response.preview_data;
                        showImportPreview(response.preview_data, response.summary);
                    }
                },
                error: function(xhr) {
                    const errors = xhr.responseJSON?.errors || {};
                    let errorMsg = xhr.responseJSON?.message || 'Failed to process import file';
                    
                    if (Object.keys(errors).length > 0) {
                        errorMsg += ':\n' + Object.values(errors).map(e => '- ' + e).join('\n');
                    }
                    
                    showFlashMessage(errorMsg, 'danger');
                },
                complete: function() {
                    btn.prop('disabled', false);
                    hideSpinner();
                }
            });
        }

        function showImportPreview(previewData, summary) {
            let summaryHtml = `${summary.total_records} records found`;
            if (summary.new_records > 0) {
                summaryHtml += ` (${summary.new_records} new`;
            }
            if (summary.updated_records > 0) {
                summaryHtml += `, ${summary.updated_records} to update`;
            }
            if (summary.new_records > 0 || summary.updated_records > 0) {
                summaryHtml += ')';
            }
            
            $('#importSummary').html(summaryHtml);
            
            let tbody = '';
            previewData.forEach((record, index) => {
                const actionBadge = record.action === 'create' 
                    ? '<span class="badge bg-success">New</span>' 
                    : '<span class="badge bg-info">Update</span>';
                
                tbody += `
                    <tr>
                        <td>${record.student_index_number}</td>
                        <td>${record.student_name}</td>
                        <td>${actionBadge}</td>
                        <td class="text-center">${record.assignment_1 ?? '-'}</td>
                        <td class="text-center">${record.assignment_2 ?? '-'}</td>
                        <td class="text-center">${record.assignment_3 ?? '-'}</td>
                        <td class="text-center">${record.assignment_4 ?? '-'}</td>
                        <td class="text-center">${record.assignment_5 ?? '-'}</td>
                        <td class="text-center">${record.mid_semester ?? '-'}</td>
                        <td class="text-center">${record.end_semester ?? '-'}</td>
                        <td class="text-center"><strong>${record.total ?? 0}%</strong></td>
                    </tr>
                `;
            });
            
            $('#importPreviewBody').html(tbody);
            $('#importPreviewModal').modal('show');
        }

        function confirmImport() {
            if (!importPreviewData.length) {
                showFlashMessage('No import data available', 'warning');
                return;
            }

            showSpinner('Importing scores...');
            const btn = $('#confirmImportBtn');
            btn.prop('disabled', true);

            $.ajax({
                url: '{{ route("admin.assessment-scores.confirm-import") }}',
                method: 'POST',
                data: {
                    preview_data: importPreviewData,
                    course_id: filters.course_id,
                    cohort_id: filters.cohort_id,
                    semester_id: filters.semester_id
                },
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        $('#importPreviewModal').modal('hide');
                        showFlashMessage(response.message, 'success');
                        
                        // Reset import file input
                        $('#importFile').val('');
                        $('#selectImportFileBtn').html('<i class="fas fa-file-upload me-1"></i>Choose File');
                        $('#importExcelBtn').prop('disabled', true);
                        
                        // Reload scoresheet to show imported data
                        loadScoresheet();
                    }
                },
                error: function(xhr) {
                    const message = xhr.responseJSON?.message || 'Failed to import scores';
                    showFlashMessage(message, 'danger');
                },
                complete: function() {
                    btn.prop('disabled', false);
                    hideSpinner();
                }
            });
        }

        function downloadTemplate() {
            const params = new URLSearchParams(filters);
            window.location.href = '{{ route("admin.assessment-scores.download-template") }}?' + params.toString();
        }

        function exportExcel() {
            showSpinner('Generating Excel file...');
            
            const form = $('<form>', {
                method: 'POST',
                action: '{{ route("admin.assessment-scores.export-excel") }}'
            });
            
            form.append($('<input>', { type: 'hidden', name: '_token', value: '{{ csrf_token() }}' }));
            form.append($('<input>', { type: 'hidden', name: 'course_id', value: filters.course_id }));
            form.append($('<input>', { type: 'hidden', name: 'class_id', value: filters.class_id }));
            form.append($('<input>', { type: 'hidden', name: 'cohort_id', value: filters.cohort_id }));
            form.append($('<input>', { type: 'hidden', name: 'semester_id', value: filters.semester_id }));
            form.append($('<input>', { type: 'hidden', name: 'scores', value: JSON.stringify(studentScores) }));
            form.append($('<input>', { type: 'hidden', name: 'weights', value: JSON.stringify(weights) }));
            
            $('body').append(form);
            form.submit();
            
            // Remove form and hide spinner after submission
            setTimeout(function() {
                form.remove();
                hideSpinner();
            }, 1000);
        }

        function updateTemplateButtonState() {
            const enabled = filters.class_id && filters.course_id && filters.cohort_id && filters.semester_id;
            $('#downloadTemplateBtn').prop('disabled', !enabled);
        }

        function clearScoresheet() {
            studentScores = [];
            $('#scoresheetBody').html('');
            $('#weightConfigSection, #statsSection, #scoresheetSection').hide();
        }

        function showFlashMessage(message, type) {
            const alertClass = type === 'success' ? 'alert-success' : type === 'danger' ? 'alert-danger' : 'alert-info';
            const iconClass = type === 'success' ? 'fa-check-circle' : type === 'danger' ? 'fa-exclamation-circle' : 'fa-info-circle';
            
            const html = `
                <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                    <i class="fas ${iconClass} me-2"></i>${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
            
            $('#flash-message').html(html);
            
            // Auto-dismiss after 5 seconds
            setTimeout(function() {
                $('#flash-message .alert').alert('close');
            }, 5000);
        }
    </script>
    @endpush
</x-dashboard.default>