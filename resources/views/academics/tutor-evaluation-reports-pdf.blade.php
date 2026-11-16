<!doctype html><html><head><meta charset="utf-8"><style>
@page{margin:40px 42px 55px}body{font-family:DejaVu Sans,sans-serif;font-size:10px;color:#263449}h1{font-size:21px;color:#183153;margin:5px 0}h2{font-size:14px;color:#183153}.muted{color:#677487}.header{border-bottom:3px solid #244775;padding-bottom:14px;margin-bottom:20px}.brand{font-weight:bold;font-size:15px}.logo{height:46px;float:right}.summary{background:#edf3f9;padding:13px;margin:16px 0}.pair{page-break-inside:avoid;margin:18px 0;border-top:1px solid #cbd5e1;padding-top:12px}.question{margin:10px 0}.label{font-weight:bold}.tag{font-size:9px;color:#596779}table{border-collapse:collapse;width:100%}th,td{border-bottom:1px solid #dce2e9;padding:6px;text-align:left}.footer{position:fixed;bottom:-34px;left:0;right:0;border-top:1px solid #ccd4df;padding-top:8px;color:#64748b;font-size:8px}ul{margin:5px 0;padding-left:18px}li{margin:4px 0}
</style></head><body>
<div class="header">
@php $logo = config('branding.logo.primary'); $logoPath = $logo && str_starts_with($logo, '/') ? public_path(ltrim($logo, '/')) : null; @endphp
@if($logoPath && is_file($logoPath))<img class="logo" src="{{ $logoPath }}" alt="Institution logo">@endif
<div class="brand">{{ config('branding.institution.name', config('app.name')) }}</div>
<h1>Tutor Assessment — Management Report</h1>
<div class="muted">{{ $campaign->name }} · {{ $campaign->semester?->name }} · {{ $campaign->semester?->academicYear?->name }}</div>
<div class="muted">Generated {{ now()->format('d M Y, H:i') }} · Confidential management document</div>
</div>
<div class="summary"><strong>Report summary</strong><p>{{ $report['total_responses'] }} recorded responses across {{ count($report['pairs']) }} lecturer/course combinations. Detailed results are withheld for groups with fewer than {{ $campaign->report_minimum }} responses.</p></div>
<table><thead><tr><th>Lecturer</th><th>Course</th><th>Responses</th><th>Eligible</th><th>Reportable</th></tr></thead><tbody>
@foreach($report['pairs'] as $pair)<tr><td>{{ $pair['lecturer'] }}</td><td>{{ $pair['course'] }}</td><td>{{ $pair['responses'] }}</td><td>{{ $pair['eligible'] }}</td><td>{{ $pair['visible'] ? 'Yes' : 'No' }}</td></tr>@endforeach
</tbody></table>
@foreach($report['pairs'] as $pair)
<div class="pair"><h2>{{ $pair['lecturer'] }} — {{ $pair['course'] }}</h2><p class="tag">{{ $pair['responses'] }} responses · {{ $pair['eligible'] }} eligible students</p>
@if(! $pair['visible'])<p>Detailed feedback withheld to protect respondent anonymity.</p>@else
@foreach($report['details'][$pair['lecturer_id'].':'.$pair['subject_id']] ?? [] as $question)
<div class="question"><div class="label">{{ $question['label'] }}</div><div class="tag">{{ $question['count'] }} answers @isset($question['average']) · Average rating: {{ $question['average'] }} @endisset</div>
<ul>@foreach($question['distribution'] ?? [] as $answer => $count)<li>{{ $answer }}: {{ $count }}</li>@endforeach @foreach($question['comments'] ?? [] as $comment)<li>{{ $comment }}</li>@endforeach</ul></div>
@endforeach
@endif</div>
@endforeach
<div class="footer">{{ config('branding.institution.name', config('app.name')) }} · Tutor Assessment · Internal use only. Free-text comments are anonymous but may contain identifying information supplied by respondents.</div>
</body></html>
