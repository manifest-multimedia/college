<div>
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="card-title">
                    <i class="fas fa-qrcode"></i> Exam Entry Ticket Manager
                </h1>
                <div>
                    <button wire:click="openGenerateModal({{ $clearance->id }})" class="btn btn-primary">
                        <i class="fas fa-plus-circle"></i> Generate New Ticket
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <!-- Student and Clearance Information -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">Student Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="me-3">
                                    @if($clearance->student->photo)
                                        <img src="{{ asset('storage/' . $clearance->student->photo) }}" 
                                            alt="Student Photo" class="rounded-circle" width="80" height="80">
                                    @else
                                        <div class="bg-secondary rounded-circle d-flex justify-content-center align-items-center text-white"
                                            style="width: 80px; height: 80px;">
                                            <i class="fas fa-user fa-2x"></i>
                                        </div>
                                    @endif
                                </div>
                                <div>
                                    <h5 class="mb-1">{{ $clearance->student->full_name }}</h5>
                                    <p class="mb-1"><strong>ID:</strong> {{ $clearance->student->student_id }}</p>
                                    <p class="mb-0"><strong>Class:</strong> {{ $clearance->student->collegeClass->name ?? 'Not Assigned' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">Clearance Information</h5>
                        </div>
                        <div class="card-body">
                            <p><strong>Academic Year:</strong> {{ $clearance->academicYear->name }}</p>
                            <p><strong>Semester:</strong> {{ $clearance->semester->name }}</p>
                            <p><strong>Exam Type:</strong> {{ $clearance->examType->name }}</p>
                            <p><strong>Clearance Date:</strong> {{ $clearance->cleared_at->format('d M Y, h:i A') }}</p>
                            <p>
                                <strong>Clearance Status:</strong>
                                @if($clearance->is_cleared)
                                    <span class="badge bg-success">Cleared</span>
                                    @if($clearance->is_manual_override)
                                        <span class="badge bg-warning" title="{{ $clearance->override_reason }}">Manual Override</span>
                                    @endif
                                @else
                                    <span class="badge bg-danger">Not Cleared</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Tickets List -->
            <h4>Exam Entry Tickets</h4>
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead class="thead-dark">
                        <tr>
                            <th>Ticket Number</th>
                            <th>Exam</th>
                            <th>Created</th>
                            <th>Expires</th>
                            <th>Status</th>
                            <th>QR Code</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tickets as $ticket)
                            <tr>
                                <td>{{ $ticket->ticket_number }}</td>
                                <td>{{ $ticket->exam->title }}</td>
                                <td>{{ $ticket->created_at->format('d M Y, h:i A') }}</td>
                                <td>
                                    @if($ticket->expires_at)
                                        {{ $ticket->expires_at->format('d M Y, h:i A') }}
                                        @if($ticket->expires_at->isPast())
                                            <span class="badge bg-danger">Expired</span>
                                        @endif
                                    @else
                                        <span class="text-muted">No expiry</span>
                                    @endif
                                </td>
                                <td>
                                    @if(!$ticket->is_active)
                                        <span class="badge bg-danger">Inactive</span>
                                    @elseif($ticket->is_verified)
                                        <span class="badge bg-success">Used</span>
                                    @else
                                        <span class="badge bg-primary">Active</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="qr-container">
                                        {!! QrCode::size(80)->generate($ticket->qr_code) !!}
                                    </div>
                                </td>
                                <td>
                                    @if($ticket->is_active && !$ticket->is_verified)
                                        <button wire:click="deactivateTicket({{ $ticket->id }})" class="btn btn-sm btn-warning">
                                            <i class="fas fa-ban"></i> Deactivate
                                        </button>
                                    @endif
                                    <a href="{{ route('finance.exam.ticket.print', ['ticketId' => $ticket->id]) }}" 
                                        target="_blank" class="btn btn-sm btn-secondary">
                                        <i class="fas fa-print"></i> Print
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">No tickets generated yet</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                
                <div class="mt-3">
                    {{ $tickets->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Generate Ticket Modal -->
    <div class="modal fade" id="generateTicketModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Generate Exam Entry Ticket</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label for="examId">Select Exam</label>
                        <select wire:model="examId" class="form-control @error('examId') is-invalid @enderror">
                            <option value="">-- Select Exam --</option>
                            @foreach($exams as $exam)
                                <option value="{{ $exam->id }}">{{ $exam->title }} ({{ $exam->exam_date ? $exam->exam_date->format('d M Y') : 'No date' }})</option>
                            @endforeach
                        </select>
                        @error('examId')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="expiryDate">Expiry Date (Optional)</label>
                        <input type="date" wire:model="expiryDate" class="form-control" min="{{ now()->format('Y-m-d') }}">
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="expiryTime">Expiry Time (Optional)</label>
                        <input type="time" wire:model="expiryTime" class="form-control">
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> If no expiry is set, the ticket will remain valid until manually deactivated.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" wire:click="generateTicket" class="btn btn-primary">
                        Generate Ticket
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('showGenerateModal', () => {
                new bootstrap.Modal(document.getElementById('generateTicketModal')).show();
            });
        });
    </script>
    @endpush
</div>