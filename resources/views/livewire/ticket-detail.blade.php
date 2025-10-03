<div>
    <x-dashboard.default title="Ticket Detail - {{ $ticket->ticket_number }}">
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
                        {{ $ticket->ticket_number }}: {{ $ticket->subject }}
                    </h3>
                </div>
                <div class="card-toolbar">
                    <a href="{{ route('support.tickets') }}" class="btn btn-sm btn-light-primary me-2">
                        <i class="ki-duotone ki-arrow-left fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        Back to Tickets
                    </a>
                    @if(!$ticket->isClosed())
                        <button wire:click="closeTicket" class="btn btn-sm btn-danger" wire:confirm="Are you sure you want to close this ticket?">
                            <i class="ki-duotone ki-cross-circle fs-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            Close Ticket
                        </button>
                    @else
                        <button wire:click="reopenTicket" class="btn btn-sm btn-success" wire:confirm="Are you sure you want to reopen this ticket?">
                            <i class="ki-duotone ki-check-circle fs-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            Reopen Ticket
                        </button>
                    @endif
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

                @if (session()->has('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!--begin::Ticket Info-->
                <div class="d-flex flex-wrap mb-10 gap-5">
                    <div class="border border-dashed border-gray-300 rounded py-3 px-4">
                        <div class="fw-semibold text-gray-500">Category:</div>
                        <div class="fs-5 fw-bold text-gray-800">{{ $ticket->category->name }}</div>
                    </div>
                    <div class="border border-dashed border-gray-300 rounded py-3 px-4">
                        <div class="fw-semibold text-gray-500">Status:</div>
                        <div class="fs-5 fw-bold text-gray-800">
                            @php
                                $statusBadge = match($ticket->status) {
                                    'Open' => 'badge-light-success',
                                    'In Progress' => 'badge-light-warning',
                                    'Resolved' => 'badge-light-info',
                                    'Closed' => 'badge-light-danger',
                                    default => 'badge-light-secondary',
                                };
                            @endphp
                            <span class="badge {{ $statusBadge }} fs-6">{{ $ticket->status }}</span>
                        </div>
                    </div>
                    <div class="border border-dashed border-gray-300 rounded py-3 px-4">
                        <div class="fw-semibold text-gray-500">Priority:</div>
                        <div class="fs-5 fw-bold text-gray-800">
                            @php
                                $badgeClass = match($ticket->priority) {
                                    'Low' => 'badge-light-info',
                                    'Medium' => 'badge-light-primary',
                                    'High' => 'badge-light-warning',
                                    'Urgent' => 'badge-light-danger',
                                    default => 'badge-light-secondary',
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }} fs-6">{{ $ticket->priority }}</span>
                        </div>
                    </div>
                    <div class="border border-dashed border-gray-300 rounded py-3 px-4">
                        <div class="fw-semibold text-gray-500">Created On:</div>
                        <div class="fs-5 fw-bold text-gray-800">{{ $ticket->created_at->format('M d, Y') }}</div>
                    </div>
                    @if($ticket->assignedTo)
                    <div class="border border-dashed border-gray-300 rounded py-3 px-4">
                        <div class="fw-semibold text-gray-500">Assigned To:</div>
                        <div class="fs-5 fw-bold text-gray-800">{{ $ticket->assignedTo->name }}</div>
                    </div>
                    @endif
                </div>
                <!--end::Ticket Info-->

                <!--begin::Conversation Thread-->
                <div class="mb-10">
                    <h3 class="fw-bold mb-5">Conversation</h3>
                    
                    <!--begin::Original Message-->
                    <div class="border border-dashed border-gray-300 rounded p-7 mb-7">
                        <div class="d-flex flex-stack mb-5">
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-circle symbol-40px me-3">
                                    <img src="{{ $ticket->user->profile_photo_url }}" alt="Profile" />
                                </div>
                                <div>
                                    <a href="#" class="fs-5 fw-bold text-gray-900 text-hover-primary me-1">{{ $ticket->user->name }}</a>
                                    <span class="text-muted fs-7 d-block">{{ $ticket->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="fs-6 text-gray-800 mb-5">
                            {!! nl2br(e($ticket->message)) !!}
                        </div>

                        @if($ticket->attachments->count() > 0)
                            <div class="separator separator-dashed my-5"></div>
                            <div class="fs-7 text-muted mb-2">Attachments:</div>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($ticket->attachments as $attachment)
                                    <a href="{{ Storage::url($attachment->file_path) }}" target="_blank" class="btn btn-sm btn-light-primary">
                                        <i class="ki-duotone ki-file fs-3">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                        {{ $attachment->file_name }} ({{ $attachment->formatted_size }})
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <!--end::Original Message-->

                    <!--begin::Replies-->
                    @foreach($ticket->replies as $reply)
                        <div class="border border-dashed border-gray-300 rounded p-7 mb-7 {{ $reply->user_id !== $ticket->user_id ? 'bg-light-primary' : '' }}">
                            <div class="d-flex flex-stack mb-5">
                                <div class="d-flex align-items-center">
                                    <div class="symbol symbol-circle symbol-40px me-3">
                                        @if($reply->user->profile_photo_url)
                                            <img src="{{ $reply->user->profile_photo_url }}" alt="Profile" />
                                        @else
                                            <div class="symbol-label bg-light-primary">
                                                <i class="ki-duotone ki-abstract-26 fs-2 text-primary">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <a href="#" class="fs-5 fw-bold text-gray-900 text-hover-primary me-1">{{ $reply->user->name }}</a>
                                        @if($reply->user_id !== $ticket->user_id)
                                            <span class="badge badge-sm badge-light-primary">Support Team</span>
                                        @endif
                                        <span class="text-muted fs-7 d-block">{{ $reply->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="fs-6 text-gray-800 mb-5">
                                {!! nl2br(e($reply->message)) !!}
                            </div>

                            @if($reply->attachments->count() > 0)
                                <div class="separator separator-dashed my-5"></div>
                                <div class="fs-7 text-muted mb-2">Attachments:</div>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($reply->attachments as $attachment)
                                        <a href="{{ Storage::url($attachment->file_path) }}" target="_blank" class="btn btn-sm btn-light-primary">
                                            <i class="ki-duotone ki-file fs-3">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                            {{ $attachment->file_name }} ({{ $attachment->formatted_size }})
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                    <!--end::Replies-->
                </div>
                <!--end::Conversation Thread-->

                <!--begin::Reply Form-->
                @if(!$ticket->isClosed())
                    <div class="card bg-light">
                        <div class="card-header">
                            <h4 class="card-title">Add Reply</h4>
                        </div>
                        <div class="card-body">
                            <form wire:submit.prevent="submitReply">
                                <div class="mb-5">
                                    <label class="required fs-6 fw-semibold mb-2">Your Reply</label>
                                    <textarea class="form-control form-control-solid @error('replyMessage') is-invalid @enderror" rows="6" wire:model="replyMessage" placeholder="Type your reply here"></textarea>
                                    @error('replyMessage') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                
                                <div class="mb-5">
                                    <label class="fs-6 fw-semibold mb-2">Attachments (Optional)</label>
                                    <input type="file" class="form-control form-control-solid @error('attachments.*') is-invalid @enderror" wire:model="attachments" multiple />
                                    <div class="form-text">You can upload up to 5 files (Max 2MB each).</div>
                                    @error('attachments.*') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    
                                    @if($attachments)
                                        <div class="mt-3">
                                            @foreach($attachments as $file)
                                                <div class="badge badge-light-primary me-2">{{ $file->getClientOriginalName() }}</div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>

                                <div class="text-end">
                                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                        <span wire:loading.remove>
                                            <i class="ki-duotone ki-send fs-2">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                            Submit Reply
                                        </span>
                                        <span wire:loading>
                                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                            Please wait...
                                        </span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="alert alert-info">
                        <i class="ki-duotone ki-information-5 fs-2x text-info me-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                        This ticket is closed. Click "Reopen Ticket" above to add more replies.
                    </div>
                @endif
                <!--end::Reply Form-->
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Card-->
    </x-dashboard.default>
</div>
