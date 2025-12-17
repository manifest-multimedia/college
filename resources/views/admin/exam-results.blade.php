<x-dashboard.default title="Exam Results">
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="card-title">
                        <h3>
                            <i class="ki-duotone ki-chart fs-2 me-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            Exam Results
                        </h3>
                    </div>
                    <div>
                        <p class="text-muted mb-0">View and export exam results for all students</p>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Search & Filter Section -->
                    <div class="mb-4">
                        <div class="row g-3 align-items-end">
                            <!-- Exam Selection -->
                            <div class="col-md-5">
                                <label for="exam_id" class="form-label">Select Exam</label>
                                <select id="exam_id" class="form-select">
                                    <option value="">-- Select an Exam --</option>
                                    @foreach($exams as $exam)
                                        <option value="{{ $exam->id }}">
                                            {{ $exam->course->name ?? 'Unknown Course' }} ({{ $exam->created_at->format('d M, Y') }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <!-- Class Filter -->
                            <div class="col-md-3 mb-3">
                                <label for="college_class_id" class="form-label">Filter by Class</label>
                                <select id="college_class_id" class="form-select">
                                    <option value="">All Classes</option>
                                    @foreach($collegeClasses as $class)
                                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Cohort Filter -->
                            <div class="col-md-3 mb-3">
                                <label for="cohort_id" class="form-label">Filter by Cohort</label>
                                <select id="cohort_id" class="form-select">
                                    <option value="">All Cohorts</option>
                                    @foreach($cohorts as $cohort)
                                        <option value="{{ $cohort->id }}">{{ $cohort->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <!-- Search -->
                            <div class="col-md-3">
                                <label for="search" class="form-label">Search</label>
                                <input type="text" id="search" class="form-control" placeholder="Student ID, Name, Email...">
                            </div>
                            
                            <!-- Export Options -->
                            <div class="col-md-1">
                                <div class="dropdown">
                                    <button class="btn btn-primary dropdown-toggle" type="button" id="exportDropdown" 
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                        Export
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                                        <li><button id="export-excel" class="dropdown-item">Excel</button></li>
                                        <li><button id="export-pdf" class="dropdown-item">PDF</button></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Results Stats Section -->
                    <div id="stats-section" class="mb-4" style="display: none;">
                        <div class="row g-3">
                            <!-- Total Students Card -->
                            <div class="col-md-2">
                                <div class="card bg-light h-100">
                                    <div class="card-body text-center">
                                        <h6 class="card-title text-muted mb-1">Students</h6>
                                        <h3 class="mb-0" id="stat-students">0</h3>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Average Score Card -->
                            <div class="col-md-2">
                                <div class="card bg-light h-100">
                                    <div class="card-body text-center">
                                        <h6 class="card-title text-muted mb-1">Average</h6>
                                        <h3 class="mb-0" id="stat-average">0%</h3>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Pass Rate Card -->
                            <div class="col-md-2">
                                <div class="card bg-light h-100">
                                    <div class="card-body text-center">
                                        <h6 class="card-title text-muted mb-1">Pass Rate</h6>
                                        <h3 class="mb-0" id="stat-pass-rate">0%</h3>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Highest Score Card -->
                            <div class="col-md-2">
                                <div class="card bg-light h-100">
                                    <div class="card-body text-center">
                                        <h6 class="card-title text-muted mb-1">Highest</h6>
                                        <h3 class="mb-0" id="stat-highest">0%</h3>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Lowest Score Card -->
                            <div class="col-md-2">
                                <div class="card bg-light h-100">
                                    <div class="card-body text-center">
                                        <h6 class="card-title text-muted mb-1">Lowest</h6>
                                        <h3 class="mb-0" id="stat-lowest">0%</h3>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Records Per Page -->
                            <div class="col-md-2">
                                <div class="card bg-light h-100">
                                    <div class="card-body text-center position-relative">
                                        <h6 class="card-title text-muted mb-1">Display</h6>
                                        <select id="per_page" class="form-select form-select-sm mx-auto" style="max-width: 80px;">
                                            <option value="15">15</option>
                                            <option value="25">25</option>
                                            <option value="50">50</option>
                                            <option value="100">100</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Loading Overlay (outside results section so it can be shown independently) -->
                    <div id="loading-overlay" class="d-none">
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <div class="mt-3">
                                <h5>Loading results...</h5>
                                <p class="text-muted">Please wait while we process the data</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Results Table Section -->
                    <div id="results-section" style="display: none;">
                        <div class="position-relative" style="min-height: 400px;">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th data-sort="student_id" class="cursor-pointer">
                                                Student ID <i class="bi bi-arrow-up-down"></i>
                                            </th>
                                            <th data-sort="name" class="cursor-pointer">
                                                Name <i class="bi bi-arrow-up-down"></i>
                                            </th>
                                            <th data-sort="class" class="cursor-pointer">
                                                Class <i class="bi bi-arrow-up-down"></i>
                                            </th>
                                            <th data-sort="completed_at" class="cursor-pointer">
                                                Date <i class="bi bi-arrow-up-down"></i>
                                            </th>
                                            <th data-sort="score" class="cursor-pointer">
                                                Score <i class="bi bi-arrow-up-down"></i>
                                            </th>
                                            <th data-sort="answered" class="cursor-pointer">
                                                Answered <i class="bi bi-arrow-up-down"></i>
                                            </th>
                                            <th data-sort="score_percentage" class="cursor-pointer">
                                                Percentage <i class="bi bi-arrow-up-down"></i>
                                            </th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="results-tbody">
                                        <!-- Results will be populated via AJAX -->
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Pagination Section -->
                            <div class="d-flex justify-content-center mt-4" id="pagination-container">
                                <!-- Pagination will be populated via AJAX -->
                            </div>
                        </div>
                    </div>
                    
                    <!-- No Exam Selected Message -->
                    <div id="no-exam-message" class="alert alert-info">
                        <h4 class="alert-heading">Select an Exam</h4>
                        <p>Please select an exam to view and export results.</p>
                    </div>
                    
                    <!-- No Results Message -->
                    <div id="no-results-message" class="alert alert-info" style="display: none;">
                        <h4 class="alert-heading">No Results Found</h4>
                        <p>No exam results matching the selected criteria were found. Please try different filters or select another exam.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .cursor-pointer {
        cursor: pointer;
    }
    .cursor-pointer:hover {
        background-color: #f8f9fa;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Setup CSRF token for AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    
    let currentPage = 1;
    let sortField = 'score_percentage';
    let sortDirection = 'desc';
    let searchTimeout = null;
    
    // Get URL parameters
    function getUrlParams() {
        const urlParams = new URLSearchParams(window.location.search);
        return {
            exam_id: urlParams.get('exam_id') || '',
            search: urlParams.get('search') || '',
            college_class_id: urlParams.get('college_class_id') || '',
            cohort_id: urlParams.get('cohort_id') || '',
            per_page: urlParams.get('per_page') || '15',
            page: urlParams.get('page') || '1'
        };
    }
    
    // Update URL without reloading page
    function updateUrl() {
        const params = new URLSearchParams();
        const examId = $('#exam_id').val();
        const search = $('#search').val();
        const collegeClassId = $('#college_class_id').val();
        const cohortId = $('#cohort_id').val();
        const perPage = $('#per_page').val();
        
        if (examId) params.set('exam_id', examId);
        if (search) params.set('search', search);
        if (collegeClassId) params.set('college_class_id', collegeClassId);
        if (cohortId) params.set('cohort_id', cohortId);
        if (perPage !== '15') params.set('per_page', perPage);
        if (currentPage > 1) params.set('page', currentPage);
        
        const newUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
        window.history.pushState({}, '', newUrl);
    }
    
    // Initialize from URL parameters
    function initializeFromUrl() {
        const params = getUrlParams();
        if (params.exam_id) $('#exam_id').val(params.exam_id);
        if (params.search) $('#search').val(params.search);
        if (params.college_class_id) $('#college_class_id').val(params.college_class_id);
        if (params.cohort_id) $('#cohort_id').val(params.cohort_id);
        if (params.per_page) $('#per_page').val(params.per_page);
        if (params.page) currentPage = parseInt(params.page);
        
        if (params.exam_id) {
            loadResults();
        }
    }
    
    // Show loading state
    function showLoading() {
        $('#loading-overlay').removeClass('d-none');
        $('#no-exam-message').hide();
        $('#stats-section').hide();
        $('#results-section').hide();
        $('#no-results-message').hide();
    }
    
    // Hide loading state
    function hideLoading() {
        $('#loading-overlay').addClass('d-none');
    }
    
    // Load results
    function loadResults() {
        const examId = $('#exam_id').val();
        
        if (!examId) {
            $('#loading-overlay').addClass('d-none');
            $('#no-exam-message').show();
            $('#stats-section').hide();
            $('#results-section').hide();
            $('#no-results-message').hide();
            return;
        }
        
        showLoading();
        
        const data = {
            exam_id: examId,
            search: $('#search').val(),
            college_class_id: $('#college_class_id').val(),
            cohort_id: $('#cohort_id').val(),
            per_page: $('#per_page').val(),
            page: currentPage,
            sort_field: sortField,
            sort_direction: sortDirection
        };
        
        $.ajax({
            url: '{{ route("admin.exam-results.get") }}',
            type: 'GET',
            data: data,
            success: function(response) {
                hideLoading();
                
                if (response.results && response.results.length > 0) {
                    updateStats(response.stats);
                    updateTable(response.results);
                    updatePagination(response.pagination);
                    $('#stats-section').show();
                    $('#results-section').show();
                    $('#no-results-message').hide();
                } else {
                    $('#stats-section').hide();
                    $('#results-section').hide();
                    $('#no-results-message').show();
                }
                
                updateUrl();
            },
            error: function(xhr) {
                hideLoading();
                console.error('Error loading results:', xhr);
                alert('Failed to load results: ' + (xhr.responseJSON?.error || 'Unknown error'));
            }
        });
    }
    
    // Update stats cards
    function updateStats(stats) {
        $('#stat-students').text(stats.totalStudents);
        $('#stat-average').text(stats.averageScore + '%');
        $('#stat-pass-rate').text(stats.passRate + '%');
        $('#stat-highest').text(stats.highestScore + '%');
        $('#stat-lowest').text(stats.lowestScore + '%');
    }
    
    // Update table
    function updateTable(results) {
        const tbody = $('#results-tbody');
        tbody.empty();
        
        results.forEach(function(result) {
            let statusClass = 'danger';
            let statusText = 'Failed';
            
            if (result.score_percentage >= 80) {
                statusClass = 'success';
                statusText = 'Excellent';
            } else if (result.score_percentage >= 70) {
                statusClass = 'primary';
                statusText = 'Very Good';
            } else if (result.score_percentage >= 60) {
                statusClass = 'info';
                statusText = 'Good';
            } else if (result.score_percentage >= 50) {
                statusClass = 'warning';
                statusText = 'Pass';
            }
            
            const row = `
                <tr>
                    <td>${result.student_id}</td>
                    <td>${result.name}</td>
                    <td>${result.class}</td>
                    <td>${result.completed_at}</td>
                    <td>${result.score}</td>
                    <td>${result.answered}</td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="me-2">${result.score_percentage}%</div>
                            <div class="progress w-100" style="height: 6px;">
                                <div class="progress-bar bg-${statusClass}" 
                                    role="progressbar" 
                                    style="width: ${result.score_percentage}%" 
                                    aria-valuenow="${result.score_percentage}" 
                                    aria-valuemin="0" 
                                    aria-valuemax="100">
                                </div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge bg-${statusClass}">${statusText}</span>
                    </td>
                    <td>
                        <a href="{{ route('exam.response.tracker') }}?student_id=${result.student_id}&exam_id=${$('#exam_id').val()}&session_id=${result.session_id}" 
                           class="btn btn-sm btn-primary">
                            View Details
                        </a>
                    </td>
                </tr>
            `;
            tbody.append(row);
        });
        
        // Update sort indicators
        $('th[data-sort]').each(function() {
            const field = $(this).data('sort');
            if (field === sortField) {
                const icon = sortDirection === 'asc' ? 'bi-arrow-up' : 'bi-arrow-down';
                $(this).find('i').attr('class', 'bi ' + icon);
            } else {
                $(this).find('i').attr('class', 'bi bi-arrow-up-down');
            }
        });
    }
    
    // Update pagination
    function updatePagination(pagination) {
        const container = $('#pagination-container');
        container.empty();
        
        if (pagination.last_page <= 1) {
            return;
        }
        
        const nav = $('<nav aria-label="Page navigation"></nav>');
        const ul = $('<ul class="pagination"></ul>');
        
        // Previous button
        if (pagination.current_page > 1) {
            ul.append(`<li class="page-item"><a class="page-link" href="#" data-page="${pagination.current_page - 1}">Previous</a></li>`);
        } else {
            ul.append('<li class="page-item disabled"><span class="page-link">Previous</span></li>');
        }
        
        // Page numbers
        const startPage = Math.max(1, pagination.current_page - 2);
        const endPage = Math.min(pagination.last_page, pagination.current_page + 2);
        
        if (startPage > 1) {
            ul.append('<li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>');
            if (startPage > 2) {
                ul.append('<li class="page-item disabled"><span class="page-link">...</span></li>');
            }
        }
        
        for (let i = startPage; i <= endPage; i++) {
            if (i === pagination.current_page) {
                ul.append(`<li class="page-item active"><span class="page-link">${i}</span></li>`);
            } else {
                ul.append(`<li class="page-item"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`);
            }
        }
        
        if (endPage < pagination.last_page) {
            if (endPage < pagination.last_page - 1) {
                ul.append('<li class="page-item disabled"><span class="page-link">...</span></li>');
            }
            ul.append(`<li class="page-item"><a class="page-link" href="#" data-page="${pagination.last_page}">${pagination.last_page}</a></li>`);
        }
        
        // Next button
        if (pagination.current_page < pagination.last_page) {
            ul.append(`<li class="page-item"><a class="page-link" href="#" data-page="${pagination.current_page + 1}">Next</a></li>`);
        } else {
            ul.append('<li class="page-item disabled"><span class="page-link">Next</span></li>');
        }
        
        nav.append(ul);
        container.append(nav);
    }
    
    // Event handlers
    $('#exam_id').on('change', function() {
        currentPage = 1;
        loadResults();
    });
    
    $('#college_class_id, #cohort_id').on('change', function() {
        currentPage = 1;
        loadResults();
    });
    
    $('#search').on('keyup', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            currentPage = 1;
            loadResults();
        }, 500);
    });
    
    $('#per_page').on('change', function() {
        currentPage = 1;
        loadResults();
    });
    
    $(document).on('click', '.page-link', function(e) {
        e.preventDefault();
        const page = $(this).data('page');
        if (page) {
            currentPage = page;
            loadResults();
        }
    });
    
    $('th[data-sort]').on('click', function() {
        const field = $(this).data('sort');
        if (sortField === field) {
            sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            sortField = field;
            sortDirection = 'asc';
        }
        loadResults();
    });
    
    $('#export-excel').on('click', function() {
        const examId = $('#exam_id').val();
        if (!examId) {
            alert('Please select an exam first');
            return;
        }
        
        const params = new URLSearchParams({
            exam_id: examId,
            college_class_id: $('#college_class_id').val(),
            cohort_id: $('#cohort_id').val(),
            search: $('#search').val()
        });
        
        window.location.href = '{{ route("admin.exam-results.export.excel") }}?' + params.toString();
    });
    
    $('#export-pdf').on('click', function() {
        const examId = $('#exam_id').val();
        if (!examId) {
            alert('Please select an exam first');
            return;
        }
        
        const params = new URLSearchParams({
            exam_id: examId,
            college_class_id: $('#college_class_id').val(),
            cohort_id: $('#cohort_id').val(),
            search: $('#search').val()
        });
        
        window.location.href = '{{ route("admin.exam-results.export.pdf") }}?' + params.toString();
    });
    
    // Initialize
    initializeFromUrl();
});
</script>
@endpush
</x-dashboard.default>
