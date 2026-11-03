<x-dashboard.default title="Withdraw qualification list">
    <div class="container-fluid py-5"><a class="btn btn-sm btn-light mb-5" href="{{ route('canteen.lists.index') }}">Back to qualification lists</a>
        <div class="card"><div class="card-body"><h1>Withdraw qualification list #{{ $batch->id }}</h1><p>{{ $batch->class_label }} · {{ $batch->academic_year }} · Semester {{ $batch->semester }}<br>{{ $batch->file_name }}</p>
        <div class="alert alert-warning">This list will stop supplying clearance. Its audit history remains. Older approvals will not be reactivated. Valid financial evidence, if available, remains independent.</div>
        @if($errors->any())<div class="alert alert-danger">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
        @if($batch->revoked_at)<p>This list has already been withdrawn or replaced.</p>@else
        <form method="POST" action="{{ route('canteen.lists.revoke', $batch->id) }}">@csrf
            <label class="form-label" for="reason">Reason for withdrawal</label><textarea class="form-control mb-5" id="reason" name="reason" minlength="5" maxlength="500" required>{{ old('reason') }}</textarea>
            <label class="form-check mb-5"><input class="form-check-input" type="checkbox" name="acknowledge" value="1" required><span class="form-check-label">I confirm withdrawal of this specific class list and period.</span></label>
            <button class="btn btn-danger" type="submit">Withdraw this list</button>
        </form>@endif
        </div></div>
    </div>
</x-dashboard.default>
