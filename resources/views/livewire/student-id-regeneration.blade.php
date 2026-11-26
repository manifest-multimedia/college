<div>
    <div class="mb-5">
        <h1 class="fw-bold fs-3">Student ID Regeneration</h1>
        <p class="text-muted">Preview and regenerate IDs for a cohort or selected students. Review operation history and restore batches when necessary.</p>
    </div>
    <!-- Alert Notifications -->
    @if(session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm mb-5" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-5" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session()->has('info'))
        <div class="alert alert-info alert-dismissible fade show shadow-sm mb-5" role="alert">
            <i class="fas fa-info-circle me-2"></i>
            {{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border shadow-none">
        <div class="card-body">
            <div class="d-flex flex-wrap gap-3 align-items-end mb-4">
                <div class="flex-grow-1">
                    <label class="form-label" for="id-cohort">Cohort</label>
                    <select id="id-cohort" class="form-select" wire:model.live="cohortFilter">
                        <option value="">All cohorts</option>
                        @foreach($cohorts as $cohort)
                            <option value="{{ $cohort->id }}">{{ $cohort->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-grow-1">
                    <label class="form-label" for="id-program">Programme</label>
                    <select id="id-program" class="form-select" wire:model.live="programFilter">
                        <option value="">All programmes</option>
                        @foreach($programs as $program)
                            <option value="{{ $program->id }}">{{ $program->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-grow-1">
                    <label class="form-label" for="id-search">Search students</label>
                    <input id="id-search" class="form-control" wire:model.live.debounce.400ms="search">
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2 align-items-center mb-4">
                <span class="fw-semibold me-auto">{{ count($selectedStudents) }} students selected</span>
                <button class="btn btn-sm btn-light" wire:click="$set('selectedStudents', [])">Clear selection</button>
                <button class="btn btn-sm btn-light-primary" wire:click="openRegenerationHistory">ID History</button>
                <button class="btn btn-sm btn-light-warning" wire:click="confirmIdRegeneration('cohort')" @disabled(!$cohortFilter)>Regenerate cohort IDs</button>
                <button class="btn btn-sm btn-warning" wire:click="confirmIdRegeneration('selected')" @disabled(!$selectedStudents)>Regenerate selected IDs</button>
            </div>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th><input type="checkbox" class="form-check-input" aria-label="Select all filtered students" wire:model.live="selectAll"></th><th>Student ID</th><th>Name</th></tr></thead>
                    <tbody>
                    @forelse($students as $student)
                        <tr wire:key="id-student-{{ $student->id }}">
                            <td><input type="checkbox" class="form-check-input" aria-label="Select student {{ $student->id }}" value="{{ $student->id }}" wire:model.live="selectedStudents"></td>
                            <td>{{ $student->student_id }}</td>
                            <td>{{ $student->last_name }} {{ $student->first_name }} {{ $student->other_name }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-muted">No students found.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            {{ $students->links() }}
        </div>
    </div>
    @if($showRegenerationHistory)
    <div class="modal fade show" style="display:block;background-color:rgba(0,0,0,.5)" role="dialog" aria-modal="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Student ID regeneration history</h5><button class="btn-close" wire:click="$set('showRegenerationHistory', false)" aria-label="Close"></button></div>
                <div class="modal-body">
                    <div wire:poll.5s class="mb-4">
                        <h6 class="fw-bold">Recent operations</h6>
                        <div class="table-responsive"><table class="table table-sm align-middle"><thead><tr><th>Cohort</th><th>Requested</th><th>Status</th><th>Committed students</th><th>Details</th></tr></thead><tbody>
                        @forelse($this->regenerationOperations as $operation)
                            <tr wire:key="operation-{{ $operation->id }}"><td>{{ $operation->cohort_name }}</td><td>{{ $operation->created_at }}</td><td>{{ ucfirst($operation->status) }}</td><td>{{ $operation->processed }} / {{ $operation->total }}</td><td>{{ $operation->message ?? ($operation->status === 'processing' ? 'Validating and applying changes atomically' : '') }}</td></tr>
                        @empty<tr><td colspan="5" class="text-muted">No operations yet.</td></tr>@endforelse
                        </tbody></table></div>
                    </div>
                    <p class="text-muted">Most recent 100 committed batches.</p>
                    @if($restoreBatchId)
                        <div class="alert alert-warning">Restore the original IDs for this entire batch? Restoration is blocked if student records have changed.
                            <button class="btn btn-sm btn-danger ms-2" wire:click="restoreRegenerationBatch" wire:loading.attr="disabled">Confirm restoration</button>
                        </div>
                    @endif
                    <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Date</th><th>Cohort</th><th>Initiated by</th><th>Mode</th><th>Changed</th><th>Status</th><th>Actions</th></tr></thead><tbody>
                    @forelse($this->regenerationBatches as $batch)
                        <tr wire:key="batch-{{ $batch->id }}"><td>{{ $batch->created_at }}</td><td>{{ $batch->cohort_name ?? 'Deleted cohort' }}</td><td>{{ $batch->actor_name ?? 'Unknown' }}</td><td>{{ str_replace('_', ' ', $batch->mode) }}</td><td>{{ $batch->changed_count }}</td><td>{{ $batch->restored_at ? 'Restored' : 'Committed' }}</td><td><button class="btn btn-sm btn-light-primary" wire:click="downloadRegenerationMapping('{{ $batch->id }}')">CSV</button>
                        @if(!$batch->restored_at && $batch->changed_count && auth()->user()?->hasAnyRole(['System', 'Super Admin']))<button class="btn btn-sm btn-light-danger" wire:click="confirmRestoreBatch('{{ $batch->id }}')">Restore</button>@endif</td></tr>
                    @empty<tr><td colspan="7" class="text-center text-muted">No regeneration history yet.</td></tr>@endforelse
                    </tbody></table></div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- ID Regeneration Modal -->
    @if($confirmingIdRegeneration)
    <div class="modal fade show" tabindex="-1" style="display:block;background-color:rgba(0,0,0,.5)" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header py-3 px-4">
                    <h5 class="modal-title fw-bold">Regenerate Student IDs</h5>
                    <button type="button" class="btn-close" wire:click="cancelIdRegeneration" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-warning">@if($regenerationSelectedIds !== null) Only the {{ count($regenerationSelectedIds) }} selected students will be included. @else All students in the selected cohort will be included, regardless of other filters. @endif Review the proposed IDs before confirming.</div>
                    <label for="regeneration-mode" class="form-label fw-semibold">Regeneration mode</label>
                    <select id="regeneration-mode" class="form-select mb-3" wire:model.live="regenerationMode">
                        <option value="preserve_sequence">Preserve existing sequence (recommended)</option>
                        <option value="resequence">Regenerate and resequence (assign new numbers)</option>
                    </select>
                    @if($regenerationMode === 'resequence')
                        <div class="alert alert-warning">Resequencing assigns new numbers in alphabetical order, including students whose IDs were manually edited. Choose Preserve existing sequence for a format-only change.</div>
                    @endif
                    @if($regenerationMode === 'preserve_sequence')
                        <label for="source-pattern" class="form-label fw-semibold">Original ID pattern</label>
                        <input id="source-pattern" class="form-control" wire:model.live.debounce.400ms="regenerationSourcePattern" placeholder="Original pattern with sequence token">
                        <div class="form-text mb-3">Enter the previous pattern, not the new branding pattern. For example, DN/26/0021 uses {PROGRAM}/{YEAR_SIMPLE}/{SEQUENCE_4}. The sequence 0021 will be retained.</div>
                    @endif
                    @error('regenerationSourcePattern') <div class="text-danger">{{ $message }}</div> @enderror
                    <button class="btn btn-primary btn-sm mb-3" wire:click="previewRegeneration" wire:loading.attr="disabled">Generate preview</button>
                    <span wire:loading wire:target="previewRegeneration" class="text-muted ms-2">Preparing preview…</span>
                    @if(!empty($regenerationPreview))
                        @if(!empty($regenerationPreview['errors']))
                            <div class="alert alert-danger">Preview blocked. {{ $regenerationPreview['message'] ?? 'Unable to generate IDs.' }}</div>
                        @else
                            <p class="fw-semibold">{{ $regenerationPreview['success'] }} students · {{ collect($regenerationPreview['updated_students'])->filter(fn($row) => $row['old_id'] !== $row['new_id'])->count() }} changed · No detected conflicts</p>
                            <div class="table-responsive" style="max-height:350px;overflow:auto">
                                <table class="table table-sm align-middle">
                                    <thead><tr><th>Student</th><th>Current ID</th><th>Proposed ID</th></tr></thead>
                                    <tbody>
                                    @foreach($regenerationPreview['updated_students'] as $row)
                                        <tr wire:key="regeneration-preview-{{ $row['id'] }}"><td>{{ $row['name'] }}</td><td>{{ $row['old_id'] ?? 'Not assigned' }}</td><td>{{ $row['new_id'] }}</td></tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    @endif
                </div>
                <div class="modal-footer bg-light py-3 px-4">
                    <button type="button" class="btn btn-sm btn-light-secondary border" wire:click="cancelIdRegeneration">Cancel</button>
                    <button type="button" class="btn btn-sm btn-warning fw-semibold" wire:click="regenerateIds" wire:loading.attr="disabled" @disabled(empty($regenerationPreview['hash']) || empty($regenerationPreview['success']) || !empty($regenerationPreview['errors']))>Confirm regeneration</button>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>
