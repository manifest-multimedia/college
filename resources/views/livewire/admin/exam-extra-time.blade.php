<div>
    @if(session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errorMessage)
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ $errorMessage }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($successMessage)
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ $successMessage }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm mb-5">
        <div class="card-header">
            <div class="card-title">
                <h3 class="card-title">
                    <i class="bi bi-search me-2"></i>
                    Search & Apply Extra Time
                </h3>
            </div>
        </div>
        <div class="card-body">
            <form wire:submit.prevent="addExtraTime">
                <div class="row mb-4">
                    <div class="col-md-6 mb-3">
                        <label for="exam_id" class="form-label fw-semibold required">Select Exam</label>
                        <select id="exam_id" wire:model.live="exam_id" class="form-select @error('exam_id') is-invalid @enderror">
                            <option value="">-- Select an Exam --</option>
                            @foreach($exams as $exam)
                                <option value="{{ $exam->id }}">
                                    {{ $exam->course->name ?? 'Unknown Course' }} ({{ $exam->created_at->format('d M, Y') }})
                                </option>
                            @endforeach
                        </select>
                        @error('exam_id') 
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="search" class="form-label fw-semibold">Search Students</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" id="search" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Search by name, ID or email">
                        </div>
                        <div class="form-text">Search for specific students to apply extra time</div>
                    </div>
                </div>
                
                <!-- Student ID search form -->
                <div class="row mb-4">
                    <div class="col-md-6 mb-3">
                        <label for="student_id" class="form-label fw-semibold">Search by Student ID</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                            <input type="text" id="student_id" wire:model.live.debounce.300ms="student_id" 
                                class="form-control" placeholder="Enter college student ID">
                        </div>
                        <div class="form-text">Enter the student's college ID (e.g., PNMTC/DA/RM/22/23/197)</div>
                    </div>
                </div>
                
                <!-- Display student information if found -->
                @if($studentFound && $foundStudent)
                <div class="alert alert-success mb-4">
                    <div class="d-flex align-items-center mb-2">
                        <div class="symbol symbol-circle symbol-30px me-3 bg-light">
                            <span class="symbol-label bg-primary text-inverse-primary">
                                {{ substr($foundStudent->name ?? 'U', 0, 1) }}
                            </span>
                        </div>
                        <h5 class="mb-0">Student Found!</h5>
                    </div>
                    <div class="ps-3">
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Name:</strong> {{ $foundStudent->name }}</p>
                                <p class="mb-1"><strong>Student ID:</strong> {{ $foundStudent->student_id }}</p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Email:</strong> {{ $foundStudent->email }}</p>
                                @if($foundUser)
                                    <p class="mb-1 text-success">
                                        <i class="bi bi-check-circle-fill me-1"></i>
                                        User account linked
                                    </p>
                                @else
                                    <p class="mb-1 text-danger">
                                        <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                        No user account found
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                
                <div class="row mb-4">
                    <div class="col-md-6 mb-3">
                        <label for="extraTimeMinutes" class="form-label fw-semibold required">Extra Time (Minutes)</label>
                        <input type="number" id="extraTimeMinutes" wire:model="extraTimeMinutes" class="form-control @error('extraTimeMinutes') is-invalid @enderror" min="1" max="60">
                        @error('extraTimeMinutes') 
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Enter the amount of extra time to add (1-60 minutes)</div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <div class="form-check form-switch mt-4">
                            <input class="form-check-input" type="checkbox" role="switch" id="applyToAll" wire:model.live="applyToAll">
                            <label class="form-check-label fw-semibold" for="applyToAll">
                                Apply to all active sessions
                            </label>
                        </div>
                        <div class="form-text">Enable to apply extra time to all active sessions for this exam</div>
                        
                        @if($applyToAll)
                        <div class="form-check form-switch mt-3">
                            <input class="form-check-input" type="checkbox" role="switch" id="includeCompletedSessions" wire:model.live="includeCompletedSessions">
                            <label class="form-check-label fw-semibold" for="includeCompletedSessions">
                                Include completed sessions
                            </label>
                        </div>
                        <div class="form-text text-warning">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i> 
                            This will reactivate previously completed or expired exam sessions
                        </div>
                        @endif
                    </div>
                </div>
                
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>
                        Add Extra Time
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    @if($exam_id && count($examSessions) > 0)
        <div class="card shadow-sm">
            <div class="card-header">
                <div class="card-title">
                    <h3 class="card-title">
                        <i class="bi bi-people me-2"></i>
                        Active Exam Sessions
                    </h3>
                </div>
            </div>
            
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-row-bordered table-hover align-middle">
                        <thead class="bg-light">
                            <tr class="fw-bold">
                                @if(!$applyToAll)
                                <th class="w-25px">
                                    <div class="form-check form-check-sm form-check-custom">
                                        <input class="form-check-input" type="checkbox" disabled>
                                    </div>
                                </th>
                                @endif
                                <th>Student</th>
                                <th>Started At</th>
                                <th>Original End Time</th>
                                <th>Current Extra Time</th>
                                <th>Adjusted End Time</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($examSessions as $session)
                                <tr>
                                    @if(!$applyToAll)
                                    <td>
                                        <div class="form-check form-check-sm form-check-custom">
                                            <input class="form-check-input" type="checkbox" wire:model="selectedSessions" value="{{ $session->id }}">
                                        </div>
                                    </td>
                                    @endif
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="symbol symbol-circle symbol-30px me-3 bg-light">
                                                <span class="symbol-label bg-primary text-inverse-primary">
                                                    {{ substr($session->student->name ?? 'U', 0, 1) }}
                                                </span>
                                            </div>
                                            <div class="d-flex flex-column">
                                                <span class="fw-bold">{{ $session->student->name ?? 'Unknown' }}</span>
                                                <span class="text-muted fs-7">{{ $session->student->student->student_id ?? 'No ID' }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $session->started_at->format('M d, Y g:i A') }}</td>
                                    <td>{{ $session->completed_at->format('M d, Y g:i A') }}</td>
                                    <td>
                                        @if($session->extra_time_minutes > 0)
                                            <span class="badge bg-success">{{ $session->extra_time_minutes }} minutes</span>
                                        @else
                                            <span class="badge bg-light text-dark">None</span>
                                        @endif
                                    </td>
                                    <td>{{ $session->adjustedCompletionTime->format('M d, Y g:i A') }}</td>
                                    <td>
                                        <button type="button" wire:click="viewSession({{ $session->id }})" class="btn btn-sm btn-icon btn-light btn-active-light-primary" title="View Session Details">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-5">
                    {{ $examSessions->links() }}
                </div>
                
                @if(!$applyToAll)
                    <div class="d-flex justify-content-end mt-4">
                        <button type="button" wire:click="addExtraTime" class="btn btn-primary" @if(empty($selectedSessions)) disabled @endif>
                            <i class="bi bi-plus-circle me-2"></i>
                            Add Extra Time to Selected Sessions ({{ count($selectedSessions) }})
                        </button>
                    </div>
                @endif
            </div>
        </div>
    @elseif($exam_id)
        <div class="card shadow-sm">
            <div class="card-body py-15 text-center">
                <div class="text-gray-600 fw-semibold fs-6 mb-5">
                    No active exam sessions found for this exam.
                </div>
            </div>
        </div>
    @endif

    <!-- Session Details Modal -->
    @if($showViewModal && $viewingSession)
        <div class="modal fade show" tabindex="-1" style="display: block; padding-right: 17px;" 
            aria-modal="true" role="dialog">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Session Details</h5>
                        <button type="button" class="btn-close" wire:click="closeViewModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="card shadow-sm mb-4">
                            <div class="card-header">
                                <div class="card-title">
                                    <h3 class="card-title">
                                        <i class="bi bi-person-badge me-2"></i>
                                        Student Information
                                    </h3>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="text-muted fs-7 d-block">Name:</label>
                                            <span class="fs-6 fw-semibold">{{ $viewingSession->student->name ?? 'Unknown' }}</span>
                                        </div>
                                        <div class="mb-3">
                                            <label class="text-muted fs-7 d-block">Email:</label>
                                            <span class="fs-6 fw-semibold">{{ $viewingSession->student->email ?? 'No email' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="text-muted fs-7 d-block">Student ID:</label>
                                            <span class="fs-6 fw-semibold">{{ $viewingSession->student->student->student_id ?? 'No ID' }}</span>
                                        </div>
                                        <div class="mb-3">
                                            <label class="text-muted fs-7 d-block">Course:</label>
                                            <span class="fs-6 fw-semibold">{{ $viewingSession->exam->course->name ?? 'Unknown Course' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card shadow-sm mb-4">
                            <div class="card-header">
                                <div class="card-title">
                                    <h3 class="card-title">
                                        <i class="bi bi-alarm me-2"></i>
                                        Exam Timing
                                    </h3>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="text-muted fs-7 d-block">Started At:</label>
                                            <span class="fs-6 fw-semibold">{{ $viewingSession->started_at->format('M d, Y g:i A') }}</span>
                                        </div>
                                        <div class="mb-3">
                                            <label class="text-muted fs-7 d-block">Original End Time:</label>
                                            <span class="fs-6 fw-semibold">{{ $viewingSession->completed_at->format('M d, Y g:i A') }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="text-muted fs-7 d-block">Extra Time:</label>
                                            @if($viewingSession->extra_time_minutes > 0)
                                                <span class="badge bg-success">{{ $viewingSession->extra_time_minutes }} minutes</span>
                                                
                                                @if($viewingSession->extra_time_added_at)
                                                <div class="small text-muted mt-1">
                                                    Added {{ $viewingSession->extra_time_added_at->diffForHumans() }}
                                                    @if($viewingSession->extraTimeAddedBy)
                                                        by {{ $viewingSession->extraTimeAddedBy->name }}
                                                    @endif
                                                </div>
                                                @endif
                                            @else
                                                <span class="badge bg-light text-dark">None</span>
                                            @endif
                                        </div>
                                        <div class="mb-3">
                                            <label class="text-muted fs-7 d-block">Adjusted End Time:</label>
                                            <span class="fs-6 fw-semibold">{{ $viewingSession->adjustedCompletionTime->format('M d, Y g:i A') }}</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="me-4">
                                                <label class="text-muted fs-7 d-block">Exam Status:</label>
                                                @if($viewingSession->completed_at && $viewingSession->completed_at->isPast())
                                                    <span class="badge bg-danger">Completed</span>
                                                @elseif(now()->gt($viewingSession->adjustedCompletionTime))
                                                    <span class="badge bg-danger">Time Expired</span>
                                                @else
                                                    <span class="badge bg-success">Active</span>
                                                @endif
                                            </div>
                                            
                                            @if($viewingSession->auto_submitted)
                                                <div class="me-4">
                                                    <span class="badge bg-warning text-dark">Auto-submitted</span>
                                                </div>
                                            @endif
                                            
                                            <div>
                                                <label class="text-muted fs-7 d-block">Remaining Time:</label>
                                                @if(now()->gt($viewingSession->adjustedCompletionTime))
                                                    <span class="text-danger fw-bold">Expired</span>
                                                @else
                                                    @php
                                                        $remainingSeconds = now()->diffInSeconds($viewingSession->adjustedCompletionTime, false);
                                                        $hours = floor($remainingSeconds / 3600);
                                                        $minutes = floor(($remainingSeconds % 3600) / 60);
                                                        $seconds = $remainingSeconds % 60;
                                                    @endphp
                                                    <span class="text-success fw-bold">
                                                        {{ sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds) }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card shadow-sm">
                            <div class="card-header">
                                <div class="card-title">
                                    <h3 class="card-title">
                                        <i class="bi bi-question-circle me-2"></i>
                                        Exam Progress
                                    </h3>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="mb-4">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="fs-6 fw-semibold">Questions Answered</span>
                                        <span class="fs-6 fw-bold text-primary">
                                            {{ count($viewingSession->responses) }} / {{ $viewingSession->exam->questions->count() }}
                                        </span>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        @php
                                            $progressPercent = $viewingSession->exam->questions->count() > 0 
                                                ? (count($viewingSession->responses) / $viewingSession->exam->questions->count() * 100) 
                                                : 0;
                                        @endphp
                                        <div class="progress-bar bg-primary" 
                                             role="progressbar" 
                                             style="width: {{ $progressPercent }}%;" 
                                             aria-valuenow="{{ $progressPercent }}" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100">
                                        </div>
                                    </div>
                                </div>
                                
                                @if($viewingSession->score !== null)
                                    <div class="mb-4">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <span class="fs-6 fw-semibold">Score</span>
                                            <span class="fs-6 fw-bold text-success">
                                                {{ $viewingSession->score }} points
                                            </span>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeViewModal">Close</button>
                        
                        <!-- Always show the add extra time button, even for completed sessions -->
                        <button type="button" class="btn btn-primary" wire:click="addExtraTimeFromModal">
                            <i class="bi bi-plus-circle me-2"></i> Add {{ $extraTimeMinutes }} Minutes Extra Time
                        </button>
                        
                        @if($viewingSession->completed_at && now()->lt($viewingSession->adjustedCompletionTime))
                            <div class="badge bg-success ms-2 py-2">Session is active</div>
                        @elseif($viewingSession->completed_at) 
                            <div class="badge bg-warning text-dark ms-2 py-2">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                Adding time will reactivate this session
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif
</div>
