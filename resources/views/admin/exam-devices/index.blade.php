<x-dashboard.default title="Exam Devices">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())
        <div class="alert alert-danger">Please correct the highlighted registration details.</div>
    @endif
    <div class="card mb-5">
        <div class="card-header"><h3 class="card-title">Register this device</h3></div>
        <div class="card-body">
            <p class="text-muted">Open this page on the physical device that will be used for examinations. Registration creates a secure, HTTP-only device credential in this browser.</p>
            <form method="POST" action="{{ route('admin.exam-devices.store') }}" class="row g-3">@csrf
                <div class="col-md-4"><label class="form-label">Device label</label><input class="form-control @error('label') is-invalid @enderror" name="label" value="{{ old('label') }}" required placeholder="e.g. ICT Lab PC 12">@error('label')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="col-md-3"><label class="form-label">Device type</label><select class="form-select" name="device_type" required><option value="desktop">Desktop</option><option value="laptop">Laptop</option><option value="mobile">Mobile</option><option value="tablet">Tablet</option></select></div>
                <div class="col-md-3"><label class="form-label">Location (optional)</label><input class="form-control" name="location" placeholder="e.g. ICT Lab"></div>
                <div class="col-md-2 d-flex align-items-end"><button class="btn btn-primary w-100">Register device</button></div>
            </form>
        </div>
    </div>
    <div class="card"><div class="card-header"><h3 class="card-title">Registered devices</h3></div><div class="card-body table-responsive">
        <table class="table table-row-bordered"><thead><tr><th>Device</th><th>Type</th><th>Location</th><th>Status</th><th>Registered by</th><th>Last seen</th><th></th></tr></thead><tbody>
        @forelse($devices as $device)<tr><td>{{ $device->label }}</td><td class="text-capitalize">{{ $device->device_type }}</td><td>{{ $device->location ?: '—' }}</td><td><span class="badge badge-light-{{ $device->status === 'active' ? 'success' : 'warning' }}">{{ $device->status }}</span></td><td>{{ $device->registeredBy->name }}</td><td>{{ $device->last_seen_at?->diffForHumans() ?: 'Never' }}</td><td class="text-end"><form class="d-inline" method="POST" action="{{ route($device->status === 'active' ? 'admin.exam-devices.suspend' : 'admin.exam-devices.activate', $device) }}">@csrf<button class="btn btn-sm btn-light">{{ $device->status === 'active' ? 'Suspend' : 'Activate' }}</button></form><form class="d-inline" method="POST" action="{{ route('admin.exam-devices.revoke', $device) }}">@csrf<button class="btn btn-sm btn-light-danger">Revoke</button></form></td></tr>@empty<tr><td colspan="7" class="text-center">No devices registered.</td></tr>@endforelse
        </tbody></table>{{ $devices->links() }}
    </div></div>
</x-dashboard.default>
