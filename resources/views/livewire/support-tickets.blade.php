<div>
    <x-dashboard.default title="Support Tickets">
        <!--begin::Card-->
        <div class="card">
            <!--begin::Card header-->
            <div class="card-header">
                <div class="card-title">
                    <h3 class="card-title">
                        <i class="ki-duotone ki-ticket fs-1 me-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                        Support Tickets
                    </h3>
                </div>
                <div class="card-toolbar">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#create_ticket_modal">
                        <i class="ki-duotone ki-plus-square fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                        Create New Ticket
                    </button>
                </div>
            </div>
            <!--end::Card header-->

            <!--begin::Card body-->
            <div class="card-body py-4">
                @if (session()->has('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- Tabs for filtering tickets -->
                <ul class="nav nav-tabs nav-line-tabs mb-5 fs-6">
                    <li class="nav-item">
                        <a class="nav-link {{ $statusFilter === 'all' ? 'active' : '' }}" href="#" wire:click.prevent="setStatusFilter('all')">All Tickets ({{ $tickets->count() }})</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $statusFilter === 'open' ? 'active' : '' }}" href="#" wire:click.prevent="setStatusFilter('open')">Open</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $statusFilter === 'in_progress' ? 'active' : '' }}" href="#" wire:click.prevent="setStatusFilter('in_progress')">In Progress</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $statusFilter === 'closed' ? 'active' : '' }}" href="#" wire:click.prevent="setStatusFilter('closed')">Closed</a>
                    </li>
                </ul>

                @if($tickets->isEmpty())
                    <div class="py-10 text-center">
                        <i class="ki-duotone ki-information-5 fs-5x text-gray-400 mb-5">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                        <h3 class="text-gray-600 mb-2">No tickets found</h3>
                        <p class="text-gray-400">Click "Create New Ticket" to submit a support request.</p>
                    </div>
                @else
                    <!--begin::Table -->
                    <div class="table-responsive">
                        <table class="table align-middle table-row-dashed fs-6 gy-5">
                            <thead>
                                <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                    <th class="min-w-125px">Ticket ID</th>
                                    <th class="min-w-175px">Subject</th>
                                    <th class="min-w-125px">Category</th>
                                    <th class="min-w-100px">Priority</th>
                                    <th class="min-w-125px">Status</th>
                                    <th class="min-w-125px">Last Updated</th>
                                    <th class="text-end min-w-100px">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-600 fw-semibold">
                                @foreach($tickets as $ticket)
                                    <tr>
                                        <td>
                                            <a href="{{ route('support.ticket.detail', $ticket->id) }}" class="text-gray-800 text-hover-primary mb-1">{{ $ticket->ticket_number }}</a>
                                        </td>
                                        <td>{{ Str::limit($ticket->subject, 50) }}</td>
                                        <td>{{ $ticket->category->name }}</td>
                                        <td>
                                            @php
                                                $badgeClass = match($ticket->priority) {
                                                    'Low' => 'badge-light-info',
                                                    'Medium' => 'badge-light-primary',
                                                    'High' => 'badge-light-warning',
                                                    'Urgent' => 'badge-light-danger',
                                                    default => 'badge-light-secondary',
                                                };
                                            @endphp
                                            <span class="badge {{ $badgeClass }}">{{ $ticket->priority }}</span>
                                        </td>
                                        <td>
                                            @php
                                                $statusBadge = match($ticket->status) {
                                                    'Open' => 'badge-light-success',
                                                    'In Progress' => 'badge-light-warning',
                                                    'Resolved' => 'badge-light-info',
                                                    'Closed' => 'badge-light-danger',
                                                    default => 'badge-light-secondary',
                                                };
                                            @endphp
                                            <span class="badge {{ $statusBadge }}">{{ $ticket->status }}</span>
                                        </td>
                                        <td>{{ $ticket->updated_at->diffForHumans() }}</td>
                                        <td class="text-end">
                                            <a href="{{ route('support.ticket.detail', $ticket->id) }}" class="btn btn-sm btn-light-primary">
                                                View
                                            </a>
                                            @if(!$ticket->isClosed())
                                                <button wire:click="closeTicket({{ $ticket->id }})" class="btn btn-sm btn-light-danger" wire:confirm="Are you sure you want to close this ticket?">
                                                    Close
                                                </button>
                                            @else
                                                <button wire:click="reopenTicket({{ $ticket->id }})" class="btn btn-sm btn-light-success" wire:confirm="Are you sure you want to reopen this ticket?">
                                                    Reopen
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <!--end::Table-->
                @endif
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Card-->

        <!-- Create Ticket Modal -->
        <div class="modal fade" id="create_ticket_modal" tabindex="-1" aria-hidden="true" wire:ignore.self>
            <div class="modal-dialog modal-dialog-centered mw-650px">
                <div class="modal-content">
                    <form wire:submit.prevent="createTicket">
                        <div class="modal-header">
                            <h2 class="fw-bold">Create New Support Ticket</h2>
                            <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                                <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                            </div>
                        </div>
                        <div class="modal-body py-10 px-lg-17">
                            <!-- Subject Field -->
                            <div class="fv-row mb-7">
                                <label class="required fs-6 fw-semibold mb-2">Subject</label>
                                <input type="text" class="form-control form-control-solid @error('subject') is-invalid @enderror" placeholder="Enter ticket subject" wire:model="subject" />
                                @error('subject') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <!-- Category Field -->
                            <div class="fv-row mb-7">
                                <label class="required fs-6 fw-semibold mb-2">Category</label>
                                <select class="form-select form-select-solid @error('category_id') is-invalid @enderror" wire:model="category_id">
                                    <option value="">Select a category...</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <!-- Priority Field -->
                            <div class="fv-row mb-7">
                                <label class="required fs-6 fw-semibold mb-2">Priority</label>
                                <select class="form-select form-select-solid @error('priority') is-invalid @enderror" wire:model="priority">
                                    <option value="Low">Low</option>
                                    <option value="Medium">Medium</option>
                                    <option value="High">High</option>
                                    <option value="Urgent">Urgent</option>
                                </select>
                                @error('priority') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <!-- Message Field -->
                            <div class="fv-row mb-7">
                                <label class="required fs-6 fw-semibold mb-2">Message</label>
                                <textarea class="form-control form-control-solid @error('message') is-invalid @enderror" rows="6" wire:model="message" placeholder="Describe your issue in detail"></textarea>
                                @error('message') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <!-- Attachments Field -->
                            <div class="fv-row mb-7">
                                <label class="fs-6 fw-semibold mb-2">Attachments (Optional)</label>
                                <input type="file" class="form-control form-control-solid @error('attachments.*') is-invalid @enderror" wire:model="attachments" multiple />
                                <div class="form-text">You can upload up to 5 files (Max 2MB each).</div>
                                @error('attachments.*') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                
                                @if($attachments)
                                    <div class="mt-3">
                                        @foreach($attachments as $index => $file)
                                            <div class="badge badge-light-primary me-2">{{ $file->getClientOriginalName() }}</div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="modal-footer flex-center">
                            <button type="button" data-bs-dismiss="modal" class="btn btn-light me-3">Cancel</button>
                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                <span wire:loading.remove>Submit</span>
                                <span wire:loading>
                                    <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                    Please wait...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </x-dashboard.default>

    @push('scripts')
    <script>
        // Close modal on successful ticket creation
        Livewire.on('close-modal', (modalId) => {
            const modal = document.getElementById(modalId);
            if (modal) {
                const bsModal = bootstrap.Modal.getInstance(modal);
                if (bsModal) {
                    bsModal.hide();
                }
            }
        });
    </script>
    @endpush
</div>
