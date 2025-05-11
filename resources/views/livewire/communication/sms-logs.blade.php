<div>
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <h3 class="card-title">
                    <i class="ki-duotone ki-message-text-2 fs-1 me-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                        <span class="path3"></span>
                    </i>
                    SMS Logs
                </h3>
            </div>
        </div>
        
        <div class="card-body">
            @if (session()->has('success'))
                <div class="alert alert-success mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if (session()->has('error'))
                <div class="alert alert-danger mb-4">
                    {{ session('error') }}
                </div>
            @endif
            
            <!-- Filter Controls -->
            <div class="mb-5">
                <div class="row g-3">
                    <!-- Search -->
                    <div class="col-md-4">
                        <div class="d-flex align-items-center position-relative">
                            <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-4">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            <input type="text" class="form-control form-control-solid ps-12" 
                                placeholder="Search by recipient or message..." wire:model.live.debounce.300ms="searchTerm">
                        </div>
                    </div>
                    
                    <!-- Status Filter -->
                    <div class="col-md-2">
                        <select class="form-select form-select-solid" wire:model.live="statusFilter">
                            <option value="">All Statuses</option>
                            <option value="sent">Sent</option>
                            <option value="delivered">Delivered</option>
                            <option value="failed">Failed</option>
                            <option value="pending">Pending</option>
                        </select>
                    </div>
                    
                    <!-- Type Filter -->
                    <div class="col-md-2">
                        <select class="form-select form-select-solid" wire:model.live="typeFilter">
                            <option value="">All Types</option>
                            <option value="single">Single</option>
                            <option value="bulk">Bulk</option>
                            <option value="group">Group</option>
                        </select>
                    </div>
                    
                    <!-- Provider Filter -->
                    <div class="col-md-2">
                        <select class="form-select form-select-solid" wire:model.live="providerFilter">
                            <option value="">All Providers</option>
                            @foreach($providers as $provider)
                                <option value="{{ $provider }}">{{ ucfirst($provider) }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Date Range Controls -->
                    <div class="col-md-2">
                        <button type="button" class="btn btn-light-primary" data-bs-toggle="modal" data-bs-target="#dateRangeModal">
                            <i class="ki-duotone ki-calendar fs-3">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            Date Range
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                    <thead>
                        <tr class="fw-bold text-muted bg-light">
                            <th class="min-w-120px">Date & Time</th>
                            <th class="min-w-120px">Recipient</th>
                            <th class="min-w-200px">Message</th>
                            <th class="min-w-90px">Type</th>
                            <th class="min-w-90px">Provider</th>
                            <th class="min-w-90px">Status</th>
                            <th class="min-w-100px text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                            <tr>
                                <td>
                                    <span class="text-dark fw-semibold d-block fs-7">{{ $log->created_at->format('M d, Y H:i') }}</span>
                                </td>
                                <td>
                                    <span class="text-dark fw-semibold d-block fs-7">{{ $log->recipient }}</span>
                                </td>
                                <td>
                                    <span class="text-dark fw-semibold d-block fs-7">{{ Str::limit($log->message, 50) }}</span>
                                </td>
                                <td>
                                    @if($log->type == 'single')
                                        <span class="badge badge-light-primary">Single</span>
                                    @elseif($log->type == 'bulk')
                                        <span class="badge badge-light-info">Bulk</span>
                                    @else
                                        <span class="badge badge-light-success">Group</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-light-dark">{{ ucfirst($log->provider) }}</span>
                                </td>
                                <td>
                                    @if($log->status == 'sent' || $log->status == 'delivered')
                                        <span class="badge badge-light-success">{{ ucfirst($log->status) }}</span>
                                    @elseif($log->status == 'pending')
                                        <span class="badge badge-light-warning">{{ ucfirst($log->status) }}</span>
                                    @else
                                        <span class="badge badge-light-danger">{{ ucfirst($log->status) }}</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end flex-shrink-0">
                                        <button type="button" wire:click="viewDetails({{ $log->id }})" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1" title="View Details">
                                            <i class="ki-duotone ki-eye fs-3">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                                <span class="path3"></span>
                                            </i>
                                        </button>
                                        {{-- Delete --}}
                                        
                                        @if(auth()->user()->hasRole('System'))
                                            <button type="button" wire:click="delete({{ $log->id }})" class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm" title="Delete">
                                                <i class="ki-duotone ki-trash fs-3">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                    <span class="path3"></span>
                                                </i>
                                            </button>
                                        @endif

                                        @if($log->status == 'failed' || $log->status == 'pending')
                                            <button type="button" wire:click="resendSms({{ $log->id }})" class="btn btn-icon btn-bg-light btn-active-color-success btn-sm" title="Resend">
                                                <i class="ki-duotone ki-send fs-3">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">No SMS logs found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="d-flex justify-content-between align-items-center flex-wrap mt-5">
                {{ $logs->links() }}
            </div>
        </div>
    </div>
    
    <!-- Date Range Modal -->
    <div class="modal fade" id="dateRangeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Select Date Range</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Start Date</label>
                        <input type="date" class="form-control" wire:model.live="startDate">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">End Date</label>
                        <input type="date" class="form-control" wire:model.live="endDate">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Apply</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- SMS Details Modal -->
    <div class="modal fade" id="logDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">SMS Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if($selectedLog)
                        <div class="mb-3">
                            <h4>Message Information</h4>
                            <table class="table table-row-bordered gy-3">
                                <tr>
                                    <th class="fw-bold w-25">Sent At:</th>
                                    <td>{{ $selectedLog->created_at->format('M d, Y H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th class="fw-bold">Recipient:</th>
                                    <td>{{ $selectedLog->recipient }}</td>
                                </tr>
                                <tr>
                                    <th class="fw-bold">Status:</th>
                                    <td>
                                        @if($selectedLog->status == 'sent' || $selectedLog->status == 'delivered')
                                            <span class="badge badge-light-success">{{ ucfirst($selectedLog->status) }}</span>
                                        @elseif($selectedLog->status == 'pending')
                                            <span class="badge badge-light-warning">{{ ucfirst($selectedLog->status) }}</span>
                                        @else
                                            <span class="badge badge-light-danger">{{ ucfirst($selectedLog->status) }}</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th class="fw-bold">Type:</th>
                                    <td>{{ ucfirst($selectedLog->type) }}</td>
                                </tr>
                                <tr>
                                    <th class="fw-bold">Provider:</th>
                                    <td>{{ ucfirst($selectedLog->provider) }}</td>
                                </tr>
                                <tr>
                                    <th class="fw-bold">User:</th>
                                    <td>{{ $selectedLog->user ? $selectedLog->user->name : 'System' }}</td>
                                </tr>
                                <tr>
                                    <th class="fw-bold">Full Message:</th>
                                    <td>{{ $selectedLog->message }}</td>
                                </tr>
                                @if(is_array($selectedLog->response_data) || is_object($selectedLog->response_data))
                                <tr>
                                    <th class="fw-bold">Response Data:</th>
                                    <td>
                                        <div class="bg-light p-3 rounded" style="max-width: 100%; overflow-x: auto;">
                                            <pre style="white-space: pre-wrap; word-break: break-word; margin: 0;">{{ json_encode($selectedLog->response_data, JSON_PRETTY_PRINT) }}</pre>
                                        </div>
                                    </td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    @if($selectedLog && ($selectedLog->status == 'failed' || $selectedLog->status == 'pending'))
                        <button type="button" wire:click="resendSms({{ $selectedLog->id }})" class="btn btn-primary">
                            Resend Message
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    @push('scripts')
    <script>
        document.addEventListener('livewire:initialized', () => {
            const logDetailsModal = new bootstrap.Modal(document.getElementById('logDetailsModal'));
            
            @this.on('openLogDetailsModal', () => {
                logDetailsModal.show();
            });
        });
    </script>
    @endpush
</div>
