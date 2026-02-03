<x-dashboard.default title="Assessment Score Management">
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Assessment Score Publishing</h5>
        </div>
        <div class="card-body">
            <!-- Filters Section -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <label for="academicYear" class="form-label">Academic Year</label>
                    <select id="academicYear" class="form-select">
                        <option value="">Select Academic Year</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year }}">{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="semester" class="form-label">Semester</label>
                    <select id="semester" class="form-select">
                        <option value="">Select Semester</option>
                        @foreach($semesters as $semester)
                            <option value="{{ $semester->id }}" 
                                {{ $currentSemester && $semester->id == $currentSemester->id ? 'selected' : '' }}>
                                {{ $semester->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="program" class="form-label">Program</label>
                    <select id="program" class="form-select">
                        <option value="">All Programs</option>
                        @foreach($collegeClasses as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="cohort" class="form-label">Cohort</label>
                    <select id="cohort" class="form-select">
                        <option value="">All Cohorts</option>
                        @foreach($cohorts as $cohort)
                            <option value="{{ $cohort->id }}"
                                {{ $currentCohort && $cohort->id == $currentCohort->id ? 'selected' : '' }}>
                                {{ $cohort->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-3">
                    <label for="course" class="form-label">Course</label>
                    <select id="course" class="form-select" disabled>
                        <option value="">Select Course</option>
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
                <div class="col-md-6 d-flex align-items-end">
                    <button id="loadScoresBtn" class="btn btn-primary me-2" disabled>
                        <i class="bi bi-search"></i> Load Scores
                    </button>
                    <button id="bulkPublishBtn" class="btn btn-success me-2" disabled>
                        <i class="bi bi-check-circle"></i> Bulk Publish
                    </button>
                    <button id="bulkUnpublishBtn" class="btn btn-warning" disabled>
                        <i class="bi bi-x-circle"></i> Bulk Unpublish
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
                                <th>Student Index</th>
                                <th>Student Name</th>
                                <th>Course Code</th>
                                <th>Course Name</th>
                                <th>Cohort</th>
                                <th>Total Score</th>
                                <th>Grade</th>
                                <th>Published Status</th>
                                <th>Published By</th>
                                <th>Published At</th>
                                <th>Action</th>
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
                <p class="mt-2">Loading assessment scores...</p>
            </div>

            <!-- No Data Message -->
            <div id="noDataMessage" style="display: none;" class="alert alert-info text-center">
                No assessment scores found for the selected criteria.
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function() {
            let currentPage = 1;
            let currentFilters = {};

            // Load courses based on program selection
            $('#program').on('change', function() {
                const programId = $(this).val();
                const $courseSelect = $('#course');
                
                $courseSelect.prop('disabled', true).html('<option value="">Loading...</option>');
                
                if (!programId) {
                    $courseSelect.html('<option value="">Select Course</option>').prop('disabled', true);
                    return;
                }

                $.ajax({
                    url: '{{ route("admin.courses.by-class") }}',
                    method: 'GET',
                    data: { college_class_id: programId },
                    success: function(response) {
                        $courseSelect.html('<option value="">All Courses</option>');
                        response.courses.forEach(course => {
                            $courseSelect.append(`<option value="${course.id}">${course.code} - ${course.name}</option>`);
                        });
                        $courseSelect.prop('disabled', false);
                    },
                    error: function() {
                        showAlert('Failed to load courses', 'danger');
                        $courseSelect.html('<option value="">Select Course</option>');
                    }
                });
            });

            // Enable/disable load button based on required fields
            $('#academicYear, #semester').on('change', function() {
                const academicYear = $('#academicYear').val();
                const semester = $('#semester').val();
                $('#loadScoresBtn').prop('disabled', !academicYear || !semester);
            });

            // Load scores
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
                if ($('#scoresTableContainer').is(':visible')) {
                    currentPage = 1;
                    loadScores();
                }
            });

            // Toggle publish
            $(document).on('click', '.toggle-publish-btn', function() {
                const scoreId = $(this).data('id');
                const $btn = $(this);
                
                $btn.prop('disabled', true);
                
                $.ajax({
                    url: `/academic-officer/assessment-scores/${scoreId}/toggle-publish`,
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        showAlert(response.message, 'success');
                        loadScores();
                    },
                    error: function(xhr) {
                        showAlert(xhr.responseJSON?.message || 'Failed to update publish status', 'danger');
                        $btn.prop('disabled', false);
                    }
                });
            });

            // Bulk publish
            $('#bulkPublishBtn').on('click', function() {
                bulkAction('publish');
            });

            // Bulk unpublish
            $('#bulkUnpublishBtn').on('click', function() {
                bulkAction('unpublish');
            });

            function loadScores() {
                currentFilters = {
                    academic_year: $('#academicYear').val(),
                    semester_id: $('#semester').val(),
                    college_class_id: $('#program').val() || '',
                    course_id: $('#course').val() || '',
                    cohort_id: $('#cohort').val() || '',
                    per_page: $('#perPage').val(),
                    page: currentPage
                };

                $('#loadingSpinner').show();
                $('#scoresTableContainer').hide();
                $('#noDataMessage').hide();

                $.ajax({
                    url: '{{ route("academic-officer.assessment-scores.get") }}',
                    method: 'GET',
                    data: currentFilters,
                    success: function(response) {
                        $('#loadingSpinner').hide();
                        
                        if (response.scores.length === 0) {
                            $('#noDataMessage').show();
                            return;
                        }

                        renderScoresTable(response.scores);
                        renderPagination(response.pagination);
                        $('#scoresTableContainer').show();

                        // Enable bulk actions if course is selected
                        const courseSelected = currentFilters.course_id !== '';
                        $('#bulkPublishBtn, #bulkUnpublishBtn').prop('disabled', !courseSelected);
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
                    const publishedBadge = score.is_published
                        ? '<span class="badge bg-success">Published</span>'
                        : '<span class="badge bg-secondary">Unpublished</span>';

                    const toggleBtn = score.is_published
                        ? '<button class="btn btn-sm btn-warning toggle-publish-btn" data-id="' + score.id + '"><i class="bi bi-x-circle"></i> Unpublish</button>'
                        : '<button class="btn btn-sm btn-success toggle-publish-btn" data-id="' + score.id + '"><i class="bi bi-check-circle"></i> Publish</button>';

                    tbody.append(`
                        <tr>
                            <td>${score.student_index}</td>
                            <td>${score.student_name}</td>
                            <td>${score.course_code}</td>
                            <td>${score.course_name}</td>
                            <td>${score.cohort}</td>
                            <td>${score.total_score || '-'}</td>
                            <td>${score.grade_letter || '-'}</td>
                            <td>${publishedBadge}</td>
                            <td>${score.published_by || '-'}</td>
                            <td>${score.published_at || '-'}</td>
                            <td>${toggleBtn}</td>
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

            function bulkAction(action) {
                if (!currentFilters.course_id) {
                    showAlert('Please select a specific course for bulk actions', 'warning');
                    return;
                }

                if (!confirm(`Are you sure you want to ${action} all scores for this course?`)) {
                    return;
                }

                $.ajax({
                    url: '{{ route("academic-officer.assessment-scores.bulk-publish") }}',
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    contentType: 'application/json',
                    data: JSON.stringify({
                        academic_year: currentFilters.academic_year,
                        semester_id: currentFilters.semester_id,
                        course_id: currentFilters.course_id,
                        cohort_id: currentFilters.cohort_id,
                        action: action
                    }),
                    success: function(response) {
                        showAlert(response.message, 'success');
                        loadScores();
                    },
                    error: function(xhr) {
                        showAlert(xhr.responseJSON?.message || 'Bulk action failed', 'danger');
                    }
                });
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
