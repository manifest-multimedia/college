@php
    $planRows = collect($plan);
    $readyCount = $planRows->whereNull('reason')->count();
    $skippedCount = $planRows->whereNotNull('reason')->count();
@endphp

<x-dashboard.default title="Preview Hostel Allocation">
    <div class="container-fluid py-6">

        <x-hostels.page-header title="Review proposed allocations" subtitle="Assignments are provisional. Availability and payment eligibility are checked again when you confirm." icon="fa-list-check" :back-url="route('hostels.bulk.index')" back-label="Bulk Allocation" />

        <x-hostels.alerts />

        <div class="row g-4 mb-6">
            <div class="col-6 col-lg-4"><x-hostels.stat label="Ready to allocate" :value="$readyCount" icon="fa-circle-check" color="success" /></div>
            <div class="col-6 col-lg-4"><x-hostels.stat label="Will be skipped" :value="$skippedCount" icon="fa-forward" color="warning" /></div>
            <div class="col-12 col-lg-4"><x-hostels.stat label="Students selected" :value="$planRows->count()" icon="fa-users" color="primary" /></div>
        </div>

        <div class="card">
            <div class="card-header py-5"><div class="card-title flex-column align-items-start">
                <h3 class="fw-bold mb-0">Proposed assignments</h3>
                <span class="text-muted fs-7 fw-normal">Nothing is saved until you press Confirm.</span>
            </div></div>
            <div class="card-body pt-0">
                <div class="table-responsive">
                    <table class="table table-row-bordered align-middle gy-4 mb-0">
                        <thead><tr class="text-muted fw-bold fs-7 text-uppercase"><th>Student</th><th>Proposed room / bed</th><th>Outcome</th></tr></thead>
                        <tbody>
                            @foreach($planRows as $row)
                                <tr>
                                    <td>
                                        <div class="fw-bold text-gray-800">{{ $row['student_name'] }}</div>
                                        <div class="text-muted fs-7">{{ $row['student_number'] }}</div>
                                    </td>
                                    <td>{{ $row['bed_label'] ?? '—' }}</td>
                                    <td>
                                        @if(empty($row['reason']))
                                            <span class="badge badge-light-success"><i class="fas fa-check me-1"></i>Ready to allocate</span>
                                        @else
                                            <span class="badge badge-light-warning"><i class="fas fa-triangle-exclamation me-1"></i>Skipped</span>
                                            <div class="text-muted fs-7 mt-1">{{ $row['reason'] }}</div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer d-flex flex-wrap justify-content-between align-items-center gap-3">
                <span class="text-muted fs-7">
                    @if($readyCount > 0)
                        {{ $readyCount }} {{ \Illuminate\Support\Str::plural('student', $readyCount) }} will be allocated{{ $skippedCount ? ' and '.$skippedCount.' skipped' : '' }}.
                    @else
                        No students can be allocated with the current selection. Go back and adjust it.
                    @endif
                </span>
                <form method="POST" action="{{ route('hostels.bulk.confirm') }}" class="d-flex gap-2">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    <a class="btn btn-light" href="{{ route('hostels.bulk.index') }}">Cancel</a>
                    <button class="btn btn-primary" @disabled($readyCount === 0)><i class="fas fa-check me-1"></i> Confirm allocations</button>
                </form>
            </div>
        </div>
    </div>
</x-dashboard.default>
