@php
    $defaultEnabled = (bool) old('enabled', $policy?->enabled);
    $defaultRelease = old('release_mode', $policy?->release_mode ?? 'immediate');
    $defaultDetail = old('detail_level', $policy?->detail_level ?? 'score');

    $releaseOptions = [
        'immediate' => ['label' => 'Immediately after submission', 'icon' => 'fa-bolt', 'help' => 'Students see their result as soon as they submit.'],
        'after_end' => ['label' => 'After examination closes', 'icon' => 'fa-hourglass-end', 'help' => 'Results stay hidden until the exam window ends for everyone.'],
        'manual' => ['label' => 'After administrator publication', 'icon' => 'fa-user-shield', 'help' => 'Results stay hidden until you publish them for each exam.'],
    ];
    $detailOptions = [
        'score' => ['label' => 'Score and percentage', 'icon' => 'fa-percent', 'help' => 'Only the final score and percentage.'],
        'summary' => ['label' => 'Score, marks and pass/fail', 'icon' => 'fa-list-check', 'help' => 'Adds marks breakdown and the pass or fail outcome.'],
    ];
    $releaseShort = ['disabled' => 'Disabled', 'immediate' => 'Immediate', 'after_end' => 'After exam closes', 'manual' => 'Manual publication'];
    $detailShort = ['score' => 'Score only', 'summary' => 'Summary'];
    $releaseBadge = ['disabled' => 'danger', 'immediate' => 'success', 'after_end' => 'info', 'manual' => 'warning'];

    $overriddenCount = $exams->filter(fn ($exam) => filled($exam->result_release_override) || filled($exam->result_detail_override))->count();
    $publishedCount = $exams->filter(fn ($exam) => $exam->results_published_at)->count();
@endphp

