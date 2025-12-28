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
                                <option value="{{ $year->id }}">{{ $year->year }}</option>
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
                        <div class="d-flex gap-4">
                            <span class="badge bg-gradient-primary px-3 py-2">
                                Assignments: <strong>{{ $assignmentWeight }}%</strong>
                            </span>
                            <span class="badge bg-gradient-info px-3 py-2">
                                Mid-Semester: <strong>{{ $midSemesterWeight }}%</strong>
                            </span>
                            <span class="badge bg-gradient-success px-3 py-2">
                                End-Semester: <strong>{{ $endSemesterWeight }}%</strong>
                            </span>
                        </div>
                    </div>
                    <button wire:click="toggleWeightConfig" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-cog me-1"></i>Configure Weights
                    </button>
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
                                           wire:model.lazy="studentScores.{{ $index }}.assignment_1"
                                           wire:change="calculateStudentTotal({{ $index }})"
                                           class="form-control form-control-sm text-center" 
                                           min="0" max="100" step="0.01"
                                           placeholder="--">
                                </td>
                                
                                {{-- Assignment 2 --}}
                                <td class="text-center">
                                    <input type="number" 
                                           wire:model.lazy="studentScores.{{ $index }}.assignment_2"
                                           wire:change="calculateStudentTotal({{ $index }})"
                                           class="form-control form-control-sm text-center" 
                                           min="0" max="100" step="0.01"
                                           placeholder="--">
                                </td>
                                
                                {{-- Assignment 3 --}}
                                <td class="text-center">
                                    <input type="number" 
                                           wire:model.lazy="studentScores.{{ $index }}.assignment_3"
                                           wire:change="calculateStudentTotal({{ $index }})"
                                           class="form-control form-control-sm text-center" 
                                           min="0" max="100" step="0.01"
                                           placeholder="--">
                                </td>
                                
                                {{-- Mid-Semester --}}
                                <td class="text-center">
                                    <input type="number" 
                                           wire:model.lazy="studentScores.{{ $index }}.mid_semester"
                                           wire:change="calculateStudentTotal({{ $index }})"
                                           class="form-control form-control-sm text-center" 
                                           min="0" max="100" step="0.01"
                                           placeholder="--">
                                </td>
                                
                                {{-- End-Semester --}}
                                <td class="text-center">
                                    <input type="number" 
                                           wire:model.lazy="studentScores.{{ $index }}.end_semester"
                                           wire:change="calculateStudentTotal({{ $index }})"
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
                <div class="mt-3 d-flex justify-content-between align-items-center">
                    <div class="text-muted">
                        <small>
                            <i class="fas fa-info-circle me-1"></i>
                            Enter scores and press Tab or click outside the field to auto-calculate. Leave blank if not taken.
                        </small>
                    </div>
                    <button wire:click="saveScores" class="btn btn-success" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="saveScores">
                            <i class="fas fa-save me-2"></i>Save All Scores
                        </span>
                        <span wire:loading wire:target="saveScores">
                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            Saving...
                        </span>
                    </button>
                </div>
                @endif
            </div>
        </div>
        @endif

    </div>
</div>

