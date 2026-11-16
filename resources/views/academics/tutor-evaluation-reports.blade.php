<x-dashboard.default>
<div class="container-fluid py-5">
    <div class="d-flex justify-content-between align-items-center mb-5"><div><h1>Tutor Assessment Reports</h1><p class="text-muted">Anonymous, course-specific lecturer feedback and participation overview.</p></div><a class="btn btn-light" href="{{ route('academics.tutor-evaluations') }}">Manage assessments</a></div>
    <form method="GET" class="card card-body mb-5"><div class="row g-3 align-items-end">
        <div class="col-md-4"><label class="form-label">Campaign</label><select name="campaign" class="form-select" required>@foreach($campaigns as $item)<option value="{{ $item->id }}" @selected($campaign?->id === $item->id)>{{ $item->name }} — {{ $item->semester?->name }}</option>@endforeach</select></div>
        <div class="col-md-3"><label class="form-label">Lecturer</label><input type="search" class="form-control" list="assessment-lecturers" id="assessment-lecturer-search" placeholder="Search lecturer name" autocomplete="off" value="{{ $lecturers->firstWhere('id', (int)$lecturerId)?->name ?? '' }}"><input type="hidden" name="lecturer" id="assessment-lecturer-id" value="{{ $lecturerId }}"><datalist id="assessment-lecturers">@foreach($lecturers as $lecturer)<option value="{{ $lecturer->name }} [#{{ $lecturer->id }}]"></option>@endforeach</datalist></div>
        <div class="col-md-3"><label class="form-label">Course</label><input type="search" class="form-control" list="assessment-courses" id="assessment-course-search" placeholder="Search course name" autocomplete="off" value="{{ $courses->firstWhere('id', (int)$subjectId)?->name ?? '' }}"><input type="hidden" name="course" id="assessment-course-id" value="{{ $subjectId }}"><datalist id="assessment-courses">@foreach($courses as $course)<option value="{{ $course->name }} [#{{ $course->id }}]"></option>@endforeach</datalist></div>
        <div class="col-md-2"><button class="btn btn-primary w-100">View report</button></div>
    </div></form>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        [['assessment-lecturer-search','assessment-lecturer-id'],['assessment-course-search','assessment-course-id']].forEach(function (pair) {
            const input = document.getElementById(pair[0]), hidden = document.getElementById(pair[1]);
            input.addEventListener('input', function () {
                const match = input.value.match(/\[#(\d+)\]$/);
                hidden.value = match ? match[1] : '';
            });
        });
    });
    </script>
    @if($campaign && $report)
        <div class="row g-4 mb-5"><div class="col-lg-6"><div class="card h-100"><div class="card-header"><h3 class="card-title">Responses by campaign</h3></div><div class="card-body">@php $maxResponses = max(1, ...array_column($overview, 'responses')); @endphp @foreach($overview as $trend)<div class="mb-3"><div class="d-flex justify-content-between"><span>{{ $trend['campaign'] }}</span><strong>{{ $trend['responses'] }}</strong></div><div class="progress" style="height:12px"><div class="progress-bar" role="progressbar" style="width:{{ 100 * $trend['responses'] / $maxResponses }}%" aria-valuenow="{{ $trend['responses'] }}" aria-valuemin="0" aria-valuemax="{{ $maxResponses }}"></div></div></div>@endforeach</div></div></div><div class="col-lg-6"><div class="card h-100"><div class="card-header"><h3 class="card-title">Average rating by campaign</h3></div><div class="card-body">@php $maxAverage = max(1, ...array_map(fn($item) => $item['average'] ?? 0, $overview)); @endphp @foreach($overview as $trend)<div class="mb-3"><div class="d-flex justify-content-between"><span>{{ $trend['campaign'] }}</span><strong>{{ $trend['average'] ?? 'N/A' }}</strong></div><div class="progress" style="height:12px">@if($trend['average'] !== null)<div class="progress-bar bg-success" role="progressbar" style="width:{{ 100 * $trend['average'] / $maxAverage }}%" aria-valuenow="{{ $trend['average'] }}" aria-valuemin="0" aria-valuemax="{{ $maxAverage }}"></div>@endif</div></div>@endforeach<p class="text-muted small">Only privacy-eligible rating results are included. Rating scales and questionnaire versions may differ.</p></div></div></div></div>
        <div class="card mb-5"><div class="card-header"><h3 class="card-title">Institutional overview & semester trends</h3></div><div class="card-body"><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Campaign</th><th>Academic year</th><th>Semester</th><th>Responses</th><th>Reportable tutor/course groups</th><th>Weighted rating average</th></tr></thead><tbody>@foreach($overview as $trend)<tr><td>{{ $trend['campaign'] }}</td><td>{{ $trend['academic_year'] }}</td><td>{{ $trend['semester'] }}</td><td>{{ $trend['responses'] }}</td><td>{{ $trend['reportable_groups'] }}</td><td>{{ $trend['average'] ?? 'Withheld / no ratings' }}</td></tr>@endforeach</tbody></table></div><p class="text-muted small">Only groups meeting each campaign's privacy threshold contribute to averages. Different questionnaires or rating scales may not be directly comparable.</p></div></div>
        <div class="d-flex gap-2 mb-4"><a class="btn btn-light-info" href="{{ route('academics.tutor-evaluation-reports.xlsx', request()->query()) }}">Download Excel (.xlsx)</a><a class="btn btn-light-primary" href="{{ route('academics.tutor-evaluation-reports.pdf', request()->query()) }}">Download management PDF</a><a class="btn btn-light-success" href="{{ route('academics.tutor-evaluation-reports.csv', request()->query()) }}">Download CSV (Excel-compatible)</a></div>
        <div class="card mb-5"><div class="card-body"><h3>{{ $campaign->name }}</h3><p>{{ $campaign->semester?->name }} — {{ $campaign->semester?->academicYear?->name }}</p><strong>{{ $report['total_responses'] }} responses</strong><p class="text-muted mb-0">Detailed results are available only for tutor/course groups meeting the {{ $campaign->report_minimum }}-response privacy threshold. Participation counts are not proof of student identity.</p></div></div>
        @forelse($report['pairs'] as $pair)
            <div class="card mb-4"><div class="card-header"><h3 class="card-title">{{ $pair['lecturer'] }} — {{ $pair['course'] }}</h3></div><div class="card-body">
                <p>{{ $pair['responses'] }} responses · {{ $pair['eligible'] }} eligible students @if($pair['eligible']) · {{ round(100 * $pair['responses'] / $pair['eligible'], 1) }}% response ratio @endif</p>
                @if(! $pair['visible'])<div class="alert alert-info">Detailed results withheld: fewer than {{ $campaign->report_minimum }} responses.</div>
                @else
                    @foreach($report['details'][$pair['lecturer_id'].':'.$pair['subject_id']] ?? [] as $question)
                        <div class="border-bottom py-3"><strong>{{ $question['label'] }}</strong><div class="text-muted">{{ $question['count'] }} answers @isset($question['average']) · Average: {{ $question['average'] }} @endisset</div>
                            @foreach($question['distribution'] ?? [] as $answer => $count)<span class="badge badge-light me-2">{{ $answer }}: {{ $count }}</span>@endforeach
                            @foreach($question['comments'] ?? [] as $comment)<p class="mt-2 mb-1">{{ $comment }}</p>@endforeach
                        </div>
                    @endforeach
                @endif
            </div></div>
        @empty <div class="alert alert-info">No responses match the selected filters.</div>@endforelse
    @else <div class="alert alert-info">No campaigns available yet.</div>@endif
</div>
</x-dashboard.default>
