<x-dashboard.default title="Resit Assessment Scores">
    <div class="container-fluid py-4">
        <div id="flash-message"></div>

        <div class="card mb-4">
            <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0">Resit Assessment Score Entry</h6>
                <a href="{{ route('admin.assessment-scores.index') }}" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i>Back To Main Score Entry
                </a>
            </div>
            <div class="card-body">
                <div class="alert alert-info mb-3">
                    New resit attempts are recorded separately. Each new attempt is averaged with the current effective exam score to update final grading.
                </div>

                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="program" class="form-label">Program <span class="text-danger">*</span></label>
                        <select id="program" class="form-select">
                            <option value="">Select Program</option>
                            @foreach($collegeClasses as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
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
                    </div>
                    <div class="col-md-3">
                        <label for="course" class="form-label">Course <span class="text-danger">*</span></label>
                        <select id="course" class="form-select" disabled>
                            <option value="">Select program & semester first</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="cohort" class="form-label">Cohort <span class="text-danger">*</span></label>
                        <select id="cohort" class="form-select">
                            <option value="">Select Cohort</option>
                            @foreach($cohorts as $cohort)
                                <option value="{{ $cohort->id }}" @if($currentCohort && $cohort->id == $currentCohort->id) selected @endif>
                                    {{ $cohort->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="academicYear" class="form-label">Academic Year</label>
                        <select id="academicYear" class="form-select">
                            <option value="">Current Academic Year</option>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}">{{ $year->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="perPageSelect" class="form-label">Rows Per Page</label>
                        <select id="perPageSelect" class="form-select">
                            <option value="15">15</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                </div>

                <div class="mt-3 d-flex gap-2">
                    <button id="loadResitScoresheetBtn" class="btn btn-primary">
                        <i class="fas fa-table me-1"></i>Load Resit Scoresheet
                    </button>
                    <button id="saveResitScoresBtn" class="btn btn-success" disabled>
                        <i class="fas fa-save me-1"></i>Save Resit Attempts
                    </button>
                </div>
            </div>
        </div>

        <div id="resitScoresheetSection" class="card" style="display: none;">
            <div class="card-header pb-0">
                <h6 class="card-title">Resit Entry Sheet</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="text-muted">
                        <small>Showing <span id="recordsFrom">0</span> to <span id="recordsTo">0</span> of <span id="recordsTotal">0</span> students</small>
                    </div>
                    <nav>
                        <ul class="pagination pagination-sm mb-0" id="paginationList"></ul>
                    </nav>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle" id="resitTable">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Index No</th>
                                <th>Student Name</th>
                                <th>Main Exam</th>
                                <th>Current Effective Exam</th>
                                <th>Attempts</th>
                                <th>Last Resit</th>
                                <th>New Resit</th>
                                <th>Updated Exam (Preview)</th>
                                <th>Updated Total (Preview)</th>
                                <th>Updated Grade (Preview)</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody id="resitScoresheetBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div id="loadingSpinner" style="display: none;">
        <div class="spinner-overlay">
            <div class="spinner-content">
                <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <div class="mt-3 text-white"><strong id="loadingMessage">Processing...</strong></div>
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
        .resit-input {
            min-width: 100px;
            text-align: center;
        }
    </style>
    @endpush

    @push('scripts')
    <script>
        let studentResitRows = [];
        let filters = {
            class_id: null,
            course_id: null,
            cohort_id: '{{ $currentCohort?->id ?? '' }}',
            semester_id: '{{ $currentSemester?->id ?? '' }}',
            academic_year_id: null,
        };
        let pagination = {
            current_page: 1,
            last_page: 1,
            per_page: 15,
            total: 0,
            from: 0,
            to: 0,
        };

        $(document).ready(function() {
            filters.class_id = $('#program').val() || null;
            filters.semester_id = $('#semester').val() || null;
            filters.cohort_id = $('#cohort').val() || null;
            filters.academic_year_id = $('#academicYear').val() || null;

            $('#program').on('change', function() {
                filters.class_id = $(this).val() || null;
                filters.course_id = null;
                $('#course').val('');

                if (filters.class_id && filters.semester_id) {
                    loadCourses(filters.class_id, filters.semester_id);
                } else {
                    $('#course').prop('disabled', true).html('<option value="">Select program & semester first</option>');
                }
                clearTable();
            });

            $('#semester').on('change', function() {
                filters.semester_id = $(this).val() || null;
                filters.course_id = null;
                $('#course').val('');

                if (filters.class_id && filters.semester_id) {
                    loadCourses(filters.class_id, filters.semester_id);
                } else {
                    $('#course').prop('disabled', true).html('<option value="">Select program & semester first</option>');
                }
                clearTable();
            });

            $('#course').on('change', function() {
                filters.course_id = $(this).val() || null;
                clearTable();
            });

            $('#cohort').on('change', function() {
                filters.cohort_id = $(this).val() || null;
                clearTable();
            });

            $('#academicYear').on('change', function() {
                filters.academic_year_id = $(this).val() || null;
                clearTable();
            });

            $('#perPageSelect').on('change', function() {
                if ($('#resitScoresheetSection').is(':visible')) {
                    loadResitScoresheet(1);
                }
            });

            $('#loadResitScoresheetBtn').on('click', function() {
                loadResitScoresheet(1);
            });

            $('#saveResitScoresBtn').on('click', function() {
                saveResitScores();
            });

            $(document).on('input', '.resit-input', function() {
                const rowIndex = parseInt($(this).closest('tr').data('index'));
                const value = $(this).val();
                studentResitRows[rowIndex].resit_score = value === '' ? null : parseFloat(value);
                refreshPreview(rowIndex);
            });

            $(document).on('input', '.resit-remarks-input', function() {
                const rowIndex = parseInt($(this).closest('tr').data('index'));
                studentResitRows[rowIndex].remarks = $(this).val() || null;
            });
        });

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
                data: { class_id: classId, semester_id: semesterId },
                success: function(response) {
                    let options = '<option value="">Select Course</option>';
                    response.courses.forEach(function(course) {
                        options += `<option value="${course.id}">${course.name}</option>`;
                    });
                    $('#course').prop('disabled', false).html(options);
                },
                error: function() {
                    showFlashMessage('Failed to load courses', 'danger');
                },
                complete: function() {
                    hideSpinner();
                }
            });
        }

        function loadResitScoresheet(page = 1) {
            if (!filters.class_id || !filters.course_id || !filters.cohort_id || !filters.semester_id) {
                showFlashMessage('Please select program, semester, course and cohort', 'danger');
                return;
            }

            showSpinner('Loading resit scoresheet...');

            $.ajax({
                url: '{{ route("admin.assessment-scores.resits.load-scoresheet") }}',
                method: 'POST',
                data: {
                    ...filters,
                    page: page,
                    per_page: $('#perPageSelect').val() || 15,
                },
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                success: function(response) {
                    studentResitRows = (response.students || []).map(function(student) {
                        return {
                            ...student,
                            resit_score: null,
                            remarks: null,
                        };
                    });
                    pagination = response.pagination;
                    renderResitTable();
                    renderPagination();
                    $('#resitScoresheetSection').show();
                    $('#saveResitScoresBtn').prop('disabled', false);
                },
                error: function(xhr) {
                    const message = xhr.responseJSON?.message || 'Failed to load resit scoresheet';
                    showFlashMessage(message, 'danger');
                },
                complete: function() {
                    hideSpinner();
                }
            });
        }

        function renderResitTable() {
            const offset = (pagination.current_page - 1) * pagination.per_page;
            let html = '';

            studentResitRows.forEach(function(student, index) {
                const currentEffective = student.current_effective_exam_score !== null ? parseFloat(student.current_effective_exam_score) : null;
                const total = student.total_score !== null ? parseFloat(student.total_score) : null;
                const canResit = student.has_base_score === true;

                html += `
                    <tr data-index="${index}">
                        <td>${offset + index + 1}</td>
                        <td>${student.student_number || '-'}</td>
                        <td>${student.student_name || '-'}</td>
                        <td class="text-center">${student.main_exam_score ?? '-'}</td>
                        <td class="text-center">${currentEffective ?? '-'}</td>
                        <td class="text-center">${student.resit_attempts_count ?? 0}</td>
                        <td class="text-center">${student.last_resit_score ?? '-'}</td>
                        <td>
                            <input type="number" class="form-control form-control-sm resit-input" min="0" max="100" step="0.01" ${canResit ? '' : 'disabled'}>
                        </td>
                        <td class="text-center preview-updated-exam">-</td>
                        <td class="text-center preview-updated-total">${total ?? '-'}</td>
                        <td class="text-center preview-updated-grade">${student.grade_letter ?? '-'}</td>
                        <td>
                            <input type="text" class="form-control form-control-sm resit-remarks-input" maxlength="255" placeholder="Optional remarks" ${canResit ? '' : 'disabled'}>
                        </td>
                    </tr>
                `;
            });

            $('#resitScoresheetBody').html(html);
            $('#recordsFrom').text(pagination.from || 0);
            $('#recordsTo').text(pagination.to || 0);
            $('#recordsTotal').text(pagination.total || 0);
        }

        function refreshPreview(index) {
            const rowData = studentResitRows[index];
            const row = $(`#resitScoresheetBody tr[data-index="${index}"]`);

            if (rowData.resit_score === null || rowData.current_effective_exam_score === null) {
                row.find('.preview-updated-exam').text('-');
                row.find('.preview-updated-total').text(rowData.total_score ?? '-');
                row.find('.preview-updated-grade').text(rowData.grade_letter ?? '-');
                return;
            }

            const updatedExamScore = roundTo2((parseFloat(rowData.current_effective_exam_score) + parseFloat(rowData.resit_score)) / 2);
            row.find('.preview-updated-exam').text(updatedExamScore.toFixed(2));

            const assignmentWeighted = parseFloat(rowData.assignment_weighted || 0);
            const midWeighted = parseFloat(rowData.mid_semester_weighted || 0);
            const endWeight = parseFloat(rowData.end_semester_weight || 0);
            const updatedTotal = roundTo2(assignmentWeighted + midWeighted + (updatedExamScore * (endWeight / 100)));

            row.find('.preview-updated-total').text(updatedTotal.toFixed(2));
            row.find('.preview-updated-grade').text(determineGrade(updatedTotal));
        }

        function determineGrade(totalScore) {
            const score = parseFloat(totalScore);
            if (score >= 80) return 'A';
            if (score >= 75) return 'B+';
            if (score >= 70) return 'B';
            if (score >= 65) return 'C+';
            if (score >= 60) return 'C';
            if (score >= 55) return 'D+';
            if (score >= 50) return 'D';
            return 'E';
        }

        function saveResitScores() {
            const payloadRows = studentResitRows
                .filter(row => row.resit_score !== null && row.resit_score !== '')
                .map(row => ({
                    student_id: row.student_id,
                    resit_score: row.resit_score,
                    remarks: row.remarks,
                }));

            if (payloadRows.length === 0) {
                showFlashMessage('Enter at least one resit score before saving', 'warning');
                return;
            }

            showSpinner('Saving resit attempts...');

            $.ajax({
                url: '{{ route("admin.assessment-scores.resits.save-scores") }}',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    course_id: filters.course_id,
                    cohort_id: filters.cohort_id,
                    semester_id: filters.semester_id,
                    academic_year_id: filters.academic_year_id,
                    scores: payloadRows,
                }),
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                success: function(response) {
                    showFlashMessage(response.message, 'success');
                    loadResitScoresheet(pagination.current_page);
                },
                error: function(xhr) {
                    const message = xhr.responseJSON?.message || 'Failed to save resit attempts';
                    showFlashMessage(message, 'danger');
                },
                complete: function() {
                    hideSpinner();
                }
            });
        }

        function renderPagination() {
            let html = '';

            html += `
                <li class="page-item ${pagination.current_page === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="#" data-page="${pagination.current_page - 1}">&laquo;</a>
                </li>
            `;

            for (let i = 1; i <= pagination.last_page; i++) {
                if (i === 1 || i === pagination.last_page || (i >= pagination.current_page - 2 && i <= pagination.current_page + 2)) {
                    html += `
                        <li class="page-item ${i === pagination.current_page ? 'active' : ''}">
                            <a class="page-link" href="#" data-page="${i}">${i}</a>
                        </li>
                    `;
                } else if (i === pagination.current_page - 3 || i === pagination.current_page + 3) {
                    html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
                }
            }

            html += `
                <li class="page-item ${pagination.current_page === pagination.last_page ? 'disabled' : ''}">
                    <a class="page-link" href="#" data-page="${pagination.current_page + 1}">&raquo;</a>
                </li>
            `;

            $('#paginationList').html(html);

            $('#paginationList .page-link').on('click', function(e) {
                e.preventDefault();
                const page = parseInt($(this).data('page'));
                if (!isNaN(page) && page >= 1 && page <= pagination.last_page && page !== pagination.current_page) {
                    loadResitScoresheet(page);
                }
            });
        }

        function clearTable() {
            studentResitRows = [];
            $('#resitScoresheetSection').hide();
            $('#saveResitScoresBtn').prop('disabled', true);
        }

        function roundTo2(value) {
            return Math.round((value + Number.EPSILON) * 100) / 100;
        }

        function showFlashMessage(message, type = 'success') {
            const html = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            $('#flash-message').html(html);
        }
    </script>
    @endpush
</x-dashboard.default>