<x-dashboard.default title="Online Examination Result Visibility">
    <div class="container-fluid py-6">

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-6">
            <div>
                <h1 class="fs-2 fw-bold text-gray-900 mb-1">
                    <i class="fas fa-eye me-2 text-primary"></i>Online Examination Result Visibility
                </h1>
                <p class="text-muted mb-0">Decide when students see their online exam results and how much detail they get.</p>
            </div>
            <div class="d-flex gap-3">
                <div class="border border-dashed rounded px-4 py-2 text-center">
                    <div class="fs-4 fw-bold text-gray-800">{{ $exams->count() }}</div>
                    <div class="fs-8 text-muted">Examinations</div>
                </div>
                <div class="border border-dashed rounded px-4 py-2 text-center">
                    <div class="fs-4 fw-bold text-warning">{{ $overriddenCount }}</div>
                    <div class="fs-8 text-muted">With overrides</div>
                </div>
                <div class="border border-dashed rounded px-4 py-2 text-center">
                    <div class="fs-4 fw-bold text-success">{{ $publishedCount }}</div>
                    <div class="fs-8 text-muted">Published</div>
                </div>
            </div>
        </div>

        @if(session('message'))
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
                <i class="fas fa-circle-check me-2"></i>
                <div>{{ session('message') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger" role="alert">
                <div class="fw-bold mb-1"><i class="fas fa-triangle-exclamation me-2"></i>Your changes could not be saved</div>
                <ul class="mb-0 ps-4">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row g-6 mb-6">
            <div class="col-xl-8">
                <form method="POST" action="{{ route('academics.online-exam-result-policy.save') }}" class="card h-100">
                    @csrf
                    <div class="card-header">
                        <div class="card-title flex-column align-items-start">
                            <h3 class="fw-bold mb-0">Configuration</h3>
                            <span class="text-muted fs-7 fw-normal">Applies to every examination that has no override below.</span>
                        </div>
                    </div>

                    <div class="card-body">
                        <input type="hidden" name="enabled" value="0">
                        <div class="d-flex justify-content-between align-items-center border rounded p-4 mb-6">
                            <div class="pe-4">
                                <div class="fw-bold text-gray-800">Allow online examination results</div>
                                <div class="text-muted fs-7">Turn off to hide results from students by default.</div>
                            </div>
                            <div class="form-check form-switch form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" role="switch" id="policy-enabled" name="enabled" value="1" @checked($defaultEnabled)>
                            </div>
                        </div>

                        <div id="policy-options" @class(['opacity-50' => ! $defaultEnabled])>
                            <div class="fw-bold text-gray-800 mb-3">Release timing</div>
                            <div class="row g-3 mb-6">
                                @foreach($releaseOptions as $key => $option)
                                    <div class="col-md-4">
                                        <label class="d-block border rounded p-4 h-100 cursor-pointer position-relative" for="release-{{ $key }}">
                                            <div class="form-check form-check-custom form-check-solid mb-2">
                                                <input class="form-check-input" type="radio" name="release_mode" id="release-{{ $key }}" value="{{ $key }}" @checked($defaultRelease === $key)>
                                                <span class="form-check-label fw-bold text-gray-800 ms-2">
                                                    <i class="fas {{ $option['icon'] }} me-1 text-primary"></i>{{ $option['label'] }}
                                                </span>
                                            </div>
                                            <div class="text-muted fs-7">{{ $option['help'] }}</div>
                                        </label>
                                    </div>
                                @endforeach
                            </div>

                            <div class="fw-bold text-gray-800 mb-3">Visible details</div>
                            <div class="row g-3">
                                @foreach($detailOptions as $key => $option)
                                    <div class="col-md-6">
                                        <label class="d-block border rounded p-4 h-100 cursor-pointer" for="detail-{{ $key }}">
                                            <div class="form-check form-check-custom form-check-solid mb-2">
                                                <input class="form-check-input" type="radio" name="detail_level" id="detail-{{ $key }}" value="{{ $key }}" @checked($defaultDetail === $key)>
                                                <span class="form-check-label fw-bold text-gray-800 ms-2">
                                                    <i class="fas {{ $option['icon'] }} me-1 text-primary"></i>{{ $option['label'] }}
                                                </span>
                                            </div>
                                            <div class="text-muted fs-7">{{ $option['help'] }}</div>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="card-footer d-flex justify-content-between align-items-center">
                        <span class="text-muted fs-7">
                            Last updated:
                            {{ $policy?->updated_at ? \Illuminate\Support\Carbon::parse($policy->updated_at)->format('d M Y, H:i') : 'never saved' }}
                        </span>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-floppy-disk me-1"></i> Save institution policy
                        </button>
                    </div>
                </form>
            </div>

            <div class="col-xl-4">
                <div class="card h-100 bg-light">
                    <div class="card-body">
                        <h4 class="fw-bold mb-4"><i class="fas fa-circle-info me-2 text-primary"></i>How this works</h4>
                        <ol class="ps-4 mb-5 text-gray-700">
                            <li class="mb-3">The <strong>institution default</strong> is used for every exam.</li>
                            <li class="mb-3">An <strong>exam override</strong> replaces the default for that exam only. Leave it on <em>Inherit</em> to follow the default.</li>
                            <li>With <strong>manual publication</strong>, students see nothing until you press <em>Publish</em> on the exam.</li>
                        </ol>
                        <div class="separator separator-dashed mb-5"></div>
                        <div class="fw-bold text-gray-800 mb-2">Current default</div>
                        <div class="d-flex flex-wrap gap-2">
                            <span class="badge badge-light-{{ $defaultEnabled ? $releaseBadge[$defaultRelease] ?? 'primary' : 'danger' }}">
                                {{ $defaultEnabled ? ($releaseShort[$defaultRelease] ?? $defaultRelease) : 'Results disabled' }}
                            </span>
                            @if($defaultEnabled)
                                <span class="badge badge-light-primary">{{ $detailShort[$defaultDetail] ?? $defaultDetail }}</span>
                            @endif
                        </div>
                        @unless($policy)
                            <p class="text-muted fs-7 mt-4 mb-0">No policy has been saved yet, so results are currently off. Save the default to turn them on.</p>
                        @endunless
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header flex-wrap gap-3 py-5">
                <div class="card-title flex-column align-items-start">
                    <h3 class="fw-bold mb-0">Examination-specific overrides</h3>
                    <span class="text-muted fs-7 fw-normal">Showing the {{ $exams->count() }} most recent examinations.</span>
                </div>
                <div class="d-flex flex-wrap gap-3 align-items-center">
                    <div class="position-relative">
                        <i class="fas fa-magnifying-glass position-absolute top-50 translate-middle-y ms-4 text-muted"></i>
                        <input type="search" id="exam-search" class="form-control form-control-sm ps-12" style="min-width: 240px;" placeholder="Search examinations" aria-label="Search examinations">
                    </div>
                    <select id="exam-filter" class="form-select form-select-sm w-auto" aria-label="Filter examinations">
                        <option value="all">All examinations</option>
                        <option value="overridden">With overrides</option>
                        <option value="unpublished">Not yet published</option>
                        <option value="published">Published</option>
                    </select>
                </div>
            </div>

            <div class="card-body pt-0">
                <div class="table-responsive">
                    <table class="table table-row-bordered align-middle gy-4 mb-0">
                        <thead>
                            <tr class="text-muted fw-bold fs-7 text-uppercase">
                                <th>Examination</th>
                                <th>Currently applies</th>
                                <th style="min-width: 200px;">Release timing</th>
                                <th style="min-width: 150px;">Details</th>
                                <th class="text-end" style="min-width: 190px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($exams as $exam)
                                @php
                                    $effectiveRelease = $exam->result_release_override ?: ($defaultEnabled ? $defaultRelease : 'disabled');
                                    $effectiveDetail = $exam->result_detail_override ?: $defaultDetail;
                                    $hasOverride = filled($exam->result_release_override) || filled($exam->result_detail_override);
                                    $formId = 'result-override-'.$exam->id;
                                @endphp
                                <tr class="exam-row"
                                    data-title="{{ \Illuminate\Support\Str::lower($exam->display_title.' '.($exam->course?->name ?? '')) }}"
                                    data-overridden="{{ $hasOverride ? '1' : '0' }}"
                                    data-published="{{ $exam->results_published_at ? '1' : '0' }}">
                                    <td>
                                        <div class="fw-bold text-gray-800">{{ $exam->display_title }}</div>
                                        <div class="text-muted fs-7">
                                            {{ $exam->course?->name ?? 'No course' }}
                                            @if($exam->end_date)
                                                &middot; closes {{ $exam->end_date->format('d M Y') }}
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-1">
                                            <span class="badge badge-light-{{ $releaseBadge[$effectiveRelease] ?? 'primary' }}">{{ $releaseShort[$effectiveRelease] ?? $effectiveRelease }}</span>
                                            @if($effectiveRelease !== 'disabled')
                                                <span class="badge badge-light-primary">{{ $detailShort[$effectiveDetail] ?? $effectiveDetail }}</span>
                                            @endif
                                            @if($hasOverride)
                                                <span class="badge badge-light-warning">Override</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <form id="{{ $formId }}" method="POST" action="{{ route('academics.online-exam-result-policy.override', $exam) }}">@csrf</form>
                                        <select form="{{ $formId }}" class="form-select form-select-sm override-field" name="result_release_override" data-initial="{{ $exam->result_release_override }}" aria-label="Release timing for {{ $exam->display_title }}">
                                            <option value="">Inherit default</option>
                                            @foreach($releaseShort as $key => $label)
                                                <option value="{{ $key }}" @selected($exam->result_release_override === $key)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <select form="{{ $formId }}" class="form-select form-select-sm override-field" name="result_detail_override" data-initial="{{ $exam->result_detail_override }}" aria-label="Visible details for {{ $exam->display_title }}">
                                            <option value="">Inherit default</option>
                                            @foreach($detailShort as $key => $label)
                                                <option value="{{ $key }}" @selected($exam->result_detail_override === $key)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-inline-flex align-items-center gap-2">
                                            <button type="submit" form="{{ $formId }}" class="btn btn-sm btn-primary override-save" disabled>
                                                <i class="fas fa-floppy-disk me-1"></i> Save
                                            </button>
                                            @if($exam->results_published_at)
                                                <span class="badge badge-light-success" title="Published {{ $exam->results_published_at->format('d M Y, H:i') }}">
                                                    <i class="fas fa-check me-1"></i>Published {{ $exam->results_published_at->format('d M') }}
                                                </span>
                                            @else
                                                <form method="POST" action="{{ route('academics.online-exam-result-policy.publish', $exam) }}" onsubmit="return confirm('Publish results for &quot;{{ addslashes($exam->display_title) }}&quot;? Students will be able to see them.')">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-light-success">
                                                        <i class="fas fa-bullhorn me-1"></i> Publish
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-10">
                                        <i class="fas fa-file-circle-question fs-2 d-block mb-3"></i>
                                        No examinations found.
                                    </td>
                                </tr>
                            @endforelse
                            <tr id="exam-no-match" class="d-none">
                                <td colspan="5" class="text-center text-muted py-10">No examinations match your search.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const enabledToggle = document.getElementById('policy-enabled');
            const options = document.getElementById('policy-options');
            if (enabledToggle && options) {
                enabledToggle.addEventListener('change', function () {
                    options.classList.toggle('opacity-50', !enabledToggle.checked);
                });
            }

            document.querySelectorAll('.exam-row').forEach(function (row) {
                const fields = row.querySelectorAll('.override-field');
                const saveButton = row.querySelector('.override-save');
                const syncSaveState = function () {
                    const changed = Array.from(fields).some(function (field) {
                        return field.value !== field.dataset.initial;
                    });
                    saveButton.disabled = !changed;
                };
                fields.forEach(function (field) {
                    field.addEventListener('change', syncSaveState);
                });
            });

            const search = document.getElementById('exam-search');
            const filter = document.getElementById('exam-filter');
            const rows = Array.from(document.querySelectorAll('.exam-row'));
            const noMatch = document.getElementById('exam-no-match');

            const applyFilters = function () {
                const term = search.value.trim().toLowerCase();
                const mode = filter.value;
                let visible = 0;
                rows.forEach(function (row) {
                    const matchesTerm = term === '' || row.dataset.title.includes(term);
                    const matchesMode = mode === 'all'
                        || (mode === 'overridden' && row.dataset.overridden === '1')
                        || (mode === 'published' && row.dataset.published === '1')
                        || (mode === 'unpublished' && row.dataset.published === '0');
                    const show = matchesTerm && matchesMode;
                    row.classList.toggle('d-none', !show);
                    if (show) {
                        visible++;
                    }
                });
                if (noMatch) {
                    noMatch.classList.toggle('d-none', visible > 0 || rows.length === 0);
                }
            };

            search.addEventListener('input', applyFilters);
            filter.addEventListener('change', applyFilters);
        });
    </script>
</x-dashboard.default>
