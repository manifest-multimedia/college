<div>
    {{-- Flash Messages --}}
    @if(session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    
    @if(session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Page Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <i class="fas fa-credit-card fs-1 text-primary me-3"></i>
                    <div>
                        <h1 class="fs-2 fw-bold text-gray-800 mb-0">Payment Providers</h1>
                        <p class="text-muted mb-0">Manage payment gateways and generate API secret keys</p>
                    </div>
                </div>
                <button wire:click="toggleForm" class="btn btn-primary">
                    <i class="fas fa-{{ $showForm ? 'times' : 'plus' }} me-2"></i>
                    {{ $showForm ? 'Cancel' : 'Add New Provider' }}
                </button>
            </div>
        </div>
    </div>

    {{-- Add Provider Form --}}
    @if($showForm)
        <div class="card mb-5 border border-primary border-dashed">
            <div class="card-header bg-light">
                <h3 class="card-title fw-bold">
                    <i class="fas fa-key me-2 text-warning"></i>Generate New API Credentials
                </h3>
            </div>
            <div class="card-body">
                <form wire:submit="generateCredentials">
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label class="form-label fw-bold required">Provider Name</label>
                            <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror" placeholder="e.g. Paystack">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="form-label fw-bold required">Provider Code</label>
                            <input type="text" wire:model="code" class="form-control @error('code') is-invalid @enderror" placeholder="e.g. paystack_v1">
                            @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="d-flex justify-content-end mt-2">
                        <button type="submit" class="btn btn-success" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="generateCredentials">
                                <i class="fas fa-cogs me-2"></i>Generate Keys
                            </span>
                            <span wire:loading wire:target="generateCredentials">
                                <i class="fas fa-spinner fa-spin me-2"></i>Generating...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Providers Table --}}
    <div class="card mb-5">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                    <thead>
                        <tr class="fw-bolder text-muted">
                            <th class="ps-4">Name</th>
                            <th>Code</th>
                            <th>Status</th>
                            <th>Created By</th>
                            <th>Created At</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($providers as $provider)
                            <tr>
                                <td class="ps-4 fw-bold">{{ $provider->name }}</td>
                                <td><span class="badge badge-light-primary">{{ $provider->code }}</span></td>
                                <td>
                                    @if($provider->status === 'active')
                                        <span class="badge badge-light-success">Active</span>
                                    @else
                                        <span class="badge badge-light-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>{{ $provider->creator->name ?? 'System' }}</td>
                                <td>{{ $provider->created_at->format('M d, Y H:i') }}</td>
                                <td class="text-end pe-4">
                                    <button wire:click="toggleStatus({{ $provider->id }})" class="btn btn-sm btn-light-{{ $provider->status === 'active' ? 'danger' : 'success' }}">
                                        {{ $provider->status === 'active' ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">No payment providers configured.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Modal for Newly Generated Key --}}
    @if($showGeneratedKey && $newlyGeneratedKey)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title fw-bold text-white">
                        <i class="fas fa-key me-2"></i>New API Secret Key Generated
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeGeneratedKeyModal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning mb-4">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Important:</strong> Copy this secret key now! You will not be able to see it again. This key should be used by the external payment gateway or webhook to authenticate requests.
                    </div>
                    
                    <label class="form-label fw-bold">Your API Secret Key:</label>
                    <div class="input-group mb-3">
                        <input type="text" readonly value="{{ $newlyGeneratedKey }}" class="form-control font-monospace" id="newApiKey">
                        <button type="button" class="btn btn-success" onclick="copyNewApiKey()">
                            <i class="fas fa-copy me-2"></i>Copy
                        </button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" wire:click="closeGeneratedKeyModal">
                        I've Copied the Key
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function copyNewApiKey() {
            const input = document.getElementById('newApiKey');
            input.select();
            navigator.clipboard.writeText(input.value).then(() => {
                alert('API secret key copied to clipboard!');
            });
        }
    </script>
    @endif
</div>
