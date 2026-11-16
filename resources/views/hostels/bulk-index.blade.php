@php
    $hasFilters = collect(['search', 'program_id', 'cohort_id', 'academic_year_id', 'gender'])->contains(fn ($key) => filled(request($key)))
        || in_array(request('allocation_status'), ['allocated', 'unallocated'], true);
    $bulkResults = session('bulk_results');
@endphp

<x-dashboard.default title="Bulk Hostel Allocation">
    <div class="container-fluid py-6">

        <x-hostels.page-header title="Bulk Hostel Allocation" subtitle="Filter students and select across pages before previewing bed assignments." icon="fa-users" />

        @if($bulkResults)
            @php $skipped = $bulkResults['skipped'] ?? []; @endphp
            <div class="alert alert-{{ count($skipped) ? 'warning' : 'success' }} alert-dismissible fade show" role="alert">
                <div class="fw-bold"><i class="fas fa-circle-check me-2"></i>{{ $bulkResults['allocated'] ?? 0 }} {{ \Illuminate\Support\Str::plural('student', $bulkResults['allocated'] ?? 0) }} allocated{{ count($skipped) ? ', '.count($skipped).' skipped' : '' }}.</div>
                @if(count($skipped))
                    <ul class="mb-0 mt-2 ps-4">@foreach($skipped as $reason)<li>{{ $reason }}</li>@endforeach</ul>
                @endif
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <x-hostels.alerts />

        <form method="GET" action="{{ route('hostels.bulk.index') }}" class="card mb-6">
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-lg-4">
                        <label class="form-label fs-7 fw-bold" for="filter-search">Search</label>
                        <input class="form-control" id="filter-search" name="search" value="{{ request('search') }}" placeholder="Name, student ID, email or phone">
                    </div>
                    <div class="col-md-6 col-lg-2">
                        <label class="form-label fs-7 fw-bold" for="filter-program">Program</label>
                        <select class="form-select" id="filter-program" name="program_id">
                            <option value="">All programs</option>
                            @foreach($programs as $program)<option value="{{ $program->id }}" @selected(request('program_id') == $program->id)>{{ $program->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-md-6 col-lg-2">
                        <label class="form-label fs-7 fw-bold" for="filter-cohort">Cohort</label>
                        <select class="form-select" id="filter-cohort" name="cohort_id">
                            <option value="">All cohorts</option>
                            @foreach($cohorts as $cohort)<option value="{{ $cohort->id }}" @selected(request('cohort_id') == $cohort->id)>{{ $cohort->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-md-6 col-lg-2">
                        <label class="form-label fs-7 fw-bold" for="filter-year">Academic year</label>
                        <select class="form-select" id="filter-year" name="academic_year_id">
                            <option value="">All years</option>
                            @foreach($years as $year)<option value="{{ $year->id }}" @selected(request('academic_year_id') == $year->id)>{{ $year->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-md-6 col-lg-2">
                        <label class="form-label fs-7 fw-bold" for="filter-gender">Gender</label>
                        <select class="form-select" id="filter-gender" name="gender">
                            <option value="">All</option>
                            @foreach(['Male', 'Female'] as $gender)<option value="{{ $gender }}" @selected(request('gender') === $gender)>{{ $gender }}</option>@endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div class="d-flex align-items-center gap-3">
                    <label class="fs-7 fw-bold mb-0" for="filter-allocation">Allocation status</label>
                    <select class="form-select form-select-sm w-auto" id="filter-allocation" name="allocation_status">
                        <option value="all">All</option>
                        <option value="unallocated" @selected(request('allocation_status') === 'unallocated')>Unallocated</option>
                        <option value="allocated" @selected(request('allocation_status') === 'allocated')>Allocated</option>
                    </select>
                </div>
                <div class="d-flex gap-2">
                    @if($hasFilters)<a href="{{ route('hostels.bulk.index') }}" class="btn btn-light">Clear filters</a>@endif
                    <button class="btn btn-primary"><i class="fas fa-filter me-1"></i> Apply filters</button>
                </div>
            </div>
        </form>

        <form method="POST" action="{{ route('hostels.bulk.preview') }}" id="bulk-form">
            @csrf
            <div class="card mb-6">
                <div class="card-body">
                    <h3 class="fw-bold mb-1">Where should they go?</h3>
                    <p class="text-muted fs-7 mb-4">Beds in the chosen hostel are assigned automatically. You will see the exact assignments before anything is saved.</p>
                    @if($periods->isEmpty())
                        <div class="alert alert-warning mb-0">There is no open allocation period. Open one in Hostel Management before allocating students.</div>
                    @else
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fs-7 fw-bold" for="bulk-hostel">Destination hostel <span class="text-danger">*</span></label>
                                <select name="hostel_id" id="bulk-hostel" class="form-select" required>
                                    <option value="">Select hostel</option>
                                    @foreach($hostels as $hostel)<option value="{{ $hostel->id }}">{{ $hostel->name }}</option>@endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fs-7 fw-bold" for="bulk-period">Allocation period <span class="text-danger">*</span></label>
                                <select name="period_id" id="bulk-period" class="form-select" required>
                                    <option value="">Select open period</option>
                                    @foreach($periods as $period)<option value="{{ $period->id }}">{{ $period->name }}</option>@endforeach
                                </select>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header py-5 flex-wrap gap-3">
                    <div class="card-title flex-column align-items-start">
                        <h3 class="fw-bold mb-0">Students</h3>
                        <span class="text-muted fs-7 fw-normal">{{ $students->total() }} {{ \Illuminate\Support\Str::plural('student', $students->total()) }} match{{ $students->total() === 1 ? 'es' : '' }}. Selections are retained across pages and filters (up to 200 students).</span>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table class="table table-row-bordered align-middle gy-4 mb-0">
                            <thead>
                                <tr class="text-muted fw-bold fs-7 text-uppercase">
                                    <th style="width:40px;"><div class="form-check form-check-custom form-check-solid"><input class="form-check-input" type="checkbox" id="hostel-select-all" aria-label="Select all students on this page"></div></th>
                                    <th>Student</th><th>Program</th><th>Cohort</th><th>Gender</th><th>Accommodation</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($students as $student)
                                    @php $studentName = trim($student->first_name.' '.$student->other_name.' '.$student->last_name); @endphp
                                    <tr>
                                        <td><div class="form-check form-check-custom form-check-solid"><input class="form-check-input hostel-student-check" type="checkbox" value="{{ $student->id }}" @checked(in_array($student->id, $selectedIds)) aria-label="Select {{ $studentName }}"></div></td>
                                        <td>
                                            <div class="fw-bold text-gray-800">{{ $studentName }}</div>
                                            <div class="text-muted fs-7">{{ $student->student_id }}</div>
                                        </td>
                                        <td>{{ $student->collegeClass?->name ?? '—' }}</td>
                                        <td>{{ $student->cohort?->name ?? '—' }}</td>
                                        <td>{{ $student->gender ?: '—' }}</td>
                                        <td>
                                            @if($student->has_active_allocation)
                                                <span class="badge badge-light-success">Has a bed</span>
                                            @else
                                                <span class="badge badge-light-secondary">Unallocated</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted py-10"><i class="fas fa-user-slash fs-2 d-block mb-3"></i>No students match your filters.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer d-flex flex-wrap justify-content-between align-items-center gap-3 position-sticky bottom-0" style="z-index:5; background: var(--bs-body-bg, #fff);">
                    <span class="fw-bold text-gray-800"><i class="fas fa-square-check me-2 text-primary"></i><span id="hostel-selected-count">{{ count($selectedIds) }} selected</span></span><button class="btn btn-sm btn-light-danger" type="button" id="hostel-clear-selection">Clear selection</button><span id="hostel-selection-error" class="text-danger fs-7" role="alert"></span>
                    <button class="btn btn-primary" type="submit" id="bulk-preview-button" disabled>Preview assignments <i class="fas fa-arrow-right ms-1"></i></button>
                </div>
            </div>
        </form>

        <div class="mt-4">{{ $students->links() }}</div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const all = document.getElementById('hostel-select-all');
            const boxes = Array.from(document.querySelectorAll('.hostel-student-check'));
            const count = document.getElementById('hostel-selected-count');
            const button = document.getElementById('bulk-preview-button');
            const hostel = document.getElementById('bulk-hostel');
            const period = document.getElementById('bulk-period');
            const error = document.getElementById('hostel-selection-error');
            const clear = document.getElementById('hostel-clear-selection');
            const form = document.getElementById('bulk-form');
            const selected = new Set(@json(array_map('intval', $selectedIds)));
            let busy = false;
            const update = () => {
                boxes.forEach(box => box.checked = selected.has(Number(box.value)));
                const visible = boxes.filter(box => box.checked).length;
                all.checked = boxes.length > 0 && visible === boxes.length;
                all.indeterminate = visible > 0 && visible < boxes.length;
                count.textContent = selected.size + ' selected across pages';
                button.disabled = busy || !selected.size || !hostel?.value || !period?.value;
                clear.disabled = busy || !selected.size;
            };
            const change = async (action, ids) => {
                if (busy) return;
                busy = true; error.textContent = ''; update();
                try {
                    const response = await fetch(@json(route('hostels.bulk.selection')), {
                        method: 'POST', headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': @json(csrf_token())},
                        credentials: 'same-origin', body: JSON.stringify({action, student_ids: ids})
                    });
                    const payload = await response.json();
                    if (!response.ok) throw new Error(Object.values(payload.errors || {}).flat().join(' ') || payload.message || 'Could not save selection.');
                    selected.clear(); payload.selected_ids.forEach(id => selected.add(Number(id)));
                } catch (e) { error.textContent = e.message || 'Selection could not be saved.'; }
                finally { busy = false; update(); }
            };
            all.addEventListener('change', () => change(all.checked ? 'add' : 'remove', boxes.map(box => Number(box.value))));
            boxes.forEach(box => box.addEventListener('change', () => change(box.checked ? 'add' : 'remove', [Number(box.value)])));
            clear.addEventListener('click', () => change('clear', []));
            [hostel, period].forEach(field => field?.addEventListener('change', update));
            form.addEventListener('submit', event => { if (busy || !selected.size) event.preventDefault(); });
            update();
        });
    </script>
</x-dashboard.default>
