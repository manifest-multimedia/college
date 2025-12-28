<div>
    <div class="container-fluid py-4">
        
        {{-- Flash Messages --}}
        @if (session()->has('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session()->has('info'))
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="fas fa-info-circle me-2"></i>{{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Filter Section --}}
        <div class="card mb-4">
            <div class="card-header pb-0">
                <h6 class="card-title">Assessment Score Management</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="course" class="form-label">Course <span class="text-danger">*</span></label>
                        <select wire:model="selectedCourseId" id="course" class="form-select">
                            <option value="">Select Course</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}">{{ $course->name }}</option>
                            @endforeach
                        </select>
                        @error('selectedCourseId') <span class="text-danger text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-3">
                        <label for="class" class="form-label">Class <span class="text-danger">*</span></label>
                        <select wire:model="selectedClassId" id="class" class="form-select">
                            <option value="">Select Class</option>
                            @foreach($collegeClasses as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                        @error('selectedClassId') <span class="text-danger text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-3">
                        <label for="academicYear" class="form-label">Academic Year <span class="text-danger">*</span></label>
                        <select wire:model="selectedAcademicYearId" id="academicYear" class="form-select">
                            <option value="">Select Year</option>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}">{{ $year->name }}</option>
                            @endforeach
                        </select>
                        @error('selectedAcademicYearId') <span class="text-danger text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-3">
                        <label for="semester" class="form-label">Semester <span class="text-danger">*</span></label>
                        <select wire:model="selectedSemesterId" id="semester" class="form-select">
                            <option value="">Select Semester</option>
                            @foreach($semesters as $semester)
                                <option value="{{ $semester->id }}">{{ $semester->name }}</option>
                            @endforeach
                        </select>
                        @error('selectedSemesterId') <span class="text-danger text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="mt-3">
                    <button wire:click="loadScoresheet" class="btn btn-primary" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="loadScoresheet">
                            <i class="fas fa-table me-2"></i>Load Scoresheet
                        </span>
                        <span wire:loading wire:target="loadScoresheet">
                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            Loading...
                        </span>
                    </button>

                    <button wire:click="downloadExcelTemplate" class="btn btn-outline-success" 
                            @if(!$selectedCourseId || !$selectedClassId || !$selectedAcademicYearId || !$selectedSemesterId) disabled @endif
                            wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="downloadExcelTemplate">
                            <i class="fas fa-download me-2"></i>Download Excel Template
                        </span>
                        <span wire:loading wire:target="downloadExcelTemplate">
                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            Generating...
                        </span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Weight Configuration Display --}}
        @if($isLoaded)
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">Grading Weights</h6>
                        <div class="d-flex gap-4 flex-wrap">
                            <span class="badge bg-gradient-primary px-3 py-2">
                                Assignments ({{ $assignmentCount }}): <strong>{{ $assignmentWeight }}%</strong>
                            </span>
                            <span class="badge bg-gradient-info px-3 py-2">
                                Mid-Semester: <strong>{{ $midSemesterWeight }}%</strong>
                            </span>
                            <span class="badge bg-gradient-success px-3 py-2">
                                End-Semester: <strong>{{ $endSemesterWeight }}%</strong>
                            </span>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <div class="btn-group" role="group">
                            <button wire:click="removeAssignmentColumn" class="btn btn-sm btn-outline-danger" 
                                    @if($assignmentCount <= 3) disabled @endif
                                    title="Remove assignment column">
                                <i class="fas fa-minus"></i>
                            </button>
                            <button wire:click="addAssignmentColumn" class="btn btn-sm btn-outline-success" 
                                    @if($assignmentCount >= 5) disabled @endif
                                    title="Add assignment column">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                        <button wire:click="toggleWeightConfig" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-cog me-1"></i>Configure Weights
                        </button>
                    </div>
                </div>

                {{-- Weight Configuration Modal --}}
                @if($showWeightConfig)
                <div class="mt-4 p-4 border rounded bg-light">
                    <h6 class="mb-3">Configure Grading Weights</h6>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="assignmentWeight" class="form-label">Assignment Weight (%)</label>
                            <input type="number" wire:model="assignmentWeight" id="assignmentWeight" class="form-control" min="0" max="100" step="0.01">
                            @error('assignmentWeight') <span class="text-danger text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="midSemesterWeight" class="form-label">Mid-Semester Weight (%)</label>
                            <input type="number" wire:model="midSemesterWeight" id="midSemesterWeight" class="form-control" min="0" max="100" step="0.01">
                            @error('midSemesterWeight') <span class="text-danger text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="endSemesterWeight" class="form-label">End-Semester Weight (%)</label>
                            <input type="number" wire:model="endSemesterWeight" id="endSemesterWeight" class="form-control" min="0" max="100" step="0.01">
                            @error('endSemesterWeight') <span class="text-danger text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="mt-3 d-flex gap-2">
                        <button wire:click="saveWeightConfiguration" class="btn btn-primary btn-sm">
                            <i class="fas fa-save me-1"></i>Save & Recalculate
                        </button>
                        <button wire:click="toggleWeightConfig" class="btn btn-secondary btn-sm">
                            <i class="fas fa-times me-1"></i>Cancel
                        </button>
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Excel Import Section --}}
        @if($isLoaded)
        <div class="card mb-4">
            <div class="card-header pb-0">
                <h6>Excel Import</h6>
            </div>
            <div class="card-body">
                <div class="row align-items-end">
                    <div class="col-md-6">
                        <label for="importFile" class="form-label">Upload Excel File</label>
                        <input type="file" wire:model="importFile" id="importFile" class="form-control" accept=".xlsx,.xls">
                        @error('importFile') <span class="text-danger text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-6">
                        <button wire:click="importFromExcel" class="btn btn-primary" 
                                @if(!$importFile) disabled @endif 
                                wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="importFromExcel">
                                <i class="fas fa-upload me-2"></i>Preview Import
                            </span>
                            <span wire:loading wire:target="importFromExcel">
                                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                Processing...
                            </span>
                        </button>
                    </div>
                </div>

                @if(!empty($importErrors))
                <div class="alert alert-danger mt-3">
                    <h6 class="alert-heading">Import Validation Errors ({{ count($importErrors) }})</h6>
                    <hr>
                    <ul class="mb-0">
                        @foreach($importErrors as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
            </div>
        </div>

        {{-- Import Preview Modal --}}
        @if($showImportPreview)
        <div class="card mb-4 border-primary">
            <div class="card-header bg-gradient-primary pb-0">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="text-white">Import Preview - Review Before Saving</h6>
                    <button wire:click="cancelImport" class="btn btn-sm btn-outline-light">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h6 class="alert-heading">Import Summary</h6>
                    <ul class="mb-0">
                        <li>Total rows: {{ $importSummary['total'] ?? 0 }}</li>
                        <li>Valid records: {{ $importSummary['valid'] ?? 0 }}</li>
                        <li>New records: {{ $importSummary['new'] ?? 0 }}</li>
                        <li>Records to update: {{ $importSummary['updates'] ?? 0 }}</li>
                        @if(($importSummary['errors'] ?? 0) > 0)
                            <li class="text-danger">Errors: {{ $importSummary['errors'] }}</li>
                        @endif
                    </ul>
                </div>

                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-sm table-striped">
                        <thead class="sticky-top bg-light">
                            <tr>
                                <th>Action</th>
                                <th>INDEX NO</th>
                                <th>STUDENT NAME</th>
                                <th>ASSIGN 1</th>
                                <th>ASSIGN 2</th>
                                <th>ASSIGN 3</th>
                                <th>MID-SEM</th>
                                <th>END-SEM</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($importPreviewData as $data)
                                <tr>
                                    <td>
                                        @if($data['is_update'])
                                            <span class="badge badge-sm bg-gradient-warning">UPDATE</span>
                                        @else
                                            <span class="badge badge-sm bg-gradient-success">NEW</span>
                                        @endif
                                    </td>
                                    <td>{{ $data['student_number'] }}</td>
                                    <td>{{ $data['student_name'] }}</td>
                                    <td>{{ $data['assignment_1'] ?? '--' }}</td>
                                    <td>{{ $data['assignment_2'] ?? '--' }}</td>
                                    <td>{{ $data['assignment_3'] ?? '--' }}</td>
                                    <td>{{ $data['mid_semester'] ?? '--' }}</td>
                                    <td>{{ $data['end_semester'] ?? '--' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3 d-flex justify-content-end gap-2">
                    <button wire:click="cancelImport" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>Cancel
                    </button>
                    <button wire:click="confirmImport" class="btn btn-success" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="confirmImport">
                            <i class="fas fa-check me-1"></i>Confirm & Save Import
                        </span>
                        <span wire:loading wire:target="confirmImport">
                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            Importing...
                        </span>
                    </button>
                </div>
            </div>
        </div>
        @endif
        @endif

        {{-- Statistics Summary --}}
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center">
                            <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md">
                                <i class="fas fa-users text-lg opacity-10"></i>
                            </div>
                            <div class="ms-3">
                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Total Students</p>
                                <h5 class="font-weight-bolder mb-0">{{ count($studentScores) }}</h5>
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
                                <h5 class="font-weight-bolder mb-0">{{ $this->passingStudents }}</h5>
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
                                <h5 class="font-weight-bolder mb-0">{{ $this->failingStudents }}</h5>
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
                                <h5 class="font-weight-bolder mb-0">{{ $this->classAverage }}%</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Scoresheet Table --}}
        <div class="card">
            <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                <h6 class="card-title">Assessment Scoresheet</h6>
                <div class="d-flex gap-2">
                    <button wire:click="saveScores" class="btn btn-success btn-sm" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="saveScores">
                            <i class="fas fa-save me-1"></i>Save All Scores
                        </span>
                        <span wire:loading wire:target="saveScores">
                            <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                            Saving...
                        </span>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 5%">#</th>
                                <th style="width: 10%">Index No</th>
                                <th style="width: 20%">Student Name</th>
                                <th class="text-center" style="width: 8%">Assign 1</th>
                                <th class="text-center" style="width: 8%">Assign 2</th>
                                <th class="text-center" style="width: 8%">Assign 3</th>
                                @if($assignmentCount >= 4)
                                <th class="text-center" style="width: 8%">Assign 4</th>
                                @endif
                                @if($assignmentCount >= 5)
                                <th class="text-center" style="width: 8%">Assign 5</th>
                                @endif
                                <th class="text-center" style="width: 10%">Mid-Sem ({{ $midSemesterWeight }}%)</th>
                                <th class="text-center" style="width: 10%">End-Sem ({{ $endSemesterWeight }}%)</th>
                                <th class="text-center bg-light" style="width: 10%">Total</th>
                                <th class="text-center bg-light" style="width: 8%">Grade</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($studentScores as $index => $student)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td class="fw-bold">{{ $student['student_number'] }}</td>
                                <td>{{ $student['student_name'] }}</td>
                                
                                {{-- Assignment 1 --}}
                                <td class="text-center">
                                    <input type="number" 
                                           wire:model.blur="studentScores.{{ $index }}.assignment_1"
                                           class="form-control form-control-sm text-center" 
                                           min="0" max="100" step="0.01"
                                           placeholder="--">
                                </td>
                                
                                {{-- Assignment 2 --}}
                                <td class="text-center">
                                    <input type="number" 
                                           wire:model.blur="studentScores.{{ $index }}.assignment_2"
                                           class="form-control form-control-sm text-center" 
                                           min="0" max="100" step="0.01"
                                           placeholder="--">
                                </td>
                                
                                {{-- Assignment 3 --}}
                                <td class="text-center">
                                    <input type="number" 
                                           wire:model.blur="studentScores.{{ $index }}.assignment_3"
                                           class="form-control form-control-sm text-center" 
                                           min="0" max="100" step="0.01"
                                           placeholder="--">
                                </td>

                                {{-- Assignment 4 (conditional) --}}
                                @if($assignmentCount >= 4)
                                <td class="text-center">
                                    <input type="number" 
                                           wire:model.blur="studentScores.{{ $index }}.assignment_4"
                                           class="form-control form-control-sm text-center" 
                                           min="0" max="100" step="0.01"
                                           placeholder="--">
                                </td>
                                @endif

                                {{-- Assignment 5 (conditional) --}}
                                @if($assignmentCount >= 5)
                                <td class="text-center">
                                    <input type="number" 
                                           wire:model.blur="studentScores.{{ $index }}.assignment_5"
                                           class="form-control form-control-sm text-center" 
                                           min="0" max="100" step="0.01"
                                           placeholder="--">
                                </td>
                                @endif
                                
                                {{-- Mid-Semester --}}
                                <td class="text-center">
                                    <input type="number" 
                                           wire:model.blur="studentScores.{{ $index }}.mid_semester"
                                           class="form-control form-control-sm text-center" 
                                           min="0" max="100" step="0.01"
                                           placeholder="--">
                                </td>
                                
                                {{-- End-Semester --}}
                                <td class="text-center">
                                    <input type="number" 
                                           wire:model.blur="studentScores.{{ $index }}.end_semester"
                                           class="form-control form-control-sm text-center" 
                                           min="0" max="100" step="0.01"
                                           placeholder="--">
                                </td>
                                
                                {{-- Total --}}
                                <td class="text-center bg-light fw-bold">
                                    {{ number_format($student['total'] ?? 0, 2) }}
                                </td>
                                
                                {{-- Grade --}}
                                <td class="text-center bg-light">
                                    @if($student['grade'])
                                        @php
                                            $gradeColor = match($student['grade']) {
                                                'A' => 'success',
                                                'B+', 'B' => 'primary',
                                                'C+', 'C' => 'info',
                                                'D+', 'D' => 'warning',
                                                'E' => 'danger',
                                                default => 'secondary'
                                            };
                                        @endphp
                                        <span class="badge bg-gradient-{{ $gradeColor }}">{{ $student['grade'] }}</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="text-center py-4 text-muted">
                                    <i class="fas fa-info-circle me-2"></i>No students loaded. Please select filters and click "Load Scoresheet"
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if(count($studentScores) > 0)
                <div class="mt-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="text-muted">
                        <small>
                            <i class="fas fa-info-circle me-1"></i>
                            Enter scores and press Tab or click outside the field to auto-calculate. Leave blank if not taken.
                        </small>
                    </div>
                    <div class="d-flex gap-2">
                        <button wire:click="saveScores" class="btn btn-success" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="saveScores">
                                <i class="fas fa-save me-1"></i>Save All Scores
                            </span>
                            <span wire:loading wire:target="saveScores">
                                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                Saving...
                            </span>
                        </button>

                        <button wire:click="exportToExcel" class="btn btn-outline-primary" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="exportToExcel">
                                <i class="fas fa-file-excel me-1"></i>Export
                            </span>
                            <span wire:loading wire:target="exportToExcel">
                                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                Exporting...
                            </span>
                        </button>
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endif

    </div>
</div>

