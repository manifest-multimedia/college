@php
    $setting = fn (string $key, $default = null) => old($key, $settings?->$key ?? $default);
    $enabled = (bool) $setting('enabled', false);
    $requirePayment = (bool) $setting('require_payment', false);

    $workflowOptions = ['staff' => 'Staff-managed', 'application' => 'Applications', 'hybrid' => 'Hybrid', 'automatic' => 'Automatic (future integration)'];
    $unitOptions = ['bed' => 'Bed', 'room' => 'Room (represented by a single allocation unit)'];
    $periodOptions = ['academic_year' => 'Academic year', 'semester' => 'Semester', 'custom' => 'Custom'];
    $ruleToggles = [
        'enabled' => ['Enable module', 'Turn hostel management on or off for the whole institution.', false],
        'enforce_gender' => ['Enforce gender restrictions', 'Only allocate students to hostels that match their gender policy.', true],
        'require_check_in' => ['Require check-in', 'Students must be checked in after they are allocated a bed.', true],
        'allow_applications' => ['Enable applications (future workflow)', 'Let students apply for accommodation from their portal.', false],
        'require_payment' => ['Require payment clearance', 'Blocks allocation until the student has paid the minimum accommodation fee.', false],
        'auto_waitlist' => ['Enable automatic waiting list', 'Place approved applicants on a waiting list when no bed is free.', true],
    ];
    $genderLabels = ['mixed' => 'Mixed', 'male' => 'Male only', 'female' => 'Female only'];
    $allocationStatusBadge = ['allocated' => 'primary', 'checked_in' => 'success', 'checked_out' => 'secondary', 'released' => 'warning', 'transferred' => 'info'];
    $periodStatusBadge = ['draft' => 'secondary', 'open' => 'success', 'closed' => 'danger'];
    $openPeriods = $periods->where('status', 'open');
    $occupancyPercent = $totals['beds'] > 0 ? round($totals['occupied'] / $totals['beds'] * 100) : 0;
@endphp

