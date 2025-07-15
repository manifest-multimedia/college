    <div class="container-fluid">
        <!-- Header Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-clipboard-list me-2"></i>
                                Offline Exam Scores Management
                            </h5>
                            <div class="d-flex gap-2">
                                @if($selectedExamId)
                                    <button type="button" 
                                            class="btn btn-outline-success btn-sm"
                                            wire:click="exportScores"
                                            title="Export scores to Excel">
                                        <i class="fas fa-download me-1"></i>
                                        Export
                                    </button>
                                    <button type="button" 
                                            class="btn btn-outline-primary btn-sm"
                                            wire:click="toggleBulkEntry">
                                        <i class="fas fa-users me-1"></i>
                                        {{ $bulkEntry ? 'Single Entry' : 'Bulk Entry' }}
                                    </button>
                                    @if(!$bulkEntry)
                                        <button type="button" 
                                                class="btn btn-primary btn-sm"
                                                wire:click="createScore">
                                            <i class="fas fa-plus me-1"></i>
                                            Add Score
                                        </button>
                                    @endif
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
                                <label class="form-label">Offline Exam</label>
                                <select class="form-select" wire:model.live="selectedExamId">
                                    <option value="">Select Exam</option>
                                    @foreach($offlineExams as $exam)
                                        <option value="{{ $exam->id }}">
                                            {{ $exam->title }} - {{ $exam->course->name ?? 'N/A' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Class Filter</label>
                                <select class="form-select" wire:model.live="selectedClassId">
                                    <option value="">All Classes</option>
                                    @foreach($collegeClasses as $class)
                                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        @if($selectedExamId)
                            <!-- Search Bar -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-search"></i>
                                        </span>
                                        <input type="text" 
                                               class="form-control" 
                                               placeholder="Search by student ID or name..."
                                               wire:model.live.debounce.300ms="search">
                                    </div>
                                </div>
                                <div class="col-md-6 text-end">
                                    <select class="form-select d-inline-block w-auto" wire:model.live="perPage">
                                        <option value="15">15 per page</option>
                                        <option value="25">25 per page</option>
                                        <option value="50">50 per page</option>
                                        <option value="100">100 per page</option>
                                    </select>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if($selectedExamId)
            <!-- Bulk Entry Section -->
            @if($bulkEntry)
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="card-title mb-0">
                                        <i class="fas fa-users me-2"></i>
                                        Bulk Score Entry
                                    </h6>
                                    <button type="button" 
                                            class="btn btn-success btn-sm"
                                            wire:click="saveBulkScores"
                                            @if(empty($studentScores) || $isBulkSaving) disabled @endif>
                                        @if($isBulkSaving)
                                            <div class="spinner-border spinner-border-sm me-1" role="status"></div>
                                            Saving... {{ $bulkProgress }}%
                                        @else
                                            <i class="fas fa-save me-1"></i>
                                            Save All Scores
                                        @endif
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                @if(!empty($studentScores))
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <label class="form-label">Exam Date</label>
                                            <input type="date" 
                                                   class="form-control" 
                                                   wire:model="scoreForm.exam_date">
                                        </div>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width: 15%">Student ID</th>
                                                    <th style="width: 25%">Student Name</th>
                                                    <th style="width: 15%">Score</th>
                                                    <th style="width: 15%">Total Marks</th>
                                                    <th style="width: 30%">Remarks</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($studentScores as $index => $studentScore)
                                                    <tr>
                                                        <td>{{ $studentScore['student_number'] }}</td>
                                                        <td>{{ $studentScore['student_name'] }}</td>
                                                        <td>
                                                            <input type="number" 
                                                                   class="form-control form-control-sm" 
                                                                   step="0.01" 
                                                                   min="0"
                                                                   wire:model="studentScores.{{ $index }}.score"
                                                                   placeholder="Score">
                                                        </td>
                                                        <td>
                                                            <input type="number" 
                                                                   class="form-control form-control-sm" 
                                                                   step="0.01" 
                                                                   min="1"
                                                                   wire:model="studentScores.{{ $index }}.total_marks">
                                                        </td>
                                                        <td>
                                                            <input type="text" 
                                                                   class="form-control form-control-sm" 
                                                                   wire:model="studentScores.{{ $index }}.remarks"
                                                                   placeholder="Optional remarks">
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-center py-4">
                                        <p class="text-muted">Please select a class to load students for bulk entry.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Scores List -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-list me-2"></i>
                                Recorded Scores
                                @if($scores->count() > 0)
                                    <span class="badge bg-primary ms-2">{{ $scores->total() }}</span>
                                @endif
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            @if($scores->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Student</th>
                                                <th>Class</th>
                                                <th>Score</th>
                                                <th>Percentage</th>
                                                <th>Grade</th>
                                                <th>Status</th>
                                                <th>Recorded By</th>
                                                <th>Date</th>
                                                <th width="100">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($scores as $score)
                                                <tr>
                                                    <td>
                                                        <div>
                                                            <strong>{{ $score->student->student_id }}</strong>
                                                            <br>
                                                            <small class="text-muted">{{ $score->student->full_name }}</small>
                                                        </div>
                                                    </td>
                                                    <td>{{ $score->student->collegeClass->name ?? 'N/A' }}</td>
                                                    <td>
                                                        <span class="fw-bold">{{ $score->score }}</span>
                                                        <small class="text-muted">/ {{ $score->total_marks }}</small>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-{{ $score->percentage >= 50 ? 'success' : 'danger' }}">
                                                            {{ number_format($score->percentage, 1) }}%
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-secondary">{{ $score->grade_letter }}</span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-{{ $score->is_passed ? 'success' : 'danger' }}">
                                                            {{ $score->is_passed ? 'Passed' : 'Failed' }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <small>{{ $score->recordedBy->name ?? 'N/A' }}</small>
                                                    </td>
                                                    <td>
                                                        <small>{{ $score->created_at->format('d/m/Y') }}</small>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm">
                                                            <button type="button" 
                                                                    class="btn btn-outline-primary"
                                                                    wire:click="editScore({{ $score->id }})"
                                                                    title="Edit Score">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <button type="button" 
                                                                    class="btn btn-outline-danger"
                                                                    wire:click="deleteScore({{ $score->id }})"
                                                                    wire:confirm="Are you sure you want to delete this score?"
                                                                    title="Delete Score">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
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
                                            Showing {{ $scores->firstItem() }} to {{ $scores->lastItem() }} 
                                            of {{ $scores->total() }} results
                                        </small>
                                    </div>
                                    <div>
                                        {{ $scores->links() }}
                                    </div>
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">No scores recorded yet</h5>
                                    <p class="text-muted">Start by adding scores for the selected exam.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @else
            <!-- No Exam Selected -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-clipboard-list fa-4x text-muted mb-3"></i>
                            <h4 class="text-muted">Select an Offline Exam</h4>
                            <p class="text-muted">Choose an offline exam from the dropdown above to manage scores.</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Score Form Modal -->
        @if($showForm)
            <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                {{ $editingScore ? 'Edit Score' : 'Add New Score' }}
                            </h5>
                            <button type="button" class="btn-close" wire:click="$set('showForm', false)"></button>
                        </div>
                        <div class="modal-body">
                            <form wire:submit.prevent="saveScore">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label">Student</label>
                                        <select class="form-select @error('scoreForm.student_id') is-invalid @enderror" 
                                                wire:model="scoreForm.student_id">
                                            <option value="">Select Student</option>
                                            @foreach($students as $id => $name)
                                                <option value="{{ $id }}">{{ $name }}</option>
                                            @endforeach
                                        </select>
                                        @error('scoreForm.student_id') 
                                            <div class="invalid-feedback">{{ $message }}</div> 
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Score Obtained</label>
                                        <input type="number" 
                                               class="form-control @error('scoreForm.score') is-invalid @enderror" 
                                               step="0.01" 
                                               min="0"
                                               wire:model="scoreForm.score"
                                               placeholder="Enter score">
                                        @error('scoreForm.score') 
                                            <div class="invalid-feedback">{{ $message }}</div> 
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Total Marks</label>
                                        <input type="number" 
                                               class="form-control @error('scoreForm.total_marks') is-invalid @enderror" 
                                               step="0.01" 
                                               min="1"
                                               wire:model="scoreForm.total_marks"
                                               placeholder="Total marks">
                                        @error('scoreForm.total_marks') 
                                            <div class="invalid-feedback">{{ $message }}</div> 
                                        @enderror
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">Exam Date</label>
                                        <input type="date" 
                                               class="form-control @error('scoreForm.exam_date') is-invalid @enderror" 
                                               wire:model="scoreForm.exam_date">
                                        @error('scoreForm.exam_date') 
                                            <div class="invalid-feedback">{{ $message }}</div> 
                                        @enderror
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">Remarks (Optional)</label>
                                        <textarea class="form-control @error('scoreForm.remarks') is-invalid @enderror" 
                                                  rows="3"
                                                  wire:model="scoreForm.remarks"
                                                  placeholder="Any additional comments or remarks..."></textarea>
                                        @error('scoreForm.remarks') 
                                            <div class="invalid-feedback">{{ $message }}</div> 
                                        @enderror
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="$set('showForm', false)">
                                Cancel
                            </button>
                            <button type="button" class="btn btn-primary" wire:click="saveScore">
                                <i class="fas fa-save me-1"></i>
                                {{ $editingScore ? 'Update Score' : 'Save Score' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

