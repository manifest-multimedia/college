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
                        <label for="sessionStateFilter" class="form-label fw-semibold">Session State Filter</label>
                        <select id="sessionStateFilter" wire:model.live="sessionStateFilter" class="form-select">
                            <option value="all">All Sessions</option>
                            <option value="active">Active Only</option>
                            <option value="completed">Completed Only</option>
                            <option value="expired">Expired Only</option>
                        </select>
                        <div class="form-text">Filter sessions by their current state</div>
                    </div>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-6 mb-3">
                        <label for="search" class="form-label fw-semibold">Search Students</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <textarea id="search" wire:model.live.debounce.500ms="search" 
                                class="form-control" rows="3" 
                                placeholder="Enter one or more student IDs separated by spaces, commas, or new lines"></textarea>
                        </div>
                        <div class="form-text">
                            Enter multiple student college IDs (e.g., COLLEGE/DEPT/RM/22/23/197, COLLEGE/DEPT/RM/22/23/198)
                            <div class="mt-1"><strong>Tip:</strong> You can paste a list of IDs from Excel or any other source</div>
                        </div>
                    </div>
                </div>
                
                <!-- Display student information if found -->
                @if(count($foundStudents) > 0)
                <div class="alert alert-success mb-4">
                    <div class="d-flex align-items-center mb-2 justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="symbol symbol-30px me-3 bg-light">
                                <span class="symbol-label bg-primary text-inverse-primary">
                                    <i class="bi bi-people-fill"></i>
                                </span>
                            </div>
                            <h5 class="mb-0">{{ $foundStudentsCount }} Student(s) Found</h5>
                        </div>
                        
                        @if(!$applyToAll && !empty($foundUserIds))
                        <button type="button" wire:click="selectAllFoundSessions" class="btn btn-sm btn-primary">
                            <i class="bi bi-check-all me-1"></i>
                            Select All Sessions
                        </button>
                        @endif
                    </div>
                    
                    <div class="table-responsive mt-2">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Student ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($foundStudents as $index => $data)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $data['student']->student_id }}</td>
                                    <td>{{ $data['student']->name }}</td>
                                    <td>{{ $data['student']->email }}</td>
                                    <td>
                                        @if($data['has_user_account'])
                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle-fill me-1"></i> Account Linked
                                            </span>
                                        @else
                                            <span class="badge bg-danger">
                                                <i class="bi bi-exclamation-triangle-fill me-1"></i> No User Account
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <button type="button" wire:click="removeStudent({{ $data['student']->id }})" 
                                            class="btn btn-sm btn-outline-danger btn-icon" title="Remove from selection">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @elseif($processingSearch)
                <div class="alert alert-info mb-4">
                    <div class="d-flex align-items-center">
                        <div class="spinner-border spinner-border-sm me-2" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <span>Searching for students...</span>
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
                    <button type="button" wire:click="addExtraTime" class="btn btn-primary" @if(!$exam_id || (empty($foundUserIds) && !$applyToAll)) disabled @endif wire:loading.attr="disabled" wire:target="addExtraTime">
                        <span wire:loading.remove wire:target="addExtraTime">
                            <i class="bi bi-plus-circle me-2"></i>
                            Add Extra Time
                        </span>
                        <span wire:loading wire:target="addExtraTime">
                            <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                            Adding...
                        </span>
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
                        Exam Sessions 
                        @if($sessionStateFilter !== 'all')
                            ({{ ucfirst($sessionStateFilter) }})
                        @endif
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
                                <th>Status</th>
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
                                            <input class="form-check-input" type="checkbox" 
                                                wire:click="toggleSessionSelection({{ $session->id }})" 
                                                @if(in_array($session->id, $selectedSessions)) checked @endif 
                                                value="{{ $session->id }}">
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
                                    <td>
                                        @php
                                            $status = $this->getSessionStatus($session);
                                        @endphp
                                        @if($status === 'active')
                                            <span class="badge bg-success">Active</span>
                                        @elseif($status === 'completed')
                                            <span class="badge bg-primary">Completed</span>
                                        @elseif($status === 'expired')
                                            <span class="badge bg-danger">Expired</span>
                                        @endif
                                    </td>
                                    <td>{{ $session->completed_at ? $session->completed_at->format('M d, Y g:i A') : 'Not set' }}</td>
                                    <td>
                                        @if($session->extra_time_minutes > 0)
                                            <span class="badge bg-success">{{ $session->extra_time_minutes }} minutes</span>
                                        @else
                                            <span class="badge bg-light text-dark">None</span>
                                        @endif
                                    </td>
                                    <td>{{ $session->adjustedCompletionTime ? $session->adjustedCompletionTime->format('M d, Y g:i A') : 'Not set' }}</td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <button type="button" wire:click="viewSession({{ $session->id }})" class="btn btn-sm btn-icon btn-light btn-active-light-primary" title="View Session Details">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            @if($status === 'completed' || $status === 'expired')
                                                <button type="button" wire:click="openResumeModal({{ $session->id }})" 
                                                        class="btn btn-sm btn-icon btn-warning btn-active-light-warning" 
                                                        title="Resume Session">
                                                    <i class="bi bi-arrow-clockwise"></i>
                                                </button>
                                            @endif
                                        </div>
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
                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <button type="button" wire:click="addExtraTime" class="btn btn-primary" @if(empty($selectedSessions)) disabled @endif wire:loading.attr="disabled" wire:target="addExtraTime">
                            <span wire:loading.remove wire:target="addExtraTime">
                                <i class="bi bi-plus-circle me-2"></i>
                                Add Extra Time to Selected Sessions ({{ count($selectedSessions) }})
                            </span>
                            <span wire:loading wire:target="addExtraTime">
                                <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                Processing...
                            </span>
                        </button>
                        <button type="button" wire:click="showBulkResumeModal" class="btn btn-warning" @if(empty($selectedSessions)) disabled @endif>
                            <i class="bi bi-arrow-clockwise me-2"></i>
                            Resume Selected Sessions ({{ count($selectedSessions) }})
                        </button>
                    </div>
                @endif
            </div>
        </div>
    @elseif($exam_id)
        <div class="card shadow-sm">
            <div class="card-body py-15 text-center">
                <div class="text-gray-600 fw-semibold fs-6 mb-5">
                    No exam sessions found for the selected criteria.
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
                        <!-- Student Information Card -->
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

                        <!-- Exam Timing Card -->
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
                                            <span class="fs-6 fw-semibold">
                                                @if($viewingSession->completed_at)
                                                    {{ $viewingSession->completed_at->format('M d, Y g:i A') }}
                                                @else
                                                    <span class="badge bg-warning">In Progress</span>
                                                @endif
                                            </span>
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
                        
                        <!-- Extra Time Management Form -->
                        @if($modifyExtraTime && $viewingSession)
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-light-primary">
                                <div class="card-title">
                                    <h3 class="card-title">
                                        <i class="bi bi-pencil-square me-2"></i>
                                        Modify Extra Time
                                    </h3>
                                </div>
                            </div>
                            <div class="card-body">
                                <form wire:submit.prevent="updateExtraTime" class="mb-3">
                                    <div class="alert alert-info mb-3">
                                        <div class="d-flex">
                                            <div class="me-3">
                                                <i class="bi bi-info-circle-fill fs-3"></i>
                                            </div>
                                            <div>
                                                <p class="mb-1 fw-semibold">Current extra time: {{ $viewingSession->extra_time_minutes }} minutes</p>
                                                <p class="mb-0">Enter a new value to update or remove extra time (set to 0 to remove all extra time).</p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="newExtraTimeValue" class="form-label fw-semibold required">New Extra Time Value (Minutes)</label>
                                            <input type="number" id="newExtraTimeValue" wire:model="newExtraTimeValue" 
                                                class="form-control @error('newExtraTimeValue') is-invalid @enderror" min="0" max="120">
                                            @error('newExtraTimeValue') 
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between">
                                        <button type="button" class="btn btn-light" wire:click="toggleModifyExtraTimeForm">
                                            Cancel
                                        </button>
                                        <div>
                                            <button type="button" class="btn btn-danger me-2" wire:click="removeExtraTime" @if($viewingSession->extra_time_minutes <= 0) disabled @endif>
                                                <i class="bi bi-trash me-1"></i> Remove All Extra Time
                                            </button>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bi bi-check-circle me-1"></i> Update Extra Time
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        @endif
                        
                        <!-- Session Restoration Form -->
                        @if($restoreSession && $viewingSession)
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-light-warning">
                                <div class="card-title">
                                    <h3 class="card-title">
                                        <i class="bi bi-arrow-clockwise me-2"></i>
                                        Restore Exam Session
                                    </h3>
                                </div>
                            </div>
                            <div class="card-body">
                                <form wire:submit.prevent="restoreExamSession">
                                    <div class="alert alert-warning mb-3">
                                        <div class="d-flex">
                                            <div class="me-3">
                                                <i class="bi bi-exclamation-triangle-fill fs-3"></i>
                                            </div>
                                            <div>
                                                <p class="mb-1 fw-semibold">You are about to restore an expired exam session</p>
                                                <p class="mb-0">This will allow the student to log back in and continue the exam. Please provide a reason for this action.</p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="restoreMinutes" class="form-label fw-semibold required">Minutes to Grant</label>
                                            <input type="number" id="restoreMinutes" wire:model="restoreMinutes" 
                                                class="form-control @error('restoreMinutes') is-invalid @enderror" min="5" max="120">
                                            @error('restoreMinutes') 
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">How many minutes should the student have to complete the exam?</div>
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-12">
                                            <label for="restoreReason" class="form-label fw-semibold required">Reason for Restoration</label>
                                            <textarea id="restoreReason" wire:model="restoreReason" rows="3" 
                                                class="form-control @error('restoreReason') is-invalid @enderror"
                                                placeholder="Provide a reason for restoring this exam session"></textarea>
                                            @error('restoreReason') 
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between">
                                        <button type="button" class="btn btn-light" wire:click="toggleRestoreForm">
                                            Cancel
                                        </button>
                                        <button type="submit" class="btn btn-warning">
                                            <i class="bi bi-arrow-clockwise me-1"></i> Restore Session
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        @endif
                        
                        <!-- Exam Progress Card -->
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
                    <div class="modal-footer justify-content-between">
                        <!-- Left side - session actions -->
                        <div>
                            @php
                                $isExpired = now()->gt($viewingSession->adjustedCompletionTime);
                                $isCompleted = $viewingSession->completed_at && $viewingSession->completed_at->isPast();
                            @endphp
                            
                            @if($isExpired || $isCompleted)
                                <button type="button" class="btn btn-warning me-2" wire:click="toggleRestoreForm">
                                    <i class="bi bi-arrow-clockwise me-1"></i> Restore Session
                                </button>
                            @endif
                            
                            <button type="button" class="btn btn-info" wire:click="toggleModifyExtraTimeForm">
                                <i class="bi bi-gear me-1"></i> 
                                @if($viewingSession->extra_time_minutes > 0)
                                    Modify Extra Time
                                @else
                                    Add Extra Time
                                @endif
                            </button>
                        </div>
                        
                        <!-- Right side - closing and additional buttons -->
                        <div>
                            <button type="button" class="btn btn-light" wire:click="closeViewModal">Close</button>
                            
                            <!-- Always show the add extra time button -->
                            <button type="button" class="btn btn-primary" wire:click="addExtraTimeFromModal">
                                <i class="bi bi-plus-circle me-2"></i> Add {{ $extraTimeMinutes }} Minutes Extra Time
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif

    <!-- Individual Resume Modal -->
    @if($showIndividualResumeModal)
        <div class="modal show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-arrow-clockwise me-2"></i>
                            Resume Exam Session
                        </h5>
                        <button type="button" class="btn-close" wire:click="cancelIndividualResume"></button>
                    </div>
                    <div class="modal-body">
                        @if($resumingSessionId)
                            @php
                                $resumingSession = \App\Models\ExamSession::with(['student', 'exam.course'])->find($resumingSessionId);
                            @endphp
                            
                            @if($resumingSession)
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    You are about to resume the exam session for:
                                    <br><strong>{{ $resumingSession->student->name ?? 'Unknown Student' }}</strong>
                                    <br>Course: <strong>{{ $resumingSession->exam->course->name ?? 'Unknown Course' }}</strong>
                                    <br>Started: {{ $resumingSession->started_at->format('M d, Y g:i A') }}
                                </div>
                            @endif
                        @endif
                        
                        <div class="mb-3">
                            <label for="individualResumeMinutes" class="form-label fw-semibold required">Additional Time (minutes)</label>
                            <input type="number" id="individualResumeMinutes" wire:model="individualResumeMinutes" 
                                   class="form-control @error('individualResumeMinutes') is-invalid @enderror" 
                                   min="5" max="120" placeholder="30">
                            @error('individualResumeMinutes') 
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Minimum: 5 minutes, Maximum: 120 minutes</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="individualResumeReason" class="form-label fw-semibold required">Reason for Resumption</label>
                            <textarea id="individualResumeReason" wire:model="individualResumeReason" 
                                      class="form-control @error('individualResumeReason') is-invalid @enderror" 
                                      rows="3" placeholder="Enter reason for resuming this session (e.g., connectivity issues, technical problems)..."></textarea>
                            @error('individualResumeReason') 
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">This will be logged for audit purposes</div>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>What happens when you resume:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Session will be reactivated for the specified time</li>
                                <li>Student can log back in and continue the exam</li>
                                <li>Previous answers will be preserved</li>
                                <li>Timer will show the new remaining time</li>
                            </ul>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="cancelIndividualResume">Cancel</button>
                        <button type="button" wire:click="confirmIndividualResume" class="btn btn-warning">
                            <i class="bi bi-arrow-clockwise me-2"></i>
                            Resume Session
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif

    <!-- Bulk Resume Modal -->
    @if($showBulkResumeModal)
        <div class="modal show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-arrow-clockwise me-2"></i>
                            Resume Selected Sessions
                        </h5>
                        <button type="button" class="btn-close" wire:click="cancelBulkResume"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            You are about to resume <strong>{{ count($selectedSessions) }} session(s)</strong>. 
                            This will reactivate completed or expired sessions, allowing students to continue their exams.
                        </div>
                        
                        <div class="mb-3">
                            <label for="bulkResumeMinutes" class="form-label fw-semibold required">Additional Time (minutes)</label>
                            <input type="number" id="bulkResumeMinutes" wire:model="bulkResumeMinutes" 
                                   class="form-control @error('bulkResumeMinutes') is-invalid @enderror" 
                                   min="5" max="120" placeholder="30">
                            @error('bulkResumeMinutes') 
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Minimum: 5 minutes, Maximum: 120 minutes</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="bulkResumeReason" class="form-label fw-semibold required">Reason for Resumption</label>
                            <textarea id="bulkResumeReason" wire:model="bulkResumeReason" 
                                      class="form-control @error('bulkResumeReason') is-invalid @enderror" 
                                      rows="3" placeholder="Enter reason for resuming these sessions..."></textarea>
                            @error('bulkResumeReason') 
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">This will be logged for audit purposes</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="cancelBulkResume">Cancel</button>
                        <button type="button" wire:click="bulkResumeSelected" class="btn btn-warning">
                            <i class="bi bi-arrow-clockwise me-2"></i>
                            Resume {{ count($selectedSessions) }} Session(s)
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif
</div>
