<x-dashboard.default title="Election IP Blacklist">
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="mb-1">Election IP Blacklist</h3>
                <p class="text-muted mb-0">Ban IP addresses from voting and verification pages.</p>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Add IP to Blacklist</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.elections.ip-blacklist.store') }}" class="row g-3">
                    @csrf
                    <div class="col-md-4">
                        <label for="ip_address" class="form-label">IP Address</label>
                        <input id="ip_address" name="ip_address" type="text" class="form-control @error('ip_address') is-invalid @enderror" placeholder="e.g. 172.70.91.157" required>
                        @error('ip_address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="reason" class="form-label">Reason (optional)</label>
                        <input id="reason" name="reason" type="text" class="form-control @error('reason') is-invalid @enderror" placeholder="Reason for blocking this IP">
                        @error('reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-danger w-100">Block IP</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <form method="GET" action="{{ route('admin.elections.ip-blacklist.index') }}" class="row g-2 align-items-center">
                    <div class="col-md-5">
                        <input name="search" value="{{ $search }}" class="form-control" placeholder="Search by IP or reason">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-outline-secondary w-100">Search</button>
                    </div>
                </form>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>IP Address</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($entries as $entry)
                                <tr>
                                    <td>{{ $entry->ip_address }}</td>
                                    <td>{{ $entry->reason ?: 'N/A' }}</td>
                                    <td>
                                        @if ($entry->is_active)
                                            <span class="badge bg-danger">Blocked</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td>{{ $entry->created_at?->format('Y-m-d H:i:s') }}</td>
                                    <td class="text-end">
                                        <form method="POST" action="{{ route('admin.elections.ip-blacklist.toggle', $entry) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-warning">
                                                {{ $entry->is_active ? 'Disable' : 'Enable' }}
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.elections.ip-blacklist.destroy', $entry) }}" class="d-inline" onsubmit="return confirm('Remove this IP from blacklist?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No blacklist entries found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                {{ $entries->links() }}
            </div>
        </div>
    </div>
</x-dashboard.default>
