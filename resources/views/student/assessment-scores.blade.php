<x-dashboard.default title="My Results">
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title mb-1">My Results</h5>
                    <p class="text-muted mb-0 small">View your published assessment results</p>
                </div>
                <div>
                    <button id="exportPdfBtn" class="btn btn-sm btn-outline-primary" style="display: none;">
                        <i class="bi bi-file-pdf"></i> Export PDF
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <!-- Filters Section -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label for="semester" class="form-label fw-semibold">Semester</label>
                    <select id="semester" class="form-select">
                        <option value="">All Semesters</option>
                        @php
                            $semesters = \App\Models\Semester::orderBy('name')->get();
                        @endphp
                        @foreach($semesters as $semester)
                            <option value="{{ $semester->id }}">{{ $semester->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="academicYear" class="form-label fw-semibold">Academic Year</label>
                    <select id="academicYear" class="form-select">
                        <option value="">All Academic Years</option>
                        @php
                            $academicYears = \App\Models\AcademicYear::query()
                                ->select('name')
                                ->distinct()
                                ->pluck('name');
                        @endphp
                        @foreach($academicYears as $year)
                            <option value="{{ $year }}">{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="perPage" class="form-label fw-semibold">Records Per Page</label>
                    <select id="perPage" class="form-select">
                        <option value="15">15</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button id="loadScoresBtn" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Load Results
                    </button>
                </div>
            </div>

            <!-- Alert Messages -->
            <div id="alertContainer"></div>

            <!-- Scores Table -->
            <div id="scoresTableContainer" style="display: none;">
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="resultsTable">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 5%;">#</th>
                                <th style="width: 15%;">Course Code</th>
                                <th style="width: 30%;">Course Name</th>
                                <th class="text-center" style="width: 10%;">Credit Hours</th>
                                <th class="text-center" style="width: 10%;">Grade</th>
                                <th class="text-center" style="width: 10%;">Grade Points</th>
                                <th class="text-center" style="width: 15%;">Status</th>
                            </tr>
                        </thead>
                        <tbody id="scoresTableBody">
                            <!-- Dynamic content -->
                        </tbody>
                    </table>
                </div>

                <!-- Summary Section -->
                <div id="summarySection" class="mt-4">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6 class="card-title mb-3 fw-bold">Academic Summary</h6>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <div class="text-center p-3 bg-white rounded">
                                        <p class="text-muted mb-1 small">Total Credits</p>
                                        <h4 class="mb-0 text-primary" id="totalCredits">0</h4>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center p-3 bg-white rounded">
                                        <p class="text-muted mb-1 small">CGPA</p>
                                        <h4 class="mb-0 text-success" id="cgpaValue">0.00</h4>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="text-center p-3 bg-white rounded">
                                        <p class="text-muted mb-1 small">Overall Remark</p>
                                        <h4 class="mb-0" id="overallRemark">-</h4>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Semester Breakdown -->
                            <div id="semesterBreakdown" class="mt-4">
                                <h6 class="fw-bold mb-3">Semester Breakdown</h6>
                                <div class="row g-2" id="semesterStats">
                                    <!-- Dynamic semester stats -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div id="paginationContainer" class="d-flex justify-content-between align-items-center mt-4">
                    <div id="paginationInfo" class="text-muted small"></div>
                    <nav>
                        <ul id="paginationLinks" class="pagination pagination-sm mb-0">
                            <!-- Dynamic pagination -->
                        </ul>
                    </nav>
                </div>
            </div>

            <!-- Loading Spinner -->
            <div id="loadingSpinner" style="display: none;" class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-3 text-muted">Loading your results...</p>
            </div>

            <!-- No Data Message -->
            <div id="noDataMessage" style="display: none;" class="alert alert-info text-center">
                <i class="bi bi-info-circle me-2"></i>
                No published results found. Please check back later or contact your academic officer.
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function() {
            let currentPage = 1;
            let currentSummary = null;

            // Auto-load scores on page load
            loadScores();

            // Load scores button
            $('#loadScoresBtn').on('click', function() {
                currentPage = 1;
                loadScores();
            });

            // Pagination
            $(document).on('click', '.page-link', function(e) {
                e.preventDefault();
                const page = $(this).data('page');
                if (page && page !== currentPage) {
                    currentPage = page;
                    loadScores();
                }
            });

            // Per page change
            $('#perPage').on('change', function() {
                currentPage = 1;
                loadScores();
            });

            // Export PDF
            $('#exportPdfBtn').on('click', function() {
                exportToPDF();
            });

            function loadScores() {
                const filters = {
                    semester_id: $('#semester').val() || '',
                    academic_year: $('#academicYear').val() || '',
                    per_page: $('#perPage').val(),
                    page: currentPage
                };

                $('#loadingSpinner').show();
                $('#scoresTableContainer').hide();
                $('#noDataMessage').hide();
                $('#exportPdfBtn').hide();

                $.ajax({
                    url: '{{ route("student.assessment-scores.get") }}',
                    method: 'GET',
                    data: filters,
                    success: function(response) {
                        $('#loadingSpinner').hide();
                        
                        if (response.scores.length === 0) {
                            $('#noDataMessage').show();
                            return;
                        }

                        currentSummary = response.summary;
                        renderScoresTable(response.scores);
                        renderSummary(response.summary);
                        renderPagination(response.pagination);
                        $('#scoresTableContainer').show();
                        $('#exportPdfBtn').show();
                    },
                    error: function(xhr) {
                        $('#loadingSpinner').hide();
                        showAlert(xhr.responseJSON?.message || 'Failed to load results', 'danger');
                    }
                });
            }

            function renderScoresTable(scores) {
                const tbody = $('#scoresTableBody');
                tbody.empty();

                scores.forEach((score, index) => {
                    const statusBadge = getStatusBadge(score.status);
                    const rowNumber = ((currentPage - 1) * parseInt($('#perPage').val())) + index + 1;
                    
                    tbody.append(`
                        <tr>
                            <td class="text-center text-muted">${rowNumber}</td>
                            <td><strong>${score.course_code}</strong></td>
                            <td>${score.course_name}</td>
                            <td class="text-center">${score.credit_hours}</td>
                            <td class="text-center"><strong>${score.grade_letter}</strong></td>
                            <td class="text-center"><strong>${score.grade_points.toFixed(1)}</strong></td>
                            <td class="text-center">${statusBadge}</td>
                        </tr>
                    `);
                });
            }

            function renderSummary(summary) {
                // Overall summary
                $('#totalCredits').text(summary.total_credits);
                $('#cgpaValue').text(summary.cgpa.toFixed(2));
                $('#overallRemark').text(summary.overall_remark);
                
                // Color code the CGPA
                const cgpaElement = $('#cgpaValue');
                cgpaElement.removeClass('text-success text-warning text-danger');
                if (summary.cgpa >= 3.0) {
                    cgpaElement.addClass('text-success');
                } else if (summary.cgpa >= 2.0) {
                    cgpaElement.addClass('text-warning');
                } else {
                    cgpaElement.addClass('text-danger');
                }

                // Semester breakdown
                const semesterStats = $('#semesterStats');
                semesterStats.empty();

                if (summary.semesters && Object.keys(summary.semesters).length > 0) {
                    $('#semesterBreakdown').show();
                    
                    Object.values(summary.semesters).forEach(sem => {
                        semesterStats.append(`
                            <div class="col-md-4">
                                <div class="card border">
                                    <div class="card-body py-2">
                                        <h6 class="card-title mb-2 text-primary">${sem.semester_name}</h6>
                                        <div class="d-flex justify-content-between small">
                                            <span>Credits: <strong>${sem.total_credits}</strong></span>
                                            <span>GPA: <strong>${sem.gpa.toFixed(2)}</strong></span>
                                        </div>
                                        <div class="small text-muted">
                                            Passed: ${sem.passed_courses} | Failed: ${sem.failed_courses}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `);
                    });
                } else {
                    $('#semesterBreakdown').hide();
                }
            }

            function renderPagination(pagination) {
                const from = pagination.per_page * (pagination.current_page - 1) + 1;
                const to = Math.min(pagination.per_page * pagination.current_page, pagination.total);
                $('#paginationInfo').text(`Showing ${from} to ${to} of ${pagination.total} results`);

                const links = $('#paginationLinks');
                links.empty();

                // Previous button
                links.append(`
                    <li class="page-item ${pagination.current_page === 1 ? 'disabled' : ''}">
                        <a class="page-link" href="#" data-page="${pagination.current_page - 1}">Previous</a>
                    </li>
                `);

                // Page numbers
                for (let i = 1; i <= pagination.last_page; i++) {
                    if (i === 1 || i === pagination.last_page || (i >= pagination.current_page - 2 && i <= pagination.current_page + 2)) {
                        links.append(`
                            <li class="page-item ${i === pagination.current_page ? 'active' : ''}">
                                <a class="page-link" href="#" data-page="${i}">${i}</a>
                            </li>
                        `);
                    } else if (i === pagination.current_page - 3 || i === pagination.current_page + 3) {
                        links.append('<li class="page-item disabled"><span class="page-link">...</span></li>');
                    }
                }

                // Next button
                links.append(`
                    <li class="page-item ${pagination.current_page === pagination.last_page ? 'disabled' : ''}">
                        <a class="page-link" href="#" data-page="${pagination.current_page + 1}">Next</a>
                    </li>
                `);
            }

            function getStatusBadge(status) {
                const badges = {
                    'Pass': '<span class="badge bg-success">Pass</span>',
                    'Resit': '<span class="badge bg-warning text-dark">Resit</span>',
                    'Carryover': '<span class="badge bg-danger">Carryover</span>',
                    'Fail': '<span class="badge bg-danger">Fail</span>'
                };
                return badges[status] || '<span class="badge bg-secondary">N/A</span>';
            }

            function exportToPDF() {
                const filters = {
                    semester_id: $('#semester').val() || '',
                    academic_year: $('#academicYear').val() || '',
                };

                const queryString = $.param(filters);
                window.open(`{{ route('student.assessment-scores.pdf') }}?${queryString}`, '_blank');
            }

            function showAlert(message, type) {
                const alertHtml = `
                    <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                        ${message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;
                $('#alertContainer').html(alertHtml);
                setTimeout(() => {
                    $('.alert').alert('close');
                }, 5000);
            }
        });
    </script>
    @endpush
</x-dashboard.default>
