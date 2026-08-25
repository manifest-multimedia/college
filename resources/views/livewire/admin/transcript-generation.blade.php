    <div class="container-fluid">
        <!-- Header Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-certificate me-2"></i>
                                Student Transcript Generation
                            </h5>
                            <div class="d-flex gap-2">
                                @if(count($selectedStudents) > 0)
                                    <button type="button" 
                                            class="btn btn-success btn-sm"
                                            wire:click="bulkGenerateTranscripts"
                                            wire:loading.attr="disabled">
                                        <i class="fas fa-download me-1"></i>
                                        Generate {{ count($selectedStudents) }} Transcripts
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Filters -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <label class="form-label">Academic Year</label>
                                <select class="form-select" wire:model.live="selectedAcademicYearId">
                                    <option value="">All Years</option>
                                    @foreach($academicYears as $year)
                                        <option value="{{ $year->id }}">{{ $year->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Semester</label>
                                <select class="form-select" wire:model.live="selectedSemesterId">
                                    <option value="">All Semesters</option>
                                    @foreach($semesters as $semester)
                                        <option value="{{ $semester->id }}">{{ $semester->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Program</label>
                                <select class="form-select" wire:model.live="selectedClassId">
                                    <option value="">All Programs</option>
                                    @foreach($collegeClasses as $class)
                                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Cohort</label>
                                <select class="form-select" wire:model.live="selectedCohortId">
                                    <option value="">All Cohorts</option>
                                    @foreach($cohorts as $cohort)
                                        <option value="{{ $cohort->id }}">{{ $cohort->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <label class="form-label">Export Format</label>
                                <div class="btn-group w-100" role="group" aria-label="Export format">
                                    <input type="radio" class="btn-check" name="format" id="format-pdf" value="pdf" wire:model="selectedFormat">
                                    <label class="btn btn-outline-danger" for="format-pdf">
                                        <i class="fas fa-file-pdf me-1"></i>
                                        PDF
                                    </label>

                                    <input type="radio" class="btn-check" name="format" id="format-excel" value="excel" wire:model="selectedFormat">
                                    <label class="btn btn-outline-success" for="format-excel">
                                        <i class="fas fa-file-excel me-1"></i>
                                        Excel
                                    </label>

                                    <input type="radio" class="btn-check" name="format" id="format-csv" value="csv" wire:model="selectedFormat">
                                    <label class="btn btn-outline-info" for="format-csv">
                                        <i class="fas fa-file-csv me-1"></i>
                                        CSV
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Search and Controls -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-search"></i>
                                    </span>
                                    <input type="text" 
                                           class="form-control" 
                                           placeholder="Search by student ID, name, or email..."
                                           wire:model.live.debounce.300ms="search">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex gap-2 justify-content-end">
                                    <button type="button" 
                                            class="btn btn-outline-primary btn-sm"
                                            wire:click="selectAllStudents">
                                        Select All
                                    </button>
                                    <button type="button" 
                                            class="btn btn-outline-secondary btn-sm"
                                            wire:click="deselectAllStudents">
                                        Deselect All
                                    </button>
                                    <select class="form-select form-select-sm" 
                                            style="width: auto;" 
                                            wire:model.live="perPage">
                                        <option value="15">15 per page</option>
                                        <option value="25">25 per page</option>
                                        <option value="50">50 per page</option>
                                        <option value="100">100 per page</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Bulk Progress -->
                        @if($bulkGeneration)
                            <div class="row mb-3">
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <div class="d-flex align-items-center">
                                            <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                                            <span>Generating transcripts... {{ $bulkProgress }}%</span>
                                        </div>
                                        <div class="progress mt-2">
                                            <div class="progress-bar" 
                                                 role="progressbar" 
                                                 style="width: {{ $bulkProgress }}%"
                                                 aria-valuenow="{{ $bulkProgress }}" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>                        </div>

        <!-- Bulk Actions -->
        @if(count($selectedStudents) > 0)
            <div class="row mb-3">
                <div class="col-12">
                    <div class="card border-success">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0">
                                        <i class="fas fa-check-circle text-success me-2"></i>
                                        {{ count($selectedStudents) }} student(s) selected
                                    </h6>
                                    <small class="text-muted">Choose an action to perform on selected students</small>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="button" 
                                            class="btn btn-success"
                                            wire:click="bulkGenerateTranscripts"
                                            wire:loading.attr="disabled"
                                            @if(!$selectedFormat) disabled @endif>
                                        <i class="fas fa-file-export me-1"></i>
                                        <span wire:loading.remove wire:target="bulkGenerateTranscripts">
                                            Generate Bulk Transcripts
                                        </span>
                                        <span wire:loading wire:target="bulkGenerateTranscripts">
                                            <i class="fas fa-spinner fa-spin"></i> Processing...
                                        </span>
                                    </button>
                                    <button type="button" 
                                            class="btn btn-outline-secondary"
                                            wire:click="deselectAllStudents">
                                        <i class="fas fa-times me-1"></i>
                                        Clear Selection
                                    </button>
                                </div>
                            </div>
                            @if(!$selectedFormat)
                                <div class="alert alert-warning mt-2 mb-0">
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    Please select an export format above before generating bulk transcripts.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Students List -->
        <style>
            .student-table-row {
                transition: background-color 0.15s ease-in-out;
            }
            .student-table-row:hover {
                background-color: #F8FAFC !important;
            }
            .student-table-row.selected-row {
                background-color: #EFF6FF !important;
            }
            .student-table-row.selected-row:hover {
                background-color: #DBEAFE !important;
            }
        </style>
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-body border-0 py-3 px-4">
                        <div class="d-flex align-items-center justify-content-between">
                            <h6 class="card-title mb-0 d-flex align-items-center gap-2">
                                <i class="fas fa-users text-primary me-1 fs-5"></i>
                                <span class="fw-bold text-gray-800 fs-5">Students</span>
                                @if($students->count() > 0)
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary fw-semibold px-2.5 py-1 rounded-pill fs-7 ms-1">{{ $students->total() }}</span>
                                @endif
                                @if(count($selectedStudents) > 0)
                                    <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold px-2.5 py-1 rounded-pill fs-7 ms-1">{{ count($selectedStudents) }} selected</span>
                                @endif
                            </h6>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        @if($students->count() > 0)
                            <div class="table-responsive">
                                <table class="table align-middle mb-0" style="border-collapse: separate; border-spacing: 0;">
                                    <thead style="background-color: #F8FAFC; border-bottom: 2px solid #E2E8F0;">
                                        <tr>
                                            <th class="text-center align-middle" style="width: 48px; min-width: 48px; max-width: 48px; padding: 12px 16px;">
                                                <div class="d-flex justify-content-center align-items-center">
                                                    <input type="checkbox" 
                                                           class="form-check-input m-0"
                                                           style="cursor: pointer; width: 16px; height: 16px;"
                                                           @if(count($selectedStudents) > 0 && count($selectedStudents) == $students->count()) checked @endif
                                                           wire:click="@if(count($selectedStudents) == $students->count()) deselectAllStudents @else selectAllStudents @endif"
                                                           title="Select all students">
                                                </div>
                                            </th>
                                            <th class="align-middle fw-semibold text-secondary text-uppercase" style="width: 150px; min-width: 150px; font-size: 0.75rem; letter-spacing: 0.05em; padding: 12px 16px;">Student ID</th>
                                            <th class="align-middle fw-semibold text-secondary text-uppercase" style="min-width: 220px; font-size: 0.75rem; letter-spacing: 0.05em; padding: 12px 16px;">Student Name</th>
                                            <th class="align-middle fw-semibold text-secondary text-uppercase" style="min-width: 240px; font-size: 0.75rem; letter-spacing: 0.05em; padding: 12px 16px;">Email</th>
                                            <th class="align-middle fw-semibold text-secondary text-uppercase" style="min-width: 200px; font-size: 0.75rem; letter-spacing: 0.05em; padding: 12px 16px;">Class</th>
                                            <th class="align-middle fw-semibold text-secondary text-uppercase text-center" style="width: 110px; min-width: 110px; font-size: 0.75rem; letter-spacing: 0.05em; padding: 12px 16px;">Status</th>
                                            <th class="align-middle fw-semibold text-secondary text-uppercase text-end" style="width: 140px; min-width: 140px; font-size: 0.75rem; letter-spacing: 0.05em; padding: 12px 16px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($students as $student)
                                            @php
                                                $isSelected = in_array($student->id, $selectedStudents);
                                            @endphp
                                            <tr class="student-table-row {{ $isSelected ? 'selected-row' : '' }}" 
                                                style="border-bottom: 1px solid #F1F5F9;">
                                                <td class="text-center align-middle" style="width: 48px; min-width: 48px; max-width: 48px; padding: 14px 16px;">
                                                    <div class="d-flex justify-content-center align-items-center">
                                                        <input type="checkbox" 
                                                               class="form-check-input m-0"
                                                               style="cursor: pointer; width: 16px; height: 16px;"
                                                               @if($isSelected) checked @endif
                                                               wire:click="toggleStudentSelection({{ $student->id }})">
                                                    </div>
                                                </td>
                                                <td class="align-middle" style="padding: 14px 16px;">
                                                    <span class="fw-semibold text-gray-900" style="font-variant-numeric: tabular-nums; font-size: 0.875rem; letter-spacing: 0.02em;">
                                                        {{ $student->student_id }}
                                                    </span>
                                                </td>
                                                <td class="align-middle" style="padding: 14px 16px;">
                                                    <span class="fw-semibold text-gray-900 fs-7" title="{{ $student->full_name }}">
                                                        {{ $student->full_name }}
                                                    </span>
                                                </td>
                                                <td class="align-middle" style="padding: 14px 16px;">
                                                    <span class="text-muted fs-7 d-inline-block text-truncate" style="max-width: 220px;" title="{{ $student->email }}">
                                                        {{ $student->email }}
                                                    </span>
                                                </td>
                                                <td class="align-middle" style="padding: 14px 16px;">
                                                    <span class="text-gray-800 fw-medium fs-7">
                                                        {{ $student->collegeClass->name ?? 'N/A' }}
                                                    </span>
                                                </td>
                                                <td class="text-center align-middle" style="padding: 14px 16px;">
                                                    @if(($student->status ?? 'Active') == 'Active')
                                                        <span class="d-inline-flex align-items-center px-2.5 py-1 rounded-pill fw-medium fs-8" style="background: #ECFDF5; color: #047857; border: 1px solid #A7F3D0;">
                                                            <span class="rounded-circle me-1.5" style="width: 5px; height: 5px; background-color: #10B981;"></span>
                                                            Active
                                                        </span>
                                                    @elseif($student->status == 'Inactive')
                                                        <span class="d-inline-flex align-items-center px-2.5 py-1 rounded-pill fw-medium fs-8" style="background: #FEF2F2; color: #B91C1C; border: 1px solid #FECACA;">
                                                            <span class="rounded-circle me-1.5" style="width: 5px; height: 5px; background-color: #EF4444;"></span>
                                                            Inactive
                                                        </span>
                                                    @elseif($student->status == 'Pending')
                                                        <span class="d-inline-flex align-items-center px-2.5 py-1 rounded-pill fw-medium fs-8" style="background: #FFFBEB; color: #B45309; border: 1px solid #FDE68A;">
                                                            <span class="rounded-circle me-1.5" style="width: 5px; height: 5px; background-color: #F59E0B;"></span>
                                                            Pending
                                                        </span>
                                                    @else
                                                        <span class="d-inline-flex align-items-center px-2.5 py-1 rounded-pill fw-medium fs-8" style="background: #F1F5F9; color: #475569; border: 1px solid #E2E8F0;">
                                                            {{ $student->status ?? 'Active' }}
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="text-end align-middle" style="padding: 14px 16px;">
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-primary fw-semibold px-3 py-1 rounded-2 d-inline-flex align-items-center gap-1.5 ms-auto"
                                                            style="font-size: 0.8125rem; transition: all 0.15s ease-in-out;"
                                                            wire:click="generateTranscript({{ $student->id }})"
                                                            wire:loading.attr="disabled"
                                                            title="Generate Transcript">
                                                        <i class="fas fa-file-invoice text-primary fs-7"></i>
                                                        <span wire:loading.remove wire:target="generateTranscript({{ $student->id }})">
                                                            Generate
                                                        </span>
                                                        <span wire:loading wire:target="generateTranscript({{ $student->id }})">
                                                            <i class="fas fa-spinner fa-spin"></i>
                                                        </span>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Pagination -->
                            <div class="d-flex justify-content-between align-items-center px-4 py-3 border-top border-gray-100">
                                <div>
                                    <small class="text-muted fw-medium">
                                        Showing {{ $students->firstItem() }} to {{ $students->lastItem() }} 
                                        of {{ $students->total() }} results
                                    </small>
                                </div>
                                <div>
                                    {{ $students->links() }}
                                </div>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No students found</h5>
                                <p class="text-muted">Adjust your search criteria to find students.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Transcript Modal -->
        @if($showTranscriptModal && $transcriptData)
            <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="fas fa-certificate me-2"></i>
                                Transcript for {{ $transcriptData['student']->student_id }}
                            </h5>
                            <button type="button" class="btn-close" wire:click="closeTranscriptModal"></button>
                        </div>
                        <div class="modal-body">
                            <!-- Student Information -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h6>Student Information</h6>
                                    <table class="table table-sm">
                                        <tr>
                                            <td><strong>Student ID:</strong></td>
                                            <td>{{ $transcriptData['student']->student_id }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Name:</strong></td>
                                            <td>{{ $transcriptData['student']->full_name }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Class:</strong></td>
                                            <td>{{ $transcriptData['student']->collegeClass->name ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Email:</strong></td>
                                            <td>{{ $transcriptData['student']->email }}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h6>Academic Information</h6>
                                    <table class="table table-sm">
                                        <tr>
                                            <td><strong>Academic Year:</strong></td>
                                            <td>{{ $transcriptData['academic_year']->name ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Semester:</strong></td>
                                            <td>{{ $transcriptData['semester']->name ?? 'All Semesters' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Generated:</strong></td>
                                            <td>{{ $transcriptData['generated_at']->format('Y-m-d H:i') }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <!-- Course Grades -->
                            <div class="mb-4">
                                <h6>Course Grades</h6>
                                @if(count($transcriptData['transcript_entries']) > 0)
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Course Code</th>
                                                    <th>Course Name</th>
                                                    <th>Credit Hours</th>
                                                    <th>Online (%)</th>
                                                    <th>Offline (%)</th>
                                                    <th>Final (%)</th>
                                                    <th>Grade</th>
                                                    <th>Points</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($transcriptData['transcript_entries'] as $entry)
                                                    <tr>
                                                        <td><strong>{{ $entry['subject_code'] }}</strong></td>
                                                        <td>{{ $entry['subject_name'] }}</td>
                                                        <td>{{ $entry['credit_hours'] }}</td>
                                                        <td>{{ $entry['online_score'] ?? '-' }}</td>
                                                        <td>{{ $entry['offline_score'] ?? '-' }}</td>
                                                        <td><strong>{{ $entry['final_score'] }}</strong></td>
                                                        <td>
                                                            <span class="badge bg-{{ $entry['letter_grade'] == 'F' ? 'danger' : 'success' }}">
                                                                {{ $entry['letter_grade'] }}
                                                            </span>
                                                        </td>
                                                        <td>{{ $entry['grade_points'] }}</td>
                                                        <td>
                                                            <span class="badge bg-{{ $entry['status'] == 'PASS' ? 'success' : 'danger' }}">
                                                                {{ $entry['status'] }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        No course grades found for the selected criteria.
                                    </div>
                                @endif
                            </div>

                            <!-- Summary -->
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Academic Summary</h6>
                                    <table class="table table-sm table-bordered">
                                        <tr>
                                            <td><strong>Total Credit Hours Attempted:</strong></td>
                                            <td>{{ $transcriptData['summary']['total_credit_hours_attempted'] }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Total Credit Hours Earned:</strong></td>
                                            <td>{{ $transcriptData['summary']['total_credit_hours_earned'] }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Total Grade Points:</strong></td>
                                            <td>{{ $transcriptData['summary']['total_grade_points'] }}</td>
                                        </tr>
                                        <tr class="table-primary">
                                            <td><strong>Semester GPA:</strong></td>
                                            <td><strong>{{ $transcriptData['summary']['semester_gpa'] }}</strong></td>
                                        </tr>
                                        <tr class="table-primary">
                                            <td><strong>Cumulative GPA:</strong></td>
                                            <td><strong>{{ $transcriptData['summary']['cumulative_gpa'] }}</strong></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="closeTranscriptModal">
                                <i class="fas fa-times me-1"></i>
                                Close
                            </button>
                            <div class="btn-group ms-2">
                                <button type="button" 
                                        class="btn btn-info" 
                                        wire:click="downloadTranscript('csv')"
                                        wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="downloadTranscript('csv')">
                                        <i class="fas fa-file-csv me-1"></i>
                                        CSV
                                    </span>
                                    <span wire:loading wire:target="downloadTranscript('csv')">
                                        <i class="fas fa-spinner fa-spin me-1"></i>
                                        Generating...
                                    </span>
                                </button>
                                <button type="button" 
                                        class="btn btn-success" 
                                        wire:click="downloadTranscript('excel')"
                                        wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="downloadTranscript('excel')">
                                        <i class="fas fa-file-excel me-1"></i>
                                        Excel
                                    </span>
                                    <span wire:loading wire:target="downloadTranscript('excel')">
                                        <i class="fas fa-spinner fa-spin me-1"></i>
                                        Generating...
                                    </span>
                                </button>
                                <button type="button" 
                                        class="btn btn-danger" 
                                        wire:click="downloadTranscript('pdf')"
                                        wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="downloadTranscript('pdf')">
                                        <i class="fas fa-file-pdf me-1"></i>
                                        PDF
                                    </span>
                                    <span wire:loading wire:target="downloadTranscript('pdf')">
                                        <i class="fas fa-spinner fa-spin me-1"></i>
                                        Generating...
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
