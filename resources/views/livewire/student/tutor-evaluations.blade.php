<div class="container-fluid py-5">
    <div class="mb-5"><h1 class="fw-bold">Tutor Evaluations</h1><p class="text-muted">Review your lecturers for the semester. Your answers are anonymous and cannot be connected to your student account.</p></div>
    @if(session('message'))<div class="alert alert-success">{{ session('message') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    @if($selectedCampaign && $subjectId && $lecturerId)
        <div class="card"><div class="card-header"><h3 class="card-title">{{ $selectedCampaign->name }}</h3></div><div class="card-body">
        @if($selectedAssignment)
            <div class="alert alert-primary mb-5" role="status">
                <strong>Course:</strong> {{ $selectedAssignment['subject_name'] }}<br>
                <strong>Lecturer:</strong> {{ $selectedAssignment['lecturer_name'] }}<br>
                <strong>Academic year:</strong> {{ $selectedCampaign->semester?->academicYear?->name }}<br>
                <strong>Semester:</strong> {{ $selectedCampaign->semester?->name }}
                <p class="mb-0 mt-2">Please evaluate this lecturer for the course shown above. Your answers are submitted anonymously.</p>
            </div>
        @endif
        <form wire:submit.prevent="submit">
        @foreach($selectedCampaign->formVersion->schema as $section)
            <h3 class="mt-4 mb-3">{{ $section['title'] }}</h3>
            @foreach($section['questions'] as $question)
                <div class="mb-5"><label class="form-label fw-semibold">{{ $question['label'] }} @if($question['required'])<span class="text-danger">*</span>@endif</label>
                    @if($question['type'] === 'rating')
                        <div class="d-flex flex-wrap gap-3">@for($score = 1; $score <= ($question['max_rating'] ?? 5); $score++)<label class="form-check form-check-inline"><input class="form-check-input" type="radio" value="{{ $score }}" wire:model="answers.{{ $question['id'] }}"><span class="form-check-label">{{ $score }}</span></label>@endfor</div>
                    @elseif($question['type'] === 'yes_no')
                        <select class="form-select" wire:model="answers.{{ $question['id'] }}"><option value="">Select...</option><option value="yes">Yes</option><option value="no">No</option></select>
                    @elseif($question['type'] === 'single_choice')
                        <select class="form-select" wire:model="answers.{{ $question['id'] }}"><option value="">Select...</option>@foreach($question['options'] as $option)<option value="{{ $option }}">{{ $option }}</option>@endforeach</select>
                    @elseif($question['type'] === 'multiple_choice')
                        @foreach($question['options'] as $option)<label class="form-check mb-2"><input type="checkbox" class="form-check-input" value="{{ $option }}" wire:model="answers.{{ $question['id'] }}"><span class="form-check-label">{{ $option }}</span></label>@endforeach
                    @elseif($question['type'] === 'textarea')
                        <textarea rows="4" class="form-control" maxlength="3000" wire:model="answers.{{ $question['id'] }}"></textarea>
                    @else
                        <input class="form-control" maxlength="500" wire:model="answers.{{ $question['id'] }}">
                    @endif
                    @error('answers.'.$question['id'])<span class="text-danger small">{{ $message }}</span>@enderror
                </div>
            @endforeach
        @endforeach
        <div class="d-flex gap-3"><button type="button" class="btn btn-light" wire:click="$set('campaignId', null)">Back</button><button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="submit">Submit anonymously</button></div>
        </form></div></div>
    @else
        @forelse($assignments as $group)
            @if(count($group['assignments']))<div class="card mb-5"><div class="card-header"><h3 class="card-title">{{ $group['campaign']->name }} — {{ $group['campaign']->semester?->name }}</h3></div><div class="card-body"><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Lecturer</th><th>Course</th><th>Status</th></tr></thead><tbody>
                @foreach($group['assignments'] as $assignment)<tr><td>{{ $assignment['lecturer_name'] }}</td><td>{{ $assignment['subject_name'] }}</td><td>@if($assignment['completed'])<span class="badge badge-light-success">Completed</span>@else<button class="btn btn-sm btn-primary" wire:click="select({{ $group['campaign']->id }}, {{ $assignment['subject_id'] }}, {{ $assignment['lecturer_id'] }})">Evaluate tutor</button>@endif</td></tr>@endforeach
            </tbody></table></div></div></div>@endif
        @empty <div class="alert alert-info">No evaluation campaigns are currently open.</div> @endforelse
    @endif
</div>
