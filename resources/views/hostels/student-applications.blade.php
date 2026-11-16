@php
    $applicationsOpen = $settings?->enabled && $settings->allow_applications && in_array($settings->allocation_method, ['application', 'hybrid'], true);
    $statusBadge = ['pending' => 'warning', 'waitlisted' => 'info', 'approved' => 'success', 'rejected' => 'danger'];
    $statusHelp = [
        'pending' => 'Under review',
        'waitlisted' => 'No bed is free right now; you stay in the queue',
        'approved' => 'A bed has been allocated to you',
        'rejected' => 'Not successful this time',
    ];
@endphp

<x-dashboard.default title="Hostel Applications">
    <div class="container-fluid py-6">

        <x-hostels.page-header title="My Hostel Applications" subtitle="Apply for accommodation and track the progress of your requests." icon="fa-house-user" :back="false" />

        <x-hostels.alerts />

        <div class="row g-6">
            <div class="col-xl-5">
                <div class="card h-100">
                    <div class="card-header py-5"><div class="card-title flex-column align-items-start">
                        <h3 class="fw-bold mb-0">Apply for accommodation</h3>
                        <span class="text-muted fs-7 fw-normal">Choose a period and tell us your hostel preference.</span>
                    </div></div>
                    <div class="card-body">
                        @if(! $applicationsOpen)
                            <div class="text-center text-muted py-8">
                                <i class="fas fa-lock fs-2 d-block mb-3"></i>
                                Hostel applications are currently unavailable.
                                <div class="fs-7 mt-1">Please check back later or contact the accommodation office.</div>
                            </div>
                        @elseif($periods->isEmpty())
                            <div class="text-center text-muted py-8">
                                <i class="fas fa-calendar-xmark fs-2 d-block mb-3"></i>
                                No application period is open right now.
                                <div class="fs-7 mt-1">Applications will appear here once a period opens.</div>
                            </div>
                        @else
                            <form method="POST" action="{{ route('student.hostel.apply') }}">
                                @csrf
                                <label class="form-label fs-7 fw-bold" for="application-period">Allocation period <span class="text-danger">*</span></label>
                                <select class="form-select mb-4" id="application-period" name="hostel_allocation_period_id" required>
                                    <option value="">Select period</option>
                                    @foreach($periods as $period)
                                        <option value="{{ $period->id }}" @selected(old('hostel_allocation_period_id') == $period->id)>{{ $period->name }}</option>
                                    @endforeach
                                </select>

                                <label class="form-label fs-7 fw-bold" for="application-hostel">Preferred hostel <span class="text-muted fw-normal">(optional)</span></label>
                                <select class="form-select mb-4" id="application-hostel" name="preferred_hostel_id">
                                    <option value="">No preference</option>
                                    @foreach($hostels as $hostel)
                                        <option value="{{ $hostel->id }}" @selected(old('preferred_hostel_id') == $hostel->id)>{{ $hostel->name }}</option>
                                    @endforeach
                                </select>

                                <label class="form-label fs-7 fw-bold" for="application-notes">Notes <span class="text-muted fw-normal">(optional)</span></label>
                                <textarea class="form-control mb-1" id="application-notes" name="notes" rows="4" maxlength="2000" placeholder="Anything the accommodation office should know">{{ old('notes') }}</textarea>
                                <div class="text-muted fs-8 text-end mb-4"><span id="notes-count">0</span> / 2000</div>

                                <button class="btn btn-primary w-100"><i class="fas fa-paper-plane me-1"></i> Submit application</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-xl-7">
                <div class="card h-100">
                    <div class="card-header py-5"><div class="card-title flex-column align-items-start">
                        <h3 class="fw-bold mb-0">Application history</h3>
                        <span class="text-muted fs-7 fw-normal">{{ $applications->count() }} {{ \Illuminate\Support\Str::plural('application', $applications->count()) }} submitted</span>
                    </div></div>
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table table-row-bordered align-middle gy-4 mb-0">
                                <thead><tr class="text-muted fw-bold fs-7 text-uppercase"><th>Period</th><th>Preferred hostel</th><th>Status</th><th>Submitted</th></tr></thead>
                                <tbody>
                                    @forelse($applications as $application)
                                        <tr>
                                            <td class="fw-bold text-gray-800">{{ $application->period?->name ?? '—' }}</td>
                                            <td>{{ $application->preferredHostel?->name ?? 'No preference' }}</td>
                                            <td>
                                                <span class="badge badge-light-{{ $statusBadge[$application->status] ?? 'secondary' }}">{{ ucfirst($application->status) }}</span>
                                                @if(isset($statusHelp[$application->status]))<div class="text-muted fs-8 mt-1">{{ $statusHelp[$application->status] }}</div>@endif
                                            </td>
                                            <td>
                                                <div>{{ $application->created_at->format('d M Y') }}</div>
                                                <div class="text-muted fs-8">{{ $application->created_at->format('H:i') }}</div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center text-muted py-10"><i class="fas fa-file-circle-question fs-2 d-block mb-3"></i>You have not applied for accommodation yet.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const notes = document.getElementById('application-notes');
            const counter = document.getElementById('notes-count');
            if (notes && counter) {
                const update = () => { counter.textContent = notes.value.length; };
                notes.addEventListener('input', update);
                update();
            }
        });
    </script>
</x-dashboard.default>
