@php
    $statusBadge = ['allocated' => 'primary', 'checked_in' => 'success', 'checked_out' => 'secondary', 'released' => 'warning', 'transferred' => 'info'];
    $awaitingCheckIn = $summary['allocated'] ?? 0;
    $checkedIn = $summary['checked_in'] ?? 0;
@endphp

<x-dashboard.default title="Hostel Warden">
    <div class="container-fluid py-6">

        <x-hostels.page-header title="My Hostel Occupancy" :subtitle="'Showing allocations for '.$hostelNames->join(', ', ' and ').'. Only hostels assigned to you are displayed.'" icon="fa-user-shield" :back="false" />

        <x-hostels.alerts />

        <div class="row g-4 mb-6">
            <div class="col-6 col-lg-4"><x-hostels.stat label="Awaiting check-in" :value="$awaitingCheckIn" icon="fa-hourglass-half" color="primary" /></div>
            <div class="col-6 col-lg-4"><x-hostels.stat label="Checked in" :value="$checkedIn" icon="fa-right-to-bracket" color="success" /></div>
            <div class="col-12 col-lg-4"><x-hostels.stat label="Total allocations" :value="$summary->sum()" icon="fa-bed" color="info" /></div>
        </div>

        <div class="card">
            <div class="card-header flex-wrap gap-3 py-5">
                <div class="card-title flex-column align-items-start">
                    <h3 class="fw-bold mb-0">Allocations</h3>
                    <span class="text-muted fs-7 fw-normal">{{ $allocations->total() }} {{ \Illuminate\Support\Str::plural('record', $allocations->total()) }}{{ $statusFilter ? ' with status "'.str_replace('_', ' ', $statusFilter).'"' : '' }}</span>
                </div>
                <form method="GET" action="{{ route('warden.hostels.index') }}">
                    <select name="status" class="form-select form-select-sm w-auto" onchange="this.form.submit()" aria-label="Filter by status">
                        <option value="">All statuses</option>
                        @foreach(['allocated', 'checked_in', 'checked_out', 'released', 'transferred'] as $status)
                            <option value="{{ $status }}" @selected($statusFilter === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
            <div class="card-body pt-0">
                <div class="table-responsive">
                    <table class="table table-row-bordered align-middle gy-4 mb-0">
                        <thead><tr class="text-muted fw-bold fs-7 text-uppercase"><th>Student</th><th>Hostel</th><th>Room / Bed</th><th>Status</th><th class="text-end">Action</th></tr></thead>
                        <tbody>
                            @forelse($allocations as $allocation)
                                @php $studentName = trim(($allocation->first_name ?? '').' '.($allocation->last_name ?? '')) ?: 'Unknown student'; @endphp
                                <tr>
                                    <td>
                                        <div class="fw-bold text-gray-800">{{ $studentName }}</div>
                                        <div class="text-muted fs-7">{{ $allocation->student_number }}</div>
                                    </td>
                                    <td>{{ $allocation->hostel_name }}</td>
                                    <td>Room {{ $allocation->room_number }} &middot; Bed {{ $allocation->bed_code }}</td>
                                    <td>
                                        <span class="badge badge-light-{{ $statusBadge[$allocation->status] ?? 'secondary' }}">{{ ucfirst(str_replace('_', ' ', $allocation->status)) }}</span>
                                        @if($allocation->status === 'checked_in' && $allocation->checked_in_at)
                                            <div class="text-muted fs-8 mt-1">since {{ $allocation->checked_in_at->format('d M Y') }}</div>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if(in_array($allocation->status, ['allocated', 'checked_in'], true))
                                            <form method="POST" action="{{ route('warden.hostels.transition', $allocation->id) }}" @if($allocation->status === 'checked_in') onsubmit="return confirm('Check {{ addslashes($studentName) }} out of this bed?')" @endif>
                                                @csrf
                                                <input type="hidden" name="action" value="{{ $allocation->status === 'allocated' ? 'check_in' : 'check_out' }}">
                                                <button class="btn btn-sm btn-{{ $allocation->status === 'allocated' ? 'light-success' : 'light-primary' }}">
                                                    <i class="fas {{ $allocation->status === 'allocated' ? 'fa-right-to-bracket' : 'fa-right-from-bracket' }} me-1"></i>{{ $allocation->status === 'allocated' ? 'Check in' : 'Check out' }}
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-10"><i class="fas fa-bed fs-2 d-block mb-3"></i>{{ $statusFilter ? 'No allocations with this status.' : 'No allocations in your hostels yet.' }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ $allocations->links() }}</div>
            </div>
        </div>
    </div>
</x-dashboard.default>
