<div class="container-fluid py-5">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div><h1 class="fw-bold mb-1">Tutor Evaluations</h1><p class="text-muted mb-0">Build reusable questionnaires, publish versions, and administer semester reviews.</p></div>
    </div>
    @if(session('message')) <div class="alert alert-success">{{ session('message') }}</div> @endif
    @if($errors->any()) <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div> @endif
    <div class="row g-5">
        <div class="col-xl-7">
            <div class="card mb-5">
                <div class="card-header"><h3 class="card-title">Form Builder — {{ $formId ? 'Publish a new version' : 'New evaluation form' }}</h3></div>
                <div class="card-body">
                    <div class="row g-3 mb-4">
                        <div class="col-md-8"><label class="form-label">Evaluation name</label><input class="form-control" wire:model="name" placeholder="End of Semester Tutor Review"></div>
                        <div class="col-md-4"><label class="form-label">Start from a form</label><select class="form-select" wire:change="loadForm($event.target.value)" aria-label="Load existing form"><option value="">Select to create version</option>@foreach($forms as $form)<option value="{{ $form->id }}">{{ $form->name }}</option>@endforeach</select></div>
                        <div class="col-12"><label class="form-label">Description</label><textarea class="form-control" rows="2" wire:model="description"></textarea></div>
                    </div>
                    @foreach($sections as $si => $section)
                        <div class="border rounded p-4 mb-4" wire:key="section-{{ $si }}">
                            <div class="d-flex gap-2 mb-3"><input class="form-control fw-bold" aria-label="Section title" wire:model="sections.{{ $si }}.title" placeholder="Section title"><button class="btn btn-light-danger" type="button" wire:click="removeSection({{ $si }})" @disabled(count($sections) === 1)>Remove</button></div>
                            @foreach($section['questions'] as $qi => $question)
                                <div class="bg-light rounded p-3 mb-3" wire:key="question-{{ $question['id'] }}">
                                    <div class="row g-2">
                                        <div class="col-md-7"><label class="form-label">Question</label><input class="form-control" wire:model="sections.{{ $si }}.questions.{{ $qi }}.label" placeholder="The tutor explains concepts clearly"></div>
                                        <div class="col-md-5"><label class="form-label">Answer type</label><select class="form-select" wire:model.live="sections.{{ $si }}.questions.{{ $qi }}.type"><option value="rating">Rating scale</option><option value="single_choice">Single choice</option><option value="multiple_choice">Multiple choice</option><option value="yes_no">Yes / No</option><option value="text">Short answer</option><option value="textarea">Long answer</option></select></div>
                                        @if(($question['type'] ?? '') === 'rating')
                                            <div class="col-md-5"><label class="form-label">Maximum score (2–10)</label><input type="number" min="2" max="10" class="form-control" wire:model="sections.{{ $si }}.questions.{{ $qi }}.max_rating"></div>
                                        @elseif(in_array($question['type'] ?? '', ['single_choice', 'multiple_choice']))
                                            <div class="col-12"><label class="form-label">Options (one per line)</label><textarea class="form-control" rows="3" wire:model.blur="sections.{{ $si }}.questions.{{ $qi }}.optionsText" placeholder="Strongly agree&#10;Agree&#10;Neutral&#10;Disagree"></textarea></div>
                                        @endif
                                        <div class="col-12 d-flex justify-content-between align-items-center"><label class="form-check"><input class="form-check-input" type="checkbox" wire:model="sections.{{ $si }}.questions.{{ $qi }}.required"><span class="form-check-label">Required</span></label><button type="button" class="btn btn-sm btn-light-danger" wire:click="removeQuestion({{ $si }}, {{ $qi }})">Remove question</button></div>
                                    </div>
                                </div>
                            @endforeach
                            <button type="button" class="btn btn-sm btn-light-primary" wire:click="addQuestion({{ $si }})">+ Add question</button>
                        </div>
                    @endforeach
                    <div class="d-flex flex-wrap gap-2"><button class="btn btn-light" wire:click="addSection">+ Add section</button><button class="btn btn-light-secondary" wire:click="resetBuilder">New blank form</button><button class="btn btn-primary" wire:click="publish" wire:loading.attr="disabled" wire:target="publish">Publish version</button></div>
                </div>
            </div>
        </div>
        <div class="col-xl-5">
            <div class="card mb-5"><div class="card-header"><h3 class="card-title">Administer a semester evaluation</h3></div><div class="card-body">
                <label class="form-label">Campaign name</label><input class="form-control mb-3" wire:model="campaignName" placeholder="Semester 1 Tutor Evaluation">
                <label class="form-label">Academic semester</label><select class="form-select mb-3" wire:model="semesterId"><option value="">Select semester</option>@foreach($semesters as $semester)<option value="{{ $semester->id }}">{{ $semester->name }} ({{ $semester->academicYear?->name }})</option>@endforeach</select>
                <label class="form-label">Course/tutor targeting</label>
                <select class="form-select mb-3" wire:model.live="targetAll"><option value="1">All eligible course/tutor pairs</option><option value="0">Selected course/tutor pairs only</option></select>
                <div class="mb-3" wire:key="target-preview-{{ $semesterId }}">
                    <p class="text-muted small mb-2">Eligible assignments for the selected semester (approved registrations only). Lecturer assignments currently come from the course-level mapping, not historical semester records.</p>
                    @forelse($eligiblePairs as $pair)
                        <label class="d-flex align-items-center gap-2 mb-2">
                            @if(! $targetAll)<input type="checkbox" class="form-check-input" wire:model="selectedTargets" value="{{ $pair->subject_id }}:{{ $pair->lecturer_id }}">@endif
                            <span>{{ $pair->subject_name }} — {{ $pair->lecturer_name }} <span class="text-muted">({{ $pair->student_count }} eligible students)</span></span>
                        </label>
                    @empty <p class="text-muted small">Select a semester to preview assignments, or no eligible assignments exist.</p> @endforelse
                    @error('selectedTargets')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <label class="form-label">Published form version</label><select class="form-select mb-3" wire:model="versionId"><option value="">Select a version</option>@foreach($versions as $version)<option value="{{ $version->id }}">{{ $version->form->name }} — v{{ $version->version }}</option>@endforeach</select>
                <div class="row g-3 mb-3"><div class="col-md-6"><label class="form-label">Opens (optional)</label><input type="datetime-local" class="form-control" wire:model="opensAt"></div><div class="col-md-6"><label class="form-label">Closes (optional)</label><input type="datetime-local" class="form-control" wire:model="closesAt"></div></div>
                <div class="mb-3"><label class="form-label">Minimum responses before reporting</label><input type="number" min="5" max="100" class="form-control" wire:model="reportMinimum"></div>
                <label class="form-check mb-3"><input type="checkbox" class="form-check-input" wire:model="campaignActive"><span class="form-check-label">Activate immediately (subject to date window)</span></label>
                <label class="form-check mb-3"><input type="checkbox" class="form-check-input" wire:model="campaignRequired"><span class="form-check-label">Mark as required (future access policies; does not block features yet)</span></label>
                <button class="btn btn-primary" wire:click="createCampaign">Create campaign</button>
            </div></div>
            <div class="card"><div class="card-header"><h3 class="card-title">Published campaigns</h3></div><div class="card-body">
                @forelse($campaigns as $campaign)<div class="border rounded p-3 mb-3"><div class="d-flex justify-content-between"><strong>{{ $campaign->name }}</strong><span class="badge badge-light-{{ $campaign->is_active ? 'success' : 'secondary' }}">{{ $campaign->is_active ? 'Active' : 'Paused' }}</span></div><div class="text-muted small mb-2">{{ $campaign->semester?->name }} ({{ $campaign->semester?->academicYear?->name }}) — {{ $campaign->formVersion?->form?->name }} v{{ $campaign->formVersion?->version }}</div><button type="button" class="btn btn-sm btn-light-primary" wire:click="toggleCampaign({{ $campaign->id }})">{{ $campaign->is_active ? 'Pause' : 'Activate' }}</button></div>@empty <p class="text-muted">No campaigns yet.</p>@endforelse
            </div></div>
        </div>
    </div>
    <div class="card mt-5"><div class="card-header"><h3 class="card-title">Aggregated rating reports</h3></div><div class="card-body">
        <p class="text-muted">Only aggregated ratings are shown when the minimum response threshold is met. Student identities and individual submissions are never displayed.</p>
        <div class="row g-3 mb-4"><div class="col-md-4"><label class="form-label">Campaign</label><select class="form-select" wire:model.live="reportCampaignId"><option value="">Choose campaign</option>@foreach($campaigns as $campaign)<option value="{{ $campaign->id }}">{{ $campaign->name }}</option>@endforeach</select></div><div class="col-md-4"><label class="form-label">Tutor user ID</label><input type="number" min="1" class="form-control" wire:model.live.debounce.400ms="reportLecturerId"></div><div class="col-md-4"><label class="form-label">Subject ID</label><input type="number" min="1" class="form-control" wire:model.live.debounce.400ms="reportSubjectId"></div></div>
        @if($report)
            <p><strong>{{ $report['count'] }}</strong> completed evaluations.</p>
            @if($report['visible'])<table class="table"><thead><tr><th>Rating question</th><th>Average</th><th>Answers</th></tr></thead><tbody>@foreach($report['statistics'] as $item)<tr><td>{{ $item['label'] }}</td><td>{{ $item['average'] ?? '—' }}</td><td>{{ $item['count'] }}</td></tr>@endforeach</tbody></table>
            @else <div class="alert alert-info">Results are withheld until at least {{ $report['minimum'] }} submissions have been received for this tutor and subject.</div>@endif
        @endif
    </div></div>
</div>
