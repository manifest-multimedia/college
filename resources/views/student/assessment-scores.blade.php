<x-dashboard.default title="My Assessment Scores">
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">My Published Assessment Scores</h5>
        </div>
        <div class="card-body">
            <!-- Filters Section -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <label for="semester" class="form-label">Semester</label>
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
                    <label for="academicYear" class="form-label">Academic Year</label>
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
                    <label for="perPage" class="form-label">Records Per Page</label>
                    <select id="perPage" class="form-select">
                        <option value="15">15</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button id="loadScoresBtn" class="btn btn-primary">
                        <i class="bi bi-search"></i> Load Scores
                    </button>
                </div>
            </div>

            <!-- Alert Messages -->
            <div id="alertContainer"></div>

            <!-- Scores Table -->
            <div id="scoresTableContainer" style="display: none;">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Course Code</th>
                                <th>Course Name</th>
                                <th>Semester</th>
                                <th>Academic Year</th>
                                <th>Cohort</th>
                                <th>Assignments</th>
                                <th>Mid-Semester</th>
                                <th>End-Semester</th>
                                <th>Total Score</th>
                                <th>Grade</th>
                                <th>Grade Points</th>
                            </tr>
                        </thead>
                        <tbody id="scoresTableBody">
                            <!-- Dynamic content -->
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div id="paginationContainer" class="d-flex justify-content-between align-items-center mt-3">
                    <div id="paginationInfo"></div>
                    <nav>
                        <ul id="paginationLinks" class="pagination mb-0">
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
                <p class="mt-2">Loading your assessment scores...</p>
            </div>

            <!-- No Data Message -->
            <div id="noDataMessage" style="display: none;" class="alert alert-info text-center">
                <i class="bi bi-info-circle me-2"></i>
                No published assessment scores found. Please check back later or contact your academic officer.
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function() {
            let currentPage = 1;

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

                        renderScoresTable(response.scores);
                        renderPagination(response.pagination);
                        $('#scoresTableContainer').show();
                    },
                    error: function(xhr) {
                        $('#loadingSpinner').hide();
                        showAlert(xhr.responseJSON?.message || 'Failed to load scores', 'danger');
                    }
                });
            }

            function renderScoresTable(scores) {
                const tbody = $('#scoresTableBody');
                tbody.empty();

                scores.forEach(score => {
                    // Collect all assignment scores
                    const assignments = [];
                    if (score.assignment_1 !== null) assignments.push(score.assignment_1);
                    if (score.assignment_2 !== null) assignments.push(score.assignment_2);
                    if (score.assignment_3 !== null) assignments.push(score.assignment_3);
                    if (score.assignment_4 !== null) assignments.push(score.assignment_4);
                    if (score.assignment_5 !== null) assignments.push(score.assignment_5);
                    
                    const assignmentDisplay = assignments.length > 0 ? assignments.join(', ') : '-';
                    
                    tbody.append(`
                        <tr>
                            <td>${score.course_code}</td>
                            <td>${score.course_name}</td>
                            <td>${score.semester}</td>
                            <td>${score.academic_year}</td>
                            <td>${score.cohort}</td>
                            <td>${assignmentDisplay}</td>
                            <td>${score.mid_semester || '-'}</td>
                            <td>${score.end_semester || '-'}</td>
                            <td><strong>${score.total_score || '-'}</strong></td>
                            <td><span class="badge bg-primary">${score.grade_letter || '-'}</span></td>
                            <td>${score.grade_points || '-'}</td>
                        </tr>
                    `);
                });
            }

            function renderPagination(pagination) {
                $('#paginationInfo').text(`Showing ${pagination.per_page * (pagination.current_page - 1) + 1} to ${Math.min(pagination.per_page * pagination.current_page, pagination.total)} of ${pagination.total} entries`);

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
