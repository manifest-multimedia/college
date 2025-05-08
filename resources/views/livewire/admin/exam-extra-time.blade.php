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
                                        <a href="#" class="btn btn-sm btn-icon btn-light btn-active-light-primary" title="View Session Details">
                                            <i class="bi bi-eye"></i>
                                        </a>
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
</div>
