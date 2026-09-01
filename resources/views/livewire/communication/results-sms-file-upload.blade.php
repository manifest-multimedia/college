<div wire:poll.5s>
    <div class="card mb-5">
        <div class="card-header"><h3 class="card-title">Results SMS File Upload</h3></div>
        <div class="card-body">
            <div class="alert alert-info">
                Upload a <strong>.xlsx</strong> or <strong>.csv</strong> file with <code>Student ID</code> and <code>SMS Message</code> columns. A <code>Status</code> column is optional; when present, only rows marked <strong>Ready</strong> can be sent. Phone numbers from the file are ignored.
            </div>
            <form wire:submit.prevent="validateUpload">
                <div class="row align-items-end">
                    <div class="col-md-8">
                        <label class="form-label required">Results message file</label>
                        <input type="file" class="form-control @error('upload') is-invalid @enderror" wire:model="upload" accept=".xlsx,.csv">
                        <div class="form-text">Maximum 10 MB and 10,000 data rows. Keep Student ID cells formatted as text to preserve leading zeroes.</div>
                        @error('upload') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4"><button class="btn btn-primary" type="submit" wire:loading.attr="disabled" wire:target="upload,validateUpload">Validate Upload</button></div>
                </div>
            </form>
        </div>
    </div>

    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if(session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif

    @if($batch)
        <div class="card mb-5">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div><h3 class="card-title mb-0">Batch preview</h3><span class="text-muted small">{{ $batch->original_filename }}</span></div>
                <span class="badge badge-light-{{ in_array($batch->status, ['completed', 'validated']) ? 'success' : ($batch->status === 'failed' ? 'danger' : 'warning') }}">{{ str_replace('_', ' ', ucfirst($batch->status)) }}</span>
            </div>
            <div class="card-body">
                @if($batch->status === 'validating')
                    <div class="d-flex align-items-center gap-3"><span class="spinner-border spinner-border-sm"></span> Validating securely in the background. This preview refreshes automatically.</div>
                @elseif($batch->status === 'failed')
                    <div class="alert alert-danger mb-0">{{ $batch->failure_reason }}</div>
                @else
                    <div class="row g-4 mb-5">
                        @foreach(['total_rows' => 'Total rows', 'ready_rows' => 'Ready rows', 'skipped_rows' => 'Skipped', 'pending_review_rows' => 'Pending review', 'missing_student_rows' => 'Missing students', 'missing_number_rows' => 'Missing numbers', 'duplicate_id_rows' => 'Duplicate IDs', 'sent_rows' => 'Sent', 'failed_rows' => 'Failed'] as $key => $label)
                            <div class="col-sm-6 col-lg-3"><div class="border rounded p-3"><div class="text-muted small">{{ $label }}</div><div class="fs-2 fw-bold">{{ $batch->$key }}</div></div></div>
                        @endforeach
                    </div>
                    <a class="btn btn-light-primary me-2" href="{{ route('communication.results-sms.report', $batch->public_id) }}">Download validation / delivery report</a>
                    @if($batch->status === 'validated')
                        <div class="border rounded p-4 mt-5 bg-light">
                            <div class="form-check mb-4"><input class="form-check-input" type="checkbox" id="results-sms-confirm" wire:model="confirmed"><label class="form-check-label" for="results-sms-confirm">I have reviewed this preview and confirm that the <strong>{{ $batch->ready_rows }}</strong> Ready messages should be sent.</label></div>
                            @error('confirmed') <div class="text-danger mb-3">{{ $message }}</div> @enderror
                            <button class="btn btn-success" wire:click="confirmAndSend" wire:loading.attr="disabled">Send Ready Messages</button>
                        </div>
                    @elseif($batch->failed_rows > 0 && in_array($batch->status, ['completed', 'processing'], true))
                        <button class="btn btn-warning ms-2" wire:click="retryFailed" wire:loading.attr="disabled">Retry Failed Messages</button>
                    @endif
                @endif
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-header"><h3 class="card-title">Recent result-message batches</h3></div>
        <div class="table-responsive"><table class="table table-row-dashed align-middle mb-0"><thead><tr><th>File</th><th>Status</th><th>Rows</th><th>Ready / Sent / Failed</th><th>Created</th><th></th></tr></thead><tbody>
            @forelse($recentBatches as $item)
                <tr><td>{{ $item->original_filename }}</td><td>{{ str_replace('_', ' ', ucfirst($item->status)) }}</td><td>{{ $item->total_rows }}</td><td>{{ $item->ready_rows }} / {{ $item->sent_rows }} / {{ $item->failed_rows }}</td><td>{{ $item->created_at->format('d M Y, H:i') }}</td><td><a class="btn btn-sm btn-light" href="{{ route('communication.results-sms.upload', $item->public_id) }}">View</a></td></tr>
            @empty <tr><td colspan="6" class="text-center text-muted py-5">No results SMS uploads yet.</td></tr> @endforelse
        </tbody></table></div>
    </div>
</div>
