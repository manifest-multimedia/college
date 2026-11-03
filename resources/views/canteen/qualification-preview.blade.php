<x-dashboard.default title="Preview qualification import">
    <div class="container-fluid py-5">
        <a href="{{ route('canteen.lists.index') }}" class="btn btn-sm btn-light mb-5">Back to qualification lists</a>
        <h1 class="mb-3">Review qualification import</h1>
        <p>No qualification changes have been saved. This preview expires in 30 minutes.</p>
        <div class="alert alert-primary"><strong>{{ $preview['prefix'] }} · {{ $preview['year'] }} · Semester {{ $preview['semester'] }}</strong><br>@if($preview['activate'])Confirming will also make this the active canteen period.@elseThe active canteen period will remain unchanged.@endif</div>
        @if($errors->any())<div class="alert alert-danger" role="alert">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
        <div class="card mb-6"><div class="card-body"><div class="table-responsive"><table class="table table-row-bordered align-middle"><thead><tr><th>Class / workbook</th><th>Students</th><th>Qualified</th><th>Not qualified</th><th>Effect</th></tr></thead><tbody>
            @foreach($preview['summary'] as $file)
            <tr><td><strong>{{ $file['class'] }}</strong><br>{{ $file['file'] }}</td><td>{{ $file['rows'] }}</td><td>{{ $file['qualified'] }}</td><td>{{ $file['not_qualified'] }}</td><td>@if($file['revoked_replay'])<strong class="text-warning">Previously withdrawn file: will remain withdrawn.</strong>@elseif($file['replayed'])Unchanged file: no duplicate statuses.@elseif($file['replaces'])<strong class="text-warning">Replaces batches {{ implode(', ', $file['replaces']) }}.</strong><br>{{ $file['omitted'] }} students omitted from the earlier lists.@elseNew qualification list.@endif</td></tr>
            @endforeach
            </tbody><tfoot><tr class="fw-bold"><td>Total</td><td>{{ array_sum(array_column($preview['summary'], 'rows')) }}</td><td>{{ array_sum(array_column($preview['summary'], 'qualified')) }}</td><td>{{ array_sum(array_column($preview['summary'], 'not_qualified')) }}</td><td></td></tr></tfoot></table></div></div></div>
        <div class="alert alert-warning">Confirm only reviewed, complete class lists. Students omitted from a replacement lose the earlier list's approval. Existing valid financial evidence can still determine clearance independently. Qualification statuses do not create payments.</div>
        <form method="POST" action="{{ route('canteen.lists.confirm', $preview['token']) }}">@csrf
            <label class="form-check mb-5"><input class="form-check-input" type="checkbox" name="acknowledge" value="1" required><span class="form-check-label">I confirm the institution, period, totals and any replacements shown above.</span></label>
            <button class="btn btn-primary" type="submit">Confirm import</button>
            <a class="btn btn-light ms-2" href="{{ route('canteen.lists.index') }}">Cancel</a>
        </form>
    </div>
</x-dashboard.default>
