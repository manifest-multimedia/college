@php
    $statLabels = [
        'hostels' => ['Hostels', 'fa-building', 'primary'],
        'rooms' => ['Rooms', 'fa-door-open', 'info'],
        'beds' => ['Beds', 'fa-bed', 'success'],
        'active_allocations' => ['Active allocations', 'fa-user-check', 'warning'],
        'checked_in' => ['Checked in', 'fa-right-to-bracket', 'danger'],
    ];
@endphp

<x-dashboard.default title="Hostel Operations & Reporting">
    <div class="container-fluid py-6">

        <x-hostels.page-header title="Operations & Reporting" subtitle="Monitor occupancy, export allocation reports and manage hostel wardens." icon="fa-chart-column" />

        <x-hostels.alerts />

        <div class="row g-4 mb-6">
            @foreach($totals as $key => $value)
                @php [$label, $icon, $color] = $statLabels[$key] ?? [ucwords(str_replace('_', ' ', $key)), 'fa-circle', 'primary']; @endphp
                <div class="col-6 col-lg"><x-hostels.stat :label="$label" :value="$value" :icon="$icon" :color="$color" /></div>
            @endforeach
        </div>

        <div class="row g-6 mb-6">
            <div class="col-xl-8">
                <div class="card h-100">
                    <div class="card-header py-5"><div class="card-title flex-column align-items-start">
                        <h3 class="fw-bold mb-0">Occupancy by hostel</h3>
                        <span class="text-muted fs-7 fw-normal">Beds with an active allocation (allocated or checked in).</span>
                    </div></div>
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table table-row-bordered align-middle gy-4 mb-0">
                                <thead><tr class="text-muted fw-bold fs-7 text-uppercase"><th>Hostel</th><th class="text-center">Total beds</th><th class="text-center">Allocated</th><th class="text-center">Free</th><th style="min-width:170px;">Occupancy</th></tr></thead>
                                <tbody>
                                    @forelse($occupancy as $row)
                                        @php $percent = $row->beds > 0 ? round($row->allocated / $row->beds * 100) : 0; @endphp
                                        <tr>
                                            <td class="fw-bold text-gray-800">{{ $row->name }}</td>
                                            <td class="text-center">{{ $row->beds }}</td>
                                            <td class="text-center">{{ $row->allocated }}</td>
                                            <td class="text-center">{{ $row->beds - $row->allocated }}</td>
                                            <td>
                                                <div class="d-flex justify-content-between fs-8 text-muted mb-1"><span>{{ $row->allocated }} / {{ $row->beds }}</span><span>{{ $percent }}%</span></div>
                                                <div class="progress" style="height:6px;"><div class="progress-bar bg-{{ $percent >= 90 ? 'danger' : ($percent >= 70 ? 'warning' : 'success') }}" style="width: {{ $percent }}%"></div></div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="text-center text-muted py-10"><i class="fas fa-building fs-2 d-block mb-3"></i>No hostels yet.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h3 class="fw-bold mb-1"><i class="fas fa-file-csv me-2 text-primary"></i>Export allocations</h3>
                        <p class="text-muted fs-7 mb-4">Download a CSV of allocations. Leave the filters empty to export everything.</p>
                        <form method="GET" action="{{ route('hostels.reports.export') }}">
                            <label class="form-label fs-7 fw-bold" for="export-period">Period</label>
                            <select name="period_id" id="export-period" class="form-select mb-3">
                                <option value="">All periods</option>
                                @foreach($periods as $period)<option value="{{ $period->id }}">{{ $period->name }}</option>@endforeach
                            </select>
                            <label class="form-label fs-7 fw-bold" for="export-status">Status</label>
                            <select name="status" id="export-status" class="form-select mb-4">
                                <option value="">All statuses</option>
                                @foreach(['allocated', 'checked_in', 'checked_out', 'released', 'transferred'] as $status)
                                    <option value="{{ $status }}">{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                                @endforeach
                            </select>
                            <button class="btn btn-primary w-100"><i class="fas fa-download me-1"></i> Export CSV</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-6">
            <div class="col-xl-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h3 class="fw-bold mb-1"><i class="fas fa-user-shield me-2 text-primary"></i>Assign hostel warden</h3>
                        <p class="text-muted fs-7 mb-4">Wardens only see and update allocations in the hostels assigned to them.</p>
                        @if($hostels->isEmpty())
                            <div class="text-muted fs-7 border border-dashed rounded p-4">Create a hostel first, then you can assign a warden to it.</div>
                        @elseif($staff->isEmpty())
                            <div class="text-muted fs-7 border border-dashed rounded p-4">No eligible staff accounts were found. Give a user a role such as Hostel Warden or Hostel Officer first.</div>
                        @else
                            <form method="POST" action="{{ route('hostels.wardens.assign') }}">
                                @csrf
                                <label class="form-label fs-7 fw-bold" for="warden-hostel">Hostel</label>
                                <select name="hostel_id" id="warden-hostel" class="form-select mb-3" required>
                                    <option value="">Select hostel</option>
                                    @foreach($hostels as $hostel)<option value="{{ $hostel->id }}" @selected(old('hostel_id') == $hostel->id)>{{ $hostel->name }}</option>@endforeach
                                </select>
                                <label class="form-label fs-7 fw-bold" for="warden-user">Staff member</label>
                                <select name="user_id" id="warden-user" class="form-select mb-4" required>
                                    <option value="">Select staff member</option>
                                    @foreach($staff as $member)<option value="{{ $member->id }}" @selected(old('user_id') == $member->id)>{{ $member->name }}@if($member->email) ({{ $member->email }})@endif</option>@endforeach
                                </select>
                                <button class="btn btn-primary w-100">Assign warden</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-xl-8">
                <div class="card h-100">
                    <div class="card-header py-5"><div class="card-title flex-column align-items-start">
                        <h3 class="fw-bold mb-0">Current wardens</h3>
                        <span class="text-muted fs-7 fw-normal">{{ $wardens->count() }} active {{ \Illuminate\Support\Str::plural('assignment', $wardens->count()) }}</span>
                    </div></div>
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table table-row-bordered align-middle gy-4 mb-0">
                                <thead><tr class="text-muted fw-bold fs-7 text-uppercase"><th>Hostel</th><th>Warden</th><th>Assigned</th><th class="text-end">Action</th></tr></thead>
                                <tbody>
                                    @forelse($wardens as $warden)
                                        <tr>
                                            <td class="fw-bold text-gray-800">{{ $warden->hostel?->name ?? 'Hostel unavailable' }}</td>
                                            <td>
                                                <div class="text-gray-800">{{ $warden->user?->name ?? 'User #'.$warden->user_id }}</div>
                                                @if($warden->user?->email)<div class="text-muted fs-7">{{ $warden->user->email }}</div>@endif
                                            </td>
                                            <td>{{ $warden->created_at?->format('d M Y') ?? '—' }}</td>
                                            <td class="text-end">
                                                <form method="POST" action="{{ route('hostels.wardens.revoke', $warden) }}" onsubmit="return confirm('Revoke warden access for {{ addslashes($warden->user?->name ?? 'this user') }}?')">
                                                    @csrf
                                                    <button class="btn btn-sm btn-light-danger"><i class="fas fa-user-slash me-1"></i> Revoke</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center text-muted py-10"><i class="fas fa-user-shield fs-2 d-block mb-3"></i>No wardens assigned yet.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-dashboard.default>