<x-dashboard.default title="Hostel Management">
    <div class="container-fluid py-6">

        <x-hostels.page-header title="Hostel Management" subtitle="Configure accommodation, create inventory, and manage student allocations." :back="false">
            <a class="btn btn-light-primary" href="{{ route('hostels.applications') }}"><i class="fas fa-clipboard-check me-1"></i> Application Approvals</a>
            <a class="btn btn-light-primary" href="{{ route('hostels.operations') }}"><i class="fas fa-chart-column me-1"></i> Operations &amp; Reports</a>
            <a class="btn btn-light-primary" href="{{ route('hostels.bulk.index') }}"><i class="fas fa-users me-1"></i> Bulk Allocation &amp; Student Search</a>
        </x-hostels.page-header>

        <x-hostels.alerts />

        @unless($settings?->enabled)
            <div class="alert alert-warning d-flex align-items-center justify-content-between flex-wrap gap-3" role="alert">
                <div><i class="fas fa-power-off me-2"></i><strong>Hostel management is switched off.</strong> Students cannot be allocated until the module is enabled in Settings.</div>
                <button type="button" class="btn btn-sm btn-warning" data-goto-tab="tab-settings">Open settings</button>
            </div>
        @endunless

        <div class="row g-4 mb-6">
            @foreach([
                ['Hostels', $totals['hostels'], 'fa-building', 'primary'],
                ['Rooms', $totals['rooms'], 'fa-door-open', 'info'],
                ['Beds', $totals['beds'], 'fa-bed', 'success'],
                ['Open periods', $totals['openPeriods'], 'fa-calendar-check', 'warning'],
            ] as [$label, $value, $icon, $color])
                <div class="col-6 col-lg-3"><x-hostels.stat :label="$label" :value="$value" :icon="$icon" :color="$color" /></div>
            @endforeach
        </div>

        <div class="card mb-6">
            <div class="card-body py-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="fw-bold text-gray-800">Overall occupancy</span>
                    <span class="text-muted fs-7">{{ $totals['occupied'] }} of {{ $totals['beds'] }} beds in use ({{ $occupancyPercent }}%)</span>
                </div>
                <div class="progress" style="height:8px;" role="progressbar" aria-valuenow="{{ $occupancyPercent }}" aria-valuemin="0" aria-valuemax="100">
                    <div class="progress-bar bg-primary" style="width: {{ $occupancyPercent }}%"></div>
                </div>
            </div>
        </div>

        <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x mb-6 fs-6" id="hostel-tabs" role="tablist">
            <li class="nav-item" role="presentation"><button class="nav-link active" id="tab-allocations" data-bs-toggle="tab" data-bs-target="#pane-allocations" type="button" role="tab"><i class="fas fa-bed me-2"></i>Allocations</button></li>
            <li class="nav-item" role="presentation"><button class="nav-link" id="tab-inventory" data-bs-toggle="tab" data-bs-target="#pane-inventory" type="button" role="tab"><i class="fas fa-building me-2"></i>Inventory</button></li>
            <li class="nav-item" role="presentation"><button class="nav-link" id="tab-periods" data-bs-toggle="tab" data-bs-target="#pane-periods" type="button" role="tab"><i class="fas fa-calendar-days me-2"></i>Periods</button></li>
            <li class="nav-item" role="presentation"><button class="nav-link" id="tab-settings" data-bs-toggle="tab" data-bs-target="#pane-settings" type="button" role="tab"><i class="fas fa-sliders me-2"></i>Settings</button></li>
        </ul>

        <div class="tab-content">

            {{-- Allocations --}}
            <div class="tab-pane fade show active" id="pane-allocations" role="tabpanel">
                <div class="card">
                    <div class="card-header flex-wrap gap-3 py-5">
                        <div class="card-title flex-column align-items-start">
                            <h3 class="fw-bold mb-0">Recent allocations</h3>
                            <span class="text-muted fs-7 fw-normal">{{ $allocations->total() }} {{ \Illuminate\Support\Str::plural('allocation', $allocations->total()) }}{{ $statusFilter ? ' with status "'.str_replace('_', ' ', $statusFilter).'"' : '' }}</span>
                        </div>
                        <div class="d-flex flex-wrap gap-3 align-items-center">
                            <form method="GET" action="{{ route('hostels.index') }}">
                                <select name="status" class="form-select form-select-sm w-auto" onchange="this.form.submit()" aria-label="Filter by status">
                                    <option value="">All statuses</option>
                                    @foreach(['allocated', 'checked_in', 'checked_out', 'released', 'transferred'] as $status)
                                        <option value="{{ $status }}" @selected($statusFilter === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                                    @endforeach
                                </select>
                            </form>
                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#allocateModal"><i class="fas fa-plus me-1"></i> Allocate student</button>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table table-row-bordered align-middle gy-4 mb-0">
                                <thead>
                                    <tr class="text-muted fw-bold fs-7 text-uppercase">
                                        <th>Student</th><th>Accommodation</th><th>Period</th><th>Status</th><th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($allocations as $allocation)
                                        @php
                                            $studentName = trim(($allocation->student?->first_name ?? '').' '.($allocation->student?->last_name ?? '')) ?: 'Unknown student';
                                            $bed = $allocation->bed;
                                            $placement = $bed ? ($bed->room?->hostel?->name.' / Room '.$bed->room?->room_number.' / Bed '.$bed->bed_code) : 'Bed #'.$allocation->hostel_bed_id;
                                            $isActive = in_array($allocation->status, ['allocated', 'checked_in'], true);
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="fw-bold text-gray-800">{{ $studentName }}</div>
                                                <div class="text-muted fs-7">{{ $allocation->student?->student_id ?? 'ID #'.$allocation->student_id }}</div>
                                            </td>
                                            <td>
                                                <div class="text-gray-800">{{ $bed?->room?->hostel?->name ?? 'Hostel unavailable' }}</div>
                                                <div class="text-muted fs-7">Room {{ $bed?->room?->room_number ?? '—' }} &middot; Bed {{ $bed?->bed_code ?? $allocation->hostel_bed_id }}</div>
                                            </td>
                                            <td>{{ $allocation->period?->name ?? '—' }}</td>
                                            <td>
                                                <span class="badge badge-light-{{ $allocationStatusBadge[$allocation->status] ?? 'secondary' }}">{{ ucfirst(str_replace('_', ' ', $allocation->status)) }}</span>
                                                @if($allocation->status === 'checked_in' && $allocation->checked_in_at)
                                                    <div class="text-muted fs-8 mt-1">since {{ $allocation->checked_in_at->format('d M Y') }}</div>
                                                @elseif($allocation->status === 'checked_out' && $allocation->checked_out_at)
                                                    <div class="text-muted fs-8 mt-1">on {{ $allocation->checked_out_at->format('d M Y') }}</div>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                @if($isActive)
                                                    <div class="d-inline-flex gap-2">
                                                        <form method="POST" action="{{ route('hostels.transition', $allocation) }}" @if($allocation->status === 'checked_in') onsubmit="return confirm('Check {{ addslashes($studentName) }} out of this bed?')" @endif>
                                                            @csrf
                                                            <input type="hidden" name="action" value="{{ $allocation->status === 'allocated' ? 'check_in' : 'check_out' }}">
                                                            <button type="submit" class="btn btn-sm btn-{{ $allocation->status === 'allocated' ? 'light-success' : 'light-primary' }}">
                                                                <i class="fas {{ $allocation->status === 'allocated' ? 'fa-right-to-bracket' : 'fa-right-from-bracket' }} me-1"></i>{{ $allocation->status === 'allocated' ? 'Check in' : 'Check out' }}
                                                            </button>
                                                        </form>
                                                        <button type="button" class="btn btn-sm btn-light-warning" data-bs-toggle="modal" data-bs-target="#transferModal"
                                                            data-action="{{ route('hostels.allocations.transfer', $allocation) }}"
                                                            data-student="{{ $studentName }}" data-current="{{ $placement }}">
                                                            <i class="fas fa-right-left me-1"></i> Transfer
                                                        </button>
                                                    </div>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="text-center text-muted py-10">
                                            <i class="fas fa-bed fs-2 d-block mb-3"></i>
                                            {{ $statusFilter ? 'No allocations with this status.' : 'No allocations yet.' }}
                                            @unless($statusFilter)
                                                <div class="mt-3"><button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#allocateModal">Allocate the first student</button></div>
                                            @endunless
                                        </td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">{{ $allocations->links() }}</div>
                    </div>
                </div>
            </div>

            {{-- Inventory --}}
            <div class="tab-pane fade" id="pane-inventory" role="tabpanel">
                <div class="row g-4 mb-6">
                    <div class="col-lg-4">
                        <div class="card h-100"><div class="card-body">
                            <h3 class="fw-bold mb-1"><span class="badge badge-circle badge-primary me-2">1</span>Add hostel</h3>
                            <p class="text-muted fs-7 mb-4">Start with the building itself.</p>
                            <form method="POST" action="{{ route('hostels.hostel') }}">@csrf
                                <input class="form-control mb-3" name="name" value="{{ old('name') }}" placeholder="Hostel name" required>
                                <input class="form-control mb-3" name="code" value="{{ old('code') }}" placeholder="Unique code, e.g. BLK-A" required>
                                <input class="form-control mb-3" name="location" value="{{ old('location') }}" placeholder="Location (optional)">
                                <select class="form-select mb-4" name="gender_policy" aria-label="Gender policy">
                                    @foreach(['mixed' => 'Mixed', 'male' => 'Male', 'female' => 'Female'] as $key => $label)
                                        <option value="{{ $key }}" @selected(old('gender_policy', 'mixed') === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <button class="btn btn-primary w-100">Create hostel</button>
                            </form>
                        </div></div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card h-100"><div class="card-body">
                            <h3 class="fw-bold mb-1"><span class="badge badge-circle badge-primary me-2">2</span>Add room</h3>
                            <p class="text-muted fs-7 mb-4">Rooms belong to a hostel.</p>
                            @if($hostels->isEmpty())
                                <div class="text-muted fs-7 border border-dashed rounded p-4">Create a hostel first, then you can add rooms to it.</div>
                            @else
                                <form method="POST" action="{{ route('hostels.room') }}">@csrf
                                    <select class="form-select mb-3" name="hostel_id" required aria-label="Hostel">
                                        <option value="">Choose hostel</option>
                                        @foreach($hostels as $hostel)<option value="{{ $hostel->id }}" @selected(old('hostel_id') == $hostel->id)>{{ $hostel->name }}</option>@endforeach
                                    </select>
                                    <input class="form-control mb-3" name="room_number" value="{{ old('room_number') }}" placeholder="Room number" required>
                                    <input class="form-control mb-4" name="floor" value="{{ old('floor') }}" placeholder="Floor (optional)">
                                    <button class="btn btn-primary w-100">Create room</button>
                                </form>
                            @endif
                        </div></div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card h-100"><div class="card-body">
                            <h3 class="fw-bold mb-1"><span class="badge badge-circle badge-primary me-2">3</span>Add bed</h3>
                            <p class="text-muted fs-7 mb-4">Beds are what students are allocated to.</p>
                            @if($rooms->isEmpty())
                                <div class="text-muted fs-7 border border-dashed rounded p-4">Create a room first, then you can add beds to it.</div>
                            @else
                                <form method="POST" action="{{ route('hostels.bed') }}">@csrf
                                    <select class="form-select mb-3" name="hostel_room_id" required aria-label="Room">
                                        <option value="">Choose room</option>
                                        @foreach($rooms->sortBy(fn ($room) => $room->hostel?->name.'|'.$room->room_number, SORT_NATURAL | SORT_FLAG_CASE) as $room)
                                            <option value="{{ $room->id }}" @selected(old('hostel_room_id') == $room->id)>{{ $room->hostel?->name }} / {{ $room->room_number }}</option>
                                        @endforeach
                                    </select>
                                    <input class="form-control mb-4" name="bed_code" value="{{ old('bed_code') }}" placeholder="Bed code, e.g. A or 1" required>
                                    <button class="btn btn-primary w-100">Create bed</button>
                                </form>
                            @endif
                        </div></div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header py-5"><div class="card-title flex-column align-items-start">
                        <h3 class="fw-bold mb-0">Hostels</h3>
                        <span class="text-muted fs-7 fw-normal">Capacity and occupancy at a glance.</span>
                    </div></div>
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table table-row-bordered align-middle gy-4 mb-0">
                                <thead><tr class="text-muted fw-bold fs-7 text-uppercase"><th>Hostel</th><th>Location</th><th>Gender policy</th><th class="text-center">Rooms</th><th class="text-center">Beds</th><th style="min-width:180px;">Occupancy</th></tr></thead>
                                <tbody>
                                    @forelse($hostels as $hostel)
                                        @php
                                            $stats = $hostelStats[$hostel->id];
                                            $hostelPercent = $stats['beds'] > 0 ? round($stats['occupied'] / $stats['beds'] * 100) : 0;
                                        @endphp
                                        <tr>
                                            <td><div class="fw-bold text-gray-800">{{ $hostel->name }}</div><div class="text-muted fs-7">{{ $hostel->code }}</div></td>
                                            <td>{{ $hostel->location ?: '—' }}</td>
                                            <td><span class="badge badge-light-{{ ['male' => 'primary', 'female' => 'danger', 'mixed' => 'info'][$hostel->gender_policy] ?? 'secondary' }}">{{ $genderLabels[$hostel->gender_policy] ?? ucfirst($hostel->gender_policy) }}</span></td>
                                            <td class="text-center">{{ $stats['rooms'] }}</td>
                                            <td class="text-center">{{ $stats['beds'] }}</td>
                                            <td>
                                                <div class="d-flex justify-content-between fs-8 text-muted mb-1"><span>{{ $stats['occupied'] }} / {{ $stats['beds'] }}</span><span>{{ $hostelPercent }}%</span></div>
                                                <div class="progress" style="height:6px;"><div class="progress-bar bg-{{ $hostelPercent >= 90 ? 'danger' : ($hostelPercent >= 70 ? 'warning' : 'success') }}" style="width: {{ $hostelPercent }}%"></div></div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="6" class="text-center text-muted py-10"><i class="fas fa-building fs-2 d-block mb-3"></i>No hostels yet. Add one above to get started.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Periods --}}
            <div class="tab-pane fade" id="pane-periods" role="tabpanel">
                <div class="row g-4">
                    <div class="col-lg-4">
                        <div class="card h-100"><div class="card-body">
                            <h3 class="fw-bold mb-1">New allocation period</h3>
                            <p class="text-muted fs-7 mb-4">Students can only be allocated while a period is open.</p>
                            <form method="POST" action="{{ route('hostels.period') }}">@csrf
                                <label class="form-label fs-7 fw-bold" for="period-name">Period name</label>
                                <input id="period-name" class="form-control mb-3" name="name" placeholder="e.g. 2026/2027 Semester 1" required>
                                <div class="row g-3 mb-3">
                                    <div class="col"><label class="form-label fs-7 fw-bold" for="period-start">Start</label><input id="period-start" type="date" class="form-control" name="starts_on" value="{{ old('starts_on') }}" required></div>
                                    <div class="col"><label class="form-label fs-7 fw-bold" for="period-end">End</label><input id="period-end" type="date" class="form-control" name="ends_on" value="{{ old('ends_on') }}" required></div>
                                </div>
                                <label class="form-label fs-7 fw-bold" for="period-status">Status</label>
                                <select id="period-status" class="form-select mb-4" name="status">
                                    @foreach(['draft' => 'Draft', 'open' => 'Open for allocation', 'closed' => 'Closed'] as $key => $label)
                                        <option value="{{ $key }}" @selected(old('status', 'draft') === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <button class="btn btn-primary w-100">Create period</button>
                            </form>
                        </div></div>
                    </div>
                    <div class="col-lg-8">
                        <div class="card h-100">
                            <div class="card-header py-5"><div class="card-title"><h3 class="fw-bold mb-0">Allocation periods</h3></div></div>
                            <div class="card-body pt-0">
                                <div class="table-responsive">
                                    <table class="table table-row-bordered align-middle gy-4 mb-0">
                                        <thead><tr class="text-muted fw-bold fs-7 text-uppercase"><th>Period</th><th>Dates</th><th>Status</th></tr></thead>
                                        <tbody>
                                            @forelse($periods as $period)
                                                <tr>
                                                    <td class="fw-bold text-gray-800">{{ $period->name }}</td>
                                                    <td>{{ $period->starts_on->format('d M Y') }} &ndash; {{ $period->ends_on->format('d M Y') }}</td>
                                                    <td><span class="badge badge-light-{{ $periodStatusBadge[$period->status] ?? 'secondary' }}">{{ ucfirst($period->status) }}</span></td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="3" class="text-center text-muted py-10"><i class="fas fa-calendar-days fs-2 d-block mb-3"></i>No allocation periods yet.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Settings --}}
            <div class="tab-pane fade" id="pane-settings" role="tabpanel">
                <form method="POST" action="{{ route('hostels.settings') }}" class="card">
                    @csrf
                    <div class="card-header py-5"><div class="card-title flex-column align-items-start">
                        <h3 class="fw-bold mb-0">Institution settings</h3>
                        <span class="text-muted fs-7 fw-normal">How accommodation is offered and allocated across the institution.</span>
                    </div></div>
                    <div class="card-body">
                        <div class="fw-bold text-gray-800 mb-3">Workflow</div>
                        <div class="row g-4 mb-8">
                            <div class="col-md-4">
                                <label class="form-label fs-7 fw-bold" for="allocation_method">Allocation workflow</label>
                                <select class="form-select" id="allocation_method" name="allocation_method">
                                    @foreach($workflowOptions as $key => $label)<option value="{{ $key }}" @selected($setting('allocation_method', 'staff') === $key)>{{ $label }}</option>@endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fs-7 fw-bold" for="allocation_unit">Allocation unit</label>
                                <select class="form-select" id="allocation_unit" name="allocation_unit">
                                    @foreach($unitOptions as $key => $label)<option value="{{ $key }}" @selected($setting('allocation_unit', 'bed') === $key)>{{ $label }}</option>@endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fs-7 fw-bold" for="period_type">Period</label>
                                <select class="form-select" id="period_type" name="period_type">
                                    @foreach($periodOptions as $key => $label)<option value="{{ $key }}" @selected($setting('period_type', 'academic_year') === $key)>{{ $label }}</option>@endforeach
                                </select>
                            </div>
                        </div>

                        <div class="fw-bold text-gray-800 mb-3">Rules</div>
                        <div class="row g-3 mb-8">
                            @foreach($ruleToggles as $key => [$label, $help, $default])
                                <div class="col-md-6">
                                    <input type="hidden" name="{{ $key }}" value="0">
                                    <label class="d-flex justify-content-between align-items-center border rounded p-4 h-100 cursor-pointer" for="setting-{{ $key }}">
                                        <span class="pe-4"><span class="d-block fw-bold text-gray-800">{{ $label }}</span><span class="d-block text-muted fs-7">{{ $help }}</span></span>
                                        <span class="form-check form-switch form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" role="switch" id="setting-{{ $key }}" name="{{ $key }}" value="1" @checked((bool) $setting($key, $default))>
                                        </span>
                                    </label>
                                </div>
                            @endforeach
                        </div>

                        <div id="payment-settings" @class(['d-none' => ! $requirePayment])>
                            <div class="fw-bold text-gray-800 mb-3">Payment clearance</div>
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label fs-7 fw-bold" for="fee_type_id">Accommodation fee type</label>
                                    <select name="fee_type_id" id="fee_type_id" class="form-select">
                                        <option value="">Not configured</option>
                                        @foreach($feeTypes as $feeType)<option value="{{ $feeType->id }}" @selected($setting('fee_type_id') == $feeType->id)>{{ $feeType->name }}</option>@endforeach
                                    </select>
                                    <div class="form-text">Required while payment clearance is on.</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fs-7 fw-bold" for="minimum_payment_percent">Minimum payment (%)</label>
                                    <input class="form-control" id="minimum_payment_percent" type="number" min="1" max="100" name="minimum_payment_percent" value="{{ $setting('minimum_payment_percent', 100) }}">
                                    <div class="form-text">Share of the fee a student must have paid before allocation.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-end"><button class="btn btn-primary"><i class="fas fa-floppy-disk me-1"></i> Save settings</button></div>
                </form>
            </div>
        </div>
    </div>

    {{-- Allocate student --}}
    <div class="modal fade" id="allocateModal" tabindex="-1" aria-labelledby="allocateModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" action="{{ route('hostels.allocate') }}" class="modal-content">
                @csrf
                <div class="modal-header"><h3 class="modal-title fw-bold" id="allocateModalLabel">Allocate student</h3><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
                <div class="modal-body">
                    @if($openPeriods->isEmpty())
                        <div class="alert alert-warning mb-4">There is no open allocation period. Open one in the <a href="#" data-goto-tab="tab-periods" data-bs-dismiss="modal">Periods</a> tab first.</div>
                    @endif
                    @if($beds->isEmpty())
                        <div class="alert alert-warning mb-4">There are no beds yet. Add some in the <a href="#" data-goto-tab="tab-inventory" data-bs-dismiss="modal">Inventory</a> tab first.</div>
                    @endif
                    <label class="form-label fs-7 fw-bold" for="hostel-student-search">Student</label>
                    <input type="search" class="form-control mb-2" id="hostel-student-search" placeholder="Search by name or student ID" autocomplete="off" required>
                    <select class="form-select mb-1" id="hostel-student-result" name="student_id" required aria-label="Matching students">
                        <option value="">Type at least 2 characters to find students</option>
                    </select>
                    <div class="form-text mb-4" id="hostel-student-hint">Pick a student from the matches.</div>

                    <label class="form-label fs-7 fw-bold" for="allocate-bed">Bed</label>
                    <select class="form-select mb-4" id="allocate-bed" name="hostel_bed_id" required>
                        <option value="">Select bed</option>
                        @foreach($beds as $bed)<option value="{{ $bed->id }}">{{ $bed->room?->hostel?->name }} / Room {{ $bed->room?->room_number }} / Bed {{ $bed->bed_code }}</option>@endforeach
                    </select>

                    <label class="form-label fs-7 fw-bold" for="allocate-period">Period</label>
                    <select class="form-select" id="allocate-period" name="hostel_allocation_period_id" required>
                        <option value="">Select open period</option>
                        @foreach($openPeriods as $period)<option value="{{ $period->id }}">{{ $period->name }}</option>@endforeach
                    </select>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Allocate</button></div>
            </form>
        </div>
    </div>

    {{-- Transfer student --}}
    <div class="modal fade" id="transferModal" tabindex="-1" aria-labelledby="transferModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" action="" class="modal-content" id="transfer-form">
                @csrf
                <div class="modal-header"><h3 class="modal-title fw-bold" id="transferModalLabel">Transfer student</h3><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
                <div class="modal-body">
                    <div class="bg-light rounded p-4 mb-4">
                        <div class="fw-bold text-gray-800" id="transfer-student">—</div>
                        <div class="text-muted fs-7">Currently in <span id="transfer-current">—</span></div>
                    </div>
                    <label class="form-label fs-7 fw-bold" for="transfer-bed">Move to bed</label>
                    <select class="form-select mb-4" id="transfer-bed" name="hostel_bed_id" required>
                        <option value="">Select bed</option>
                        @foreach($beds as $bed)<option value="{{ $bed->id }}">{{ $bed->room?->hostel?->name }} / Room {{ $bed->room?->room_number }} / Bed {{ $bed->bed_code }}</option>@endforeach
                    </select>
                    <label class="form-label fs-7 fw-bold" for="transfer-reason">Reason for transfer <span class="text-muted fw-normal">(optional)</span></label>
                    <input class="form-control" id="transfer-reason" name="reason" maxlength="2000" placeholder="e.g. Maintenance in current room">
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button class="btn btn-warning">Transfer</button></div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const tabKey = 'hostels.activeTab';
            const tabButtons = Array.from(document.querySelectorAll('#hostel-tabs [data-bs-toggle="tab"]'));
            const openTab = (id) => {
                const button = document.getElementById(id);
                if (button && !button.classList.contains('active')) { button.click(); }
            };

            let stored = null;
            try { stored = sessionStorage.getItem(tabKey); } catch (e) {}
            const initial = (location.hash || '').replace('#', '') || stored;
            if (initial && tabButtons.some((button) => button.id === initial)) { openTab(initial); }

            tabButtons.forEach((button) => button.addEventListener('shown.bs.tab', () => {
                try { sessionStorage.setItem(tabKey, button.id); } catch (e) {}
            }));

            document.querySelectorAll('[data-goto-tab]').forEach((link) => link.addEventListener('click', (event) => {
                event.preventDefault();
                openTab(link.dataset.gotoTab);
            }));

            const paymentToggle = document.getElementById('setting-require_payment');
            const paymentSettings = document.getElementById('payment-settings');
            if (paymentToggle && paymentSettings) {
                paymentToggle.addEventListener('change', () => paymentSettings.classList.toggle('d-none', !paymentToggle.checked));
            }

            const transferModal = document.getElementById('transferModal');
            if (transferModal) {
                transferModal.addEventListener('show.bs.modal', (event) => {
                    const trigger = event.relatedTarget;
                    if (!trigger) { return; }
                    document.getElementById('transfer-form').action = trigger.dataset.action;
                    document.getElementById('transfer-student').textContent = trigger.dataset.student;
                    document.getElementById('transfer-current').textContent = trigger.dataset.current;
                    document.getElementById('transfer-bed').value = '';
                    document.getElementById('transfer-reason').value = '';
                });
            }

            const input = document.getElementById('hostel-student-search');
            const results = document.getElementById('hostel-student-result');
            const hint = document.getElementById('hostel-student-hint');
            if (!input || !results) { return; }
            let timer;
            input.addEventListener('input', () => {
                clearTimeout(timer);
                const term = input.value.trim();
                if (term.length < 2) {
                    results.replaceChildren(new Option('Type at least 2 characters to find students', ''));
                    hint.textContent = 'Pick a student from the matches.';
                    return;
                }
                results.replaceChildren(new Option('Searching...', ''));
                timer = setTimeout(async () => {
                    try {
                        const response = await fetch('{{ route('hostels.students.search') }}?search=' + encodeURIComponent(term), { headers: { 'Accept': 'application/json' } });
                        if (!response.ok) { throw new Error('Search failed'); }
                        const students = await response.json();
                        results.replaceChildren(new Option(students.length ? 'Select student' : 'No matching students', ''));
                        students.forEach((student) => results.add(new Option(student.label, student.id)));
                        hint.textContent = students.length ? students.length + ' match' + (students.length === 1 ? '' : 'es') + ' found.' : 'Try a different name or ID.';
                    } catch (error) {
                        results.replaceChildren(new Option('Unable to load students', ''));
                        hint.textContent = 'Search failed. Please try again.';
                    }
                }, 250);
            });
        });
    </script>
</x-dashboard.default>
