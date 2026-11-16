@php
    $queueStatusBadge = ['pending' => 'warning', 'waitlisted' => 'info'];
    $totalInQueue = $counts->sum();
@endphp

<x-dashboard.default title="Hostel Application Approvals">
    <div class="container-fluid py-6">

        <x-hostels.page-header title="Hostel Applications" subtitle="Review pending and waitlisted accommodation applications." icon="fa-clipboard-check" />

        <x-hostels.alerts />

        <div class="row g-4 mb-6">
            <div class="col-6 col-lg-4"><x-hostels.stat label="Pending review" :value="$counts['pending'] ?? 0" icon="fa-hourglass-half" color="warning" /></div>
            <div class="col-6 col-lg-4"><x-hostels.stat label="Waitlisted" :value="$counts['waitlisted'] ?? 0" icon="fa-list-ol" color="info" /></div>
            <div class="col-12 col-lg-4"><x-hostels.stat label="Open allocation periods" :value="$openPeriods->count()" icon="fa-calendar-check" color="success" /></div>
        </div>

        <div class="card mb-6">
            <div class="card-body">
                <div class="row g-4 align-items-center">
                    <div class="col-lg-6">
                        <h3 class="fw-bold mb-1"><i class="fas fa-wand-magic-sparkles me-2 text-primary"></i>Automatic allocation</h3>
                        <p class="text-muted fs-7 mb-0">Assigns available beds to applicants, oldest applications first. Applicants who have not cleared payment are skipped, and applicants with no free bed are waitlisted when the waiting list is enabled.</p>
                    </div>
                    <div class="col-lg-6">
                        @if($openPeriods->isEmpty())
                            <div class="text-muted fs-7 border border-dashed rounded p-4">There is no open allocation period. Open one from the Periods tab in Hostel Management to run automatic allocation.</div>
                        @else
                            <form method="POST" action="{{ route('hostels.applications.auto-allocate') }}" class="d-flex flex-wrap gap-3" onsubmit="return confirm('Run automatic allocation for the selected period? Beds will be assigned right away.')">
                                @csrf
                                <select class="form-select flex-grow-1 w-auto" name="hostel_allocation_period_id" required aria-label="Allocation period">
                                    <option value="">Choose open period</option>
                                    @foreach($openPeriods as $period)<option value="{{ $period->id }}">{{ $period->name }}</option>@endforeach
                                </select>
                                <button class="btn btn-primary"><i class="fas fa-play me-1"></i> Run allocation</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header flex-wrap gap-3 py-5">
                <div class="card-title flex-column align-items-start">
                    <h3 class="fw-bold mb-0">Applications awaiting a decision</h3>
                    <span class="text-muted fs-7 fw-normal">Oldest first. Approving an application allocates the chosen bed straight away.</span>
                </div>
                <form method="GET" action="{{ route('hostels.applications') }}">
                    <select name="status" class="form-select form-select-sm w-auto" onchange="this.form.submit()" aria-label="Filter by status">
                        <option value="">All ({{ $totalInQueue }})</option>
                        <option value="pending" @selected($statusFilter === 'pending')>Pending ({{ $counts['pending'] ?? 0 }})</option>
                        <option value="waitlisted" @selected($statusFilter === 'waitlisted')>Waitlisted ({{ $counts['waitlisted'] ?? 0 }})</option>
                    </select>
                </form>
            </div>
            <div class="card-body pt-0">
                <div class="table-responsive">
                    <table class="table table-row-bordered align-middle gy-4 mb-0">
                        <thead>
                            <tr class="text-muted fw-bold fs-7 text-uppercase">
                                <th>Applicant</th><th>Period</th><th>Preferred hostel</th><th>Submitted</th><th>Status</th><th class="text-end">Decision</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($applications as $application)
                                @php
                                    $applicantName = trim(($application->student?->first_name ?? '').' '.($application->student?->last_name ?? '')) ?: 'Unknown student';
                                @endphp
                                <tr>
                                    <td>
                                        <div class="fw-bold text-gray-800">{{ $applicantName }}</div>
                                        <div class="text-muted fs-7">{{ $application->student?->student_id ?? 'ID #'.$application->student_id }}</div>
                                        @if(filled($application->notes))
                                            <div class="text-muted fs-8 mt-1 fst-italic text-truncate" style="max-width:260px;" title="{{ $application->notes }}"><i class="fas fa-comment-dots me-1"></i>{{ $application->notes }}</div>
                                        @endif
                                    </td>
                                    <td>{{ $application->period?->name ?? '—' }}</td>
                                    <td>{{ $application->preferredHostel?->name ?? 'No preference' }}</td>
                                    <td>
                                        <div>{{ $application->created_at->format('d M Y') }}</div>
                                        <div class="text-muted fs-8">{{ $application->created_at->diffForHumans() }}</div>
                                    </td>
                                    <td><span class="badge badge-light-{{ $queueStatusBadge[$application->status] ?? 'secondary' }}">{{ ucfirst($application->status) }}</span></td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-2">
                                            <button type="button" class="btn btn-sm btn-light-success" data-bs-toggle="modal" data-bs-target="#approveModal"
                                                data-action="{{ route('hostels.applications.decide', $application) }}"
                                                data-applicant="{{ $applicantName }}"
                                                data-preferred-id="{{ $application->preferred_hostel_id }}"
                                                data-preferred-name="{{ $application->preferredHostel?->name }}">
                                                <i class="fas fa-check me-1"></i> Approve
                                            </button>
                                            <form method="POST" action="{{ route('hostels.applications.decide', $application) }}" onsubmit="return confirm('Reject the application from {{ addslashes($applicantName) }}?')">
                                                @csrf
                                                <button type="submit" name="decision" value="rejected" class="btn btn-sm btn-light-danger"><i class="fas fa-xmark me-1"></i> Reject</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted py-10">
                                    <i class="fas fa-inbox fs-2 d-block mb-3"></i>
                                    {{ $statusFilter ? 'No '.$statusFilter.' applications.' : 'No pending applications. You are all caught up.' }}
                                </td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ $applications->links() }}</div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" action="" class="modal-content" id="approve-form">
                @csrf
                <input type="hidden" name="decision" value="approved">
                <div class="modal-header"><h3 class="modal-title fw-bold" id="approveModalLabel">Approve &amp; allocate</h3><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
                <div class="modal-body">
                    <div class="bg-light rounded p-4 mb-4">
                        <div class="fw-bold text-gray-800" id="approve-applicant">—</div>
                        <div class="text-muted fs-7" id="approve-preference">—</div>
                    </div>
                    @if($beds->isEmpty())
                        <div class="alert alert-warning mb-0">There are no active beds to allocate. Add inventory in Hostel Management first.</div>
                    @else
                        <label class="form-label fs-7 fw-bold" for="approve-bed">Bed <span class="text-danger">*</span></label>
                        <select class="form-select mb-2" id="approve-bed" name="hostel_bed_id" required>
                            <option value="">Select a bed</option>
                            @foreach($beds->groupBy(fn ($bed) => $bed->room?->hostel?->name) as $hostelName => $hostelBeds)
                                <optgroup label="{{ $hostelName }}">
                                    @foreach($hostelBeds as $bed)
                                        <option value="{{ $bed->id }}" data-hostel-id="{{ $bed->room?->hostel_id }}">Room {{ $bed->room?->room_number }} / Bed {{ $bed->bed_code }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                        <div class="form-check form-check-custom form-check-solid d-none" id="approve-all-wrapper">
                            <input class="form-check-input" type="checkbox" id="approve-show-all">
                            <label class="form-check-label fs-7" for="approve-show-all">Show beds from all hostels</label>
                        </div>
                        <div class="form-text">A bed is required to approve. Availability is checked again when you confirm.</div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success" @disabled($beds->isEmpty())>Approve &amp; allocate</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById('approveModal');
            if (!modal) { return; }
            const bedSelect = document.getElementById('approve-bed');
            const showAll = document.getElementById('approve-show-all');
            const allWrapper = document.getElementById('approve-all-wrapper');
            let preferredId = '';

            const applyBedFilter = () => {
                if (!bedSelect) { return; }
                const restrict = preferredId !== '' && !showAll.checked;
                bedSelect.querySelectorAll('option[data-hostel-id]').forEach((option) => {
                    const hidden = restrict && option.dataset.hostelId !== preferredId;
                    option.hidden = hidden;
                    option.disabled = hidden;
                });
                bedSelect.querySelectorAll('optgroup').forEach((group) => {
                    group.hidden = Array.from(group.querySelectorAll('option')).every((option) => option.hidden);
                });
                bedSelect.value = '';
            };

            modal.addEventListener('show.bs.modal', (event) => {
                const trigger = event.relatedTarget;
                if (!trigger) { return; }
                document.getElementById('approve-form').action = trigger.dataset.action;
                document.getElementById('approve-applicant').textContent = trigger.dataset.applicant;
                preferredId = trigger.dataset.preferredId || '';
                document.getElementById('approve-preference').textContent = preferredId
                    ? 'Prefers ' + trigger.dataset.preferredName
                    : 'No hostel preference';
                if (showAll) { showAll.checked = false; }
                if (allWrapper) { allWrapper.classList.toggle('d-none', preferredId === ''); }
                applyBedFilter();
            });
            if (showAll) { showAll.addEventListener('change', applyBedFilter); }
        });
    </script>
</x-dashboard.default>
