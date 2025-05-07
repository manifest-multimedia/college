<div>
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title">
                    <i class="fas fa-ticket-alt mr-2"></i> Exam Entry Tickets
                </h3>
                <div class="d-flex">
                    <div class="input-group mr-2" style="width: 250px;">
                        <input wire:model.live.debounce.300ms="search" type="text" class="form-control" placeholder="Search tickets...">
                        <div class="input-group-append">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                    </div>
                    <div class="mr-2" style="width: 180px;">
                        <select wire:model.live="examFilter" class="form-control">
                            <option value="">All Exams</option>
                            @foreach($exams as $exam)
                                <option value="{{ $exam->id }}">{{ $exam->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mr-2" style="width: 140px;">
                        <select wire:model.live="statusFilter" class="form-control">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="used">Used</option>
                            <option value="expired">Expired</option>
                            <option value="revoked">Revoked</option>
                        </select>
                    </div>
                    <div style="width: 70px;">
                        <select wire:model.live="perPage" class="form-control">
                            <option>10</option>
                            <option>25</option>
                            <option>50</option>
                            <option>100</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Ticket #</th>
                            <th>Student</th>
                            <th>Exam</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Used At</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tickets as $ticket)
                            <tr>
                                <td>
                                    <span class="text-primary font-weight-bold">{{ $ticket->ticket_number }}</span><br>
                                    <small class="text-muted">{{ $ticket->verification_code }}</small>
                                </td>
                                <td>
                                    @if($ticket->examClearance && $ticket->examClearance->student)
                                        {{ $ticket->examClearance->student->name }}<br>
                                        <small class="text-muted">{{ $ticket->examClearance->student->student_id }}</small>
                                    @else
                                        <span class="text-muted">No student data</span>
                                    @endif
                                </td>
                                <td>
                                    @if($ticket->examClearance && $ticket->examClearance->exam)
                                        {{ $ticket->examClearance->exam->title }}
                                    @else
                                        <span class="text-muted">No exam data</span>
                                    @endif
                                </td>
                                <td>
                                    @switch($ticket->status)
                                        @case('active')
                                            <span class="badge badge-success">Active</span>
                                            @break
                                        @case('used')
                                            <span class="badge badge-info">Used</span>
                                            @break
                                        @case('expired')
                                            <span class="badge badge-warning">Expired</span>
                                            @break
                                        @case('revoked')
                                            <span class="badge badge-danger">Revoked</span>
                                            @break
                                        @default
                                            <span class="badge badge-secondary">{{ $ticket->status }}</span>
                                    @endswitch
                                </td>
                                <td>{{ $ticket->created_at->format('M d, Y H:i') }}</td>
                                <td>{{ $ticket->used_at ? $ticket->used_at->format('M d, Y H:i') : 'Not used' }}</td>
                                <td class="text-right">
                                    <button wire:click="viewTicketDetails({{ $ticket->id }})" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button wire:click="printTicket({{ $ticket->id }})" class="btn btn-sm btn-primary">
                                        <i class="fas fa-print"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="empty-state">
                                        <i class="fas fa-ticket-alt fa-3x text-muted mb-3"></i>
                                        <p>No exam entry tickets found</p>
                                        @if($search || $examFilter || $statusFilter)
                                            <button wire:click="$set('search', ''); $set('examFilter', ''); $set('statusFilter', '')" class="btn btn-sm btn-outline-primary">
                                                Clear filters
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    Showing {{ $tickets->firstItem() ?? 0 }} to {{ $tickets->lastItem() ?? 0 }} of {{ $tickets->total() }} tickets
                </div>
                <div>
                    {{ $tickets->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Ticket Details Modal -->
    <div class="modal fade" id="ticketDetailsModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ticket Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    @if($selectedTicket)
                        <div class="text-center mb-4">
                            <div style="width: 180px; height: 180px; margin: 0 auto;">
                                {!! QrCode::size(180)->generate($selectedTicket->verification_code) !!}
                            </div>
                            <p class="mt-2 font-weight-bold">{{ $selectedTicket->verification_code }}</p>
                        </div>

                        <div class="card bg-light mb-3">
                            <div class="card-body p-3">
                                <div class="row">
                                    <div class="col-5 text-muted">Ticket Number:</div>
                                    <div class="col-7 font-weight-bold">{{ $selectedTicket->ticket_number }}</div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-5 text-muted">Status:</div>
                                    <div class="col-7">
                                        @switch($selectedTicket->status)
                                            @case('active')
                                                <span class="badge badge-success">Active</span>
                                                @break
                                            @case('used')
                                                <span class="badge badge-info">Used</span>
                                                @break
                                            @case('expired')
                                                <span class="badge badge-warning">Expired</span>
                                                @break
                                            @case('revoked')
                                                <span class="badge badge-danger">Revoked</span>
                                                @break
                                            @default
                                                <span class="badge badge-secondary">{{ $selectedTicket->status }}</span>
                                        @endswitch
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-5 text-muted">Student:</div>
                                    <div class="col-7">
                                        @if($selectedTicket->examClearance && $selectedTicket->examClearance->student)
                                            {{ $selectedTicket->examClearance->student->name }}<br>
                                            <small class="text-muted">ID: {{ $selectedTicket->examClearance->student->student_id }}</small>
                                        @else
                                            <span class="text-muted">No student data</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-5 text-muted">Exam:</div>
                                    <div class="col-7">
                                        @if($selectedTicket->examClearance && $selectedTicket->examClearance->exam)
                                            {{ $selectedTicket->examClearance->exam->title }}
                                        @else
                                            <span class="text-muted">No exam data</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-5 text-muted">Created At:</div>
                                    <div class="col-7">{{ $selectedTicket->created_at->format('M d, Y H:i') }}</div>
                                </div>
                                @if($selectedTicket->used_at)
                                <div class="row mt-2">
                                    <div class="col-5 text-muted">Used At:</div>
                                    <div class="col-7">{{ $selectedTicket->used_at->format('M d, Y H:i') }}</div>
                                </div>
                                @endif
                                @if($selectedTicket->expires_at)
                                <div class="row mt-2">
                                    <div class="col-5 text-muted">Expires At:</div>
                                    <div class="col-7">{{ $selectedTicket->expires_at->format('M d, Y H:i') }}</div>
                                </div>
                                @endif
                                @if($selectedTicket->notes)
                                <div class="row mt-2">
                                    <div class="col-5 text-muted">Notes:</div>
                                    <div class="col-7">{{ $selectedTicket->notes }}</div>
                                </div>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    @if($selectedTicket)
                        <button wire:click="printTicket({{ $selectedTicket->id }})" class="btn btn-primary">
                            <i class="fas fa-print mr-1"></i> Print Ticket
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('livewire:initialized', () => {
            @this.on('show-ticket-details-modal', () => {
                $('#ticketDetailsModal').modal('show');
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            $('#ticketDetailsModal').on('hidden.bs.modal', function () {
                @this.call('closeTicketDetails');
            });
        });
    </script>
    @endpush
</div>
