<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h1 class="card-title">
                            <i class="fas fa-ticket-alt me-2"></i> Exam Entry Ticket Manager
                        </h1>
                    </div>
                </div>
                <div class="card-body">
                    @if($clearanceId)
                        <p class="card-text">Manage exam entry tickets for the selected student's exam clearance.</p>
                    @else
                        <p class="card-text">Select a student's exam clearance to manage their exam entry tickets.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if(session('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(!$clearanceId)
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Select Exam Clearance</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Please select a student's exam clearance from the Exam Clearance page first.
                        </div>
                        <a href="{{ route('finance.exam.clearance') }}" class="btn btn-primary">
                            <i class="fas fa-arrow-left me-2"></i>
                            Go to Exam Clearance
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @elseif($clearance)
        <!-- Student Info -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Student Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <h6>Student</h6>
                                <p class="fw-bold">{{ $clearance->student->first_name }} {{ $clearance->student->last_name }}</p>
                                <p>ID: {{ $clearance->student->student_id }}</p>
                            </div>
                            <div class="col-md-4">
                                <h6>Academic Period</h6>
                                <p>{{ $clearance->academicYear->name }}</p>
                                <p>{{ $clearance->semester->name }}</p>
                            </div>
                            <div class="col-md-4">
                                <h6>Exam Type</h6>
                                <p>{{ $clearance->examType->name }}</p>
                                <p>Clearance ID: {{ $clearance->id }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tickets Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title">Exam Entry Tickets</h5>
                            <button type="button" class="btn btn-primary" wire:click="openGenerateModal({{ $clearance->id }})">
                                <i class="fas fa-plus-circle me-2"></i> Generate New Ticket
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        @if($tickets->isEmpty())
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                No exam entry tickets have been generated for this student yet.
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Exam</th>
                                            <th>Generated</th>
                                            <th>Expires</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($tickets as $ticket)
                                            <tr>
                                                <td>{{ $ticket->id }}</td>
                                                <td>{{ $ticket->exam->title }}</td>
                                                <td>{{ $ticket->created_at->format('d M Y, H:i') }}</td>
                                                <td>{{ $ticket->expires_at ? $ticket->expires_at->format('d M Y, H:i') : 'No Expiry' }}</td>
                                                <td>
                                                    @if($ticket->is_active)
                                                        @if($ticket->expires_at && $ticket->expires_at < now())
                                                            <span class="badge bg-warning">Expired</span>
                                                        @else
                                                            <span class="badge bg-success">Active</span>
                                                        @endif
                                                    @else
                                                        <span class="badge bg-danger">Deactivated</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="{{ route('finance.exam.ticket.print', ['ticketId' => $ticket->id]) }}" 
                                                           target="_blank" 
                                                           class="btn btn-sm btn-info">
                                                            <i class="fas fa-print"></i>
                                                        </a>
                                                        
                                                        @if($ticket->is_active)
                                                            <button type="button" 
                                                                    class="btn btn-sm btn-danger" 
                                                                    wire:click="deactivateTicket({{ $ticket->id }})" 
                                                                    onclick="return confirm('Are you sure you want to deactivate this ticket?')">
                                                                <i class="fas fa-ban"></i>
                                                            </button>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="mt-3">
                                {{ $tickets->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Error</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Unable to find the specified exam clearance. It may have been deleted or is no longer available.
                        </div>
                        <a href="{{ route('finance.exam.clearance') }}" class="btn btn-primary">
                            <i class="fas fa-arrow-left me-2"></i>
                            Go to Exam Clearance
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Generate Ticket Modal -->
    @if($showGenerateModal && $clearance)
        <div class="modal fade show" tabindex="-1" style="display: block; background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Generate Exam Entry Ticket</h5>
                        <button type="button" class="btn-close" wire:click="$set('showGenerateModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <form>
                            <div class="mb-3">
                                <label for="examId" class="form-label">Select Exam</label>
                                <select id="examId" class="form-select @error('examId') is-invalid @enderror" wire:model="examId">
                                    <option value="">-- Select Exam --</option>
                                    @foreach($exams as $exam)
                                        <option value="{{ $exam->id }}">{{ $exam->title }}</option>
                                    @endforeach
                                </select>
                                @error('examId')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="expiryDate" class="form-label">Expiry Date</label>
                                        <input type="date" id="expiryDate" class="form-control @error('expiryDate') is-invalid @enderror" wire:model="expiryDate">
                                        @error('expiryDate')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="expiryTime" class="form-label">Expiry Time</label>
                                        <input type="time" id="expiryTime" class="form-control @error('expiryTime') is-invalid @enderror" wire:model="expiryTime">
                                        @error('expiryTime')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <p class="text-muted small">
                                <i class="fas fa-info-circle"></i> Leave expiry date and time empty for a ticket that never expires.
                            </p>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showGenerateModal', false)">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="generateTicket">Generate Ticket</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>