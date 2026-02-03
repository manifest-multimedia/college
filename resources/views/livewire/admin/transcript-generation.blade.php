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
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-users me-2"></i>
                            Students
                            @if($students->count() > 0)
                                <span class="badge bg-primary ms-2">{{ $students->total() }}</span>
                            @endif
                            @if(count($selectedStudents) > 0)
                                <span class="badge bg-success ms-2">{{ count($selectedStudents) }} selected</span>
                            @endif
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        @if($students->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="50">
                                                <input type="checkbox" 
                                                       class="form-check-input"
                                                       @if(count($selectedStudents) > 0 && count($selectedStudents) == $students->count()) checked @endif
                                                       wire:click="@if(count($selectedStudents) == $students->count()) deselectAllStudents @else selectAllStudents @endif">
                                            </th>
                                            <th>Student ID</th>
                                            <th>Student Name</th>
                                            <th>Email</th>
                                            <th>Class</th>
                                            <th>Status</th>
                                            <th width="120">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($students as $student)
                                            <tr>
                                                <td>
                                                    <input type="checkbox" 
                                                           class="form-check-input"
                                                           @if(in_array($student->id, $selectedStudents)) checked @endif
                                                           wire:click="toggleStudentSelection({{ $student->id }})">
                                                </td>
                                                <td>
                                                    <strong>{{ $student->student_id }}</strong>
                                                </td>
                                                <td>{{ $student->full_name }}</td>
                                                <td>
                                                    <small class="text-muted">{{ $student->email }}</small>
                                                </td>
                                                <td>{{ $student->collegeClass->name ?? 'N/A' }}</td>
                                                <td>
                                                    <span class="badge bg-success">Active</span>
                                                </td>
                                                <td>
                                                    <button type="button" 
                                                            class="btn btn-primary btn-sm"
                                                            wire:click="generateTranscript({{ $student->id }})"
                                                            wire:loading.attr="disabled"
                                                            title="Generate Transcript">
                                                        <i class="fas fa-certificate"></i>
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
                            <div class="d-flex justify-content-between align-items-center p-3">
                                <div>
                                    <small class="text-muted">
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
