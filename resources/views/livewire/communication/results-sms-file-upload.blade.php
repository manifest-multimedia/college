<div @if($batch && in_array($batch->status, ['validating', 'processing', 'queued'], true)) wire:poll.5s @endif>
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

    @if(session('success')) <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}</div> @endif
    @if(session('error')) <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}</div> @endif

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
                    <div class="alert alert-danger mb-4">{{ $batch->failure_reason }}</div>
                    <button class="btn btn-light-warning" wire:click="retryValidation" wire:loading.attr="disabled">Retry Validation</button>
                @else
                    @php
                        $filterMap = [
                            'total_rows' => ['label' => 'Total rows', 'filter' => 'all'],
                            'ready_rows' => ['label' => 'Ready rows', 'filter' => 'ready'],
                            'skipped_rows' => ['label' => 'Skipped', 'filter' => 'skipped'],
                            'missing_number_rows' => ['label' => 'Missing numbers', 'filter' => 'missing_number'],
                            'missing_student_rows' => ['label' => 'Missing students', 'filter' => 'missing_student'],
                            'duplicate_id_rows' => ['label' => 'Duplicate IDs', 'filter' => 'duplicate_id'],
                            'pending_review_rows' => ['label' => 'Pending review', 'filter' => 'pending_review'],
                            'sent_rows' => ['label' => 'Sent', 'filter' => 'all'],
                            'failed_rows' => ['label' => 'Failed', 'filter' => 'all'],
                        ];
                    @endphp
                    <div class="row g-3 mb-5">
                        @foreach($filterMap as $key => $meta)
                            @php
                                $count = (int) ($batch->$key ?? 0);
                                $isActive = $rowFilter === $meta['filter'];
                                $isAlert = in_array($key, ['missing_number_rows', 'skipped_rows']) && $count > 0;
                            @endphp
                            <div class="col-sm-6 col-lg-3">
                                <div 
                                    wire:click="filterBy('{{ $meta['filter'] }}')" 
                                    class="border rounded p-3 {{ $isActive ? 'border-primary bg-light-primary shadow-sm' : ($isAlert ? 'border-danger bg-light-danger' : 'bg-body') }}"
                                    style="cursor: pointer; transition: all 0.2s ease;"
                                    title="Click to view {{ strtolower($meta['label']) }}"
                                >
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="text-muted small fw-semibold">{{ $meta['label'] }}</span>
                                        @if($isAlert)
                                            <span class="badge badge-danger badge-sm">Action needed</span>
                                        @elseif($isActive)
                                            <span class="badge badge-primary badge-sm">Active filter</span>
                                        @endif
                                    </div>
                                    <div class="fs-2 fw-bold {{ $isAlert ? 'text-danger' : ($isActive ? 'text-primary' : 'text-gray-900') }}">
                                        {{ number_format($count) }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @php
                        $queuedCount = \App\Models\ResultsSmsUploadRow::where('batch_id', $batch->id)->where('status', 'queued')->count();
                    @endphp

                    @if($batch->status === 'processing')
                        <div class="alert alert-info d-flex align-items-center mb-4">
                            <span class="spinner-border spinner-border-sm me-3"></span>
                            <div>
                                Sending is currently active: <strong>{{ number_format($batch->sent_rows) }}</strong> sent, <strong>{{ number_format($queuedCount) }}</strong> queued in background.
                                Rate-limited to ensure reliable delivery without provider rejection.
                            </div>
                        </div>
                    @endif

                    <div class="d-flex flex-wrap align-items-center gap-2 mb-4">
                        <a class="btn btn-light-primary" href="{{ route('communication.results-sms.report', $batch->public_id) }}">
                            <i class="fas fa-download me-1"></i> Download validation / delivery report
                        </a>
                        @if($batch->status === 'validated')
                            <button class="btn btn-light-warning" wire:click="retryValidation" wire:loading.attr="disabled">
                                <i class="fas fa-sync-alt me-1"></i> Revalidate Preview
                            </button>
                        @endif
                        @if($queuedCount > 0 && in_array($batch->status, ['processing', 'queued', 'validated', 'completed'], true))
                            <button class="btn btn-primary" wire:click="resumeSending" wire:loading.attr="disabled">
                                <i class="fas fa-play me-1"></i> Resume Sending ({{ $queuedCount }} remaining)
                            </button>
                        @endif
                        @if($batch->failed_rows > 0 && in_array($batch->status, ['completed', 'processing'], true))
                            <button class="btn btn-warning" wire:click="retryFailed" wire:loading.attr="disabled">
                                <i class="fas fa-redo me-1"></i> Retry Failed Messages ({{ $batch->failed_rows }})
                            </button>
                        @endif
                    </div>

                    @if($batch->status === 'validated')
                        <div class="border rounded p-4 mb-5 bg-light">
                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" id="results-sms-confirm" wire:model="confirmed">
                                <label class="form-check-label" for="results-sms-confirm">
                                    I have reviewed this preview and confirm that the <strong>{{ $batch->ready_rows }}</strong> Ready messages should be sent.
                                </label>
                            </div>
                            @error('confirmed') <div class="text-danger mb-3">{{ $message }}</div> @enderror
                            <button class="btn btn-success" wire:click="confirmAndSend" wire:loading.attr="disabled">
                                <i class="fas fa-paper-plane me-1"></i> Send Ready Messages
                            </button>
                        </div>
                    @endif

                    <!-- Batch Rows Inspection Table -->
                    @if($rows)
                        <div class="card border border-gray-300 shadow-sm mt-5" id="batch-rows-section">
                            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-3 py-4">
                                <div>
                                    <h3 class="card-title fw-bold mb-1">
                                        <i class="fas fa-users text-primary me-2"></i>Students in this Batch
                                    </h3>
                                    <span class="text-muted small">
                                        Showing {{ $rows->total() }} rows
                                        @if($rowFilter !== 'all')
                                            (filtered: <strong>{{ str_replace('_', ' ', $rowFilter) }}</strong>)
                                        @endif
                                    </span>
                                </div>
                                
                                <div class="d-flex flex-wrap align-items-center gap-2">
                                    <div class="position-relative">
                                        <input 
                                            type="text" 
                                            class="form-control form-control-sm form-control-solid w-200px w-md-250px" 
                                            placeholder="Search student, name, row #..." 
                                            wire:model.live.debounce.300ms="rowSearch"
                                        >
                                    </div>

                                    <div class="btn-group btn-group-sm" role="group">
                                        <button type="button" class="btn btn-sm {{ $rowFilter === 'all' ? 'btn-primary' : 'btn-light' }}" wire:click="filterBy('all')">
                                            All ({{ $batch->total_rows }})
                                        </button>
                                        @if($batch->missing_number_rows > 0)
                                            <button type="button" class="btn btn-sm {{ $rowFilter === 'missing_number' ? 'btn-danger' : 'btn-light-danger' }}" wire:click="filterBy('missing_number')">
                                                <i class="fas fa-exclamation-triangle me-1"></i> Missing Contact ({{ $batch->missing_number_rows }})
                                            </button>
                                        @endif
                                        @if($batch->missing_student_rows > 0)
                                            <button type="button" class="btn btn-sm {{ $rowFilter === 'missing_student' ? 'btn-warning' : 'btn-light-warning' }}" wire:click="filterBy('missing_student')">
                                                Missing Student ({{ $batch->missing_student_rows }})
                                            </button>
                                        @endif
                                        <button type="button" class="btn btn-sm {{ $rowFilter === 'skipped' ? 'btn-danger' : 'btn-light' }}" wire:click="filterBy('skipped')">
                                            Skipped ({{ $batch->skipped_rows }})
                                        </button>
                                        <button type="button" class="btn btn-sm {{ $rowFilter === 'ready' ? 'btn-success' : 'btn-light' }}" wire:click="filterBy('ready')">
                                            Ready ({{ $batch->ready_rows }})
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-row-dashed table-row-gray-300 align-middle gy-3 px-4 mb-0">
                                    <thead class="border-gray-200 fs-7 fw-bold text-muted text-uppercase bg-light">
                                        <tr>
                                            <th class="ps-4 w-60px">Row</th>
                                            <th class="min-w-160px">Student ID</th>
                                            <th class="min-w-200px">Student Details</th>
                                            <th class="min-w-150px">Contact Number</th>
                                            <th class="min-w-220px">Status / Safe Reason</th>
                                            <th class="text-end pe-4 min-w-180px">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="fs-6 text-gray-700">
                                        @forelse($rows as $r)
                                            <tr class="{{ $r->status === 'skipped' ? 'bg-light-danger bg-opacity-25' : '' }}">
                                                <td class="ps-4 fw-bold text-muted">{{ $r->row_number }}</td>
                                                <td>
                                                    <span class="badge badge-light fw-bold text-dark fs-7 font-monospace">
                                                        {{ $r->student_id }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @if($r->student)
                                                        <div class="fw-bold text-gray-800">{{ $r->student->full_name }}</div>
                                                        <div class="text-muted small">
                                                            {{ $r->student->collegeClass?->name ?? 'Class not assigned' }}
                                                        </div>
                                                    @else
                                                        <span class="text-danger small fst-italic">
                                                            <i class="fas fa-user-slash me-1"></i> No matching active student
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($r->status === 'ready' && $r->masked_recipient)
                                                        <span class="badge badge-light-success fw-semibold">
                                                            <i class="fas fa-check-circle text-success me-1"></i> {{ $r->masked_recipient }}
                                                        </span>
                                                    @elseif($r->student?->mobile_number)
                                                        <div>
                                                            <span class="badge badge-light-danger text-danger fw-bold font-monospace">
                                                                {{ $r->student->mobile_number }}
                                                            </span>
                                                            <div class="text-danger small mt-1">Invalid phone format</div>
                                                        </div>
                                                    @else
                                                        <span class="badge badge-light-danger">
                                                            <i class="fas fa-phone-slash me-1"></i> Missing contact
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div>
                                                        <span class="badge badge-light-{{ $r->status === 'ready' ? 'success' : ($r->status === 'skipped' ? 'danger' : ($r->status === 'pending_review' ? 'info' : 'warning')) }} fw-bold">
                                                            {{ ucfirst(str_replace('_', ' ', $r->status)) }}
                                                        </span>
                                                        @if($r->safe_reason)
                                                            <div class="small {{ $r->status === 'skipped' ? 'text-danger fw-semibold' : 'text-muted' }} mt-1">
                                                                {{ $r->safe_reason }}
                                                            </div>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="text-end pe-4">
                                                    <div class="d-flex justify-content-end align-items-center gap-1">
                                                        @if($r->student_record_id)
                                                            <button 
                                                                type="button" 
                                                                class="btn btn-sm {{ $r->status === 'skipped' ? 'btn-danger' : 'btn-light-primary' }}" 
                                                                wire:click="openRectifyModal({{ $r->id }})"
                                                                title="Click to find details and rectify contact"
                                                            >
                                                                <i class="fas fa-edit me-1"></i> Rectify Contact
                                                            </button>
                                                            <a 
                                                                href="{{ route('students.edit', $r->student_record_id) }}" 
                                                                target="_blank" 
                                                                class="btn btn-sm btn-icon btn-light-primary" 
                                                                title="Full student edit in new tab"
                                                            >
                                                                <i class="fas fa-external-link-alt"></i>
                                                            </a>
                                                            <a 
                                                                href="{{ route('students.show', $r->student_record_id) }}" 
                                                                target="_blank" 
                                                                class="btn btn-sm btn-icon btn-light-secondary" 
                                                                title="View student profile in new tab"
                                                            >
                                                                <i class="fas fa-user"></i>
                                                            </a>
                                                        @else
                                                            <a 
                                                                href="{{ route('students.create') }}" 
                                                                target="_blank" 
                                                                class="btn btn-sm btn-light-warning" 
                                                                title="Register missing student record"
                                                            >
                                                                <i class="fas fa-user-plus me-1"></i> Register Student
                                                            </a>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center py-6 text-muted">
                                                    No student rows match the selected filter.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            @if($rows->hasPages())
                                <div class="card-footer py-3 d-flex justify-content-between align-items-center">
                                    <div class="text-muted small">
                                        Showing {{ $rows->firstItem() ?? 0 }} to {{ $rows->lastItem() ?? 0 }} of {{ $rows->total() }} rows
                                    </div>
                                    <div>
                                        {{ $rows->links() }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                @endif
            </div>
        </div>
    @endif

    <!-- Rectify Contact Modal -->
    @if($showRectifyModal)
        <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background-color: rgba(0, 0, 0, 0.5);">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content shadow-lg border-0">
                    <div class="modal-header bg-primary py-4 text-white">
                        <h4 class="modal-title text-white fw-bold">
                            <i class="fas fa-user-edit me-2 text-white"></i>Rectify Student Contact Number
                        </h4>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeRectifyModal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-6">
                        <!-- Student Summary Box -->
                        <div class="card bg-light border border-gray-300 p-4 mb-5">
                            <div class="row gy-3">
                                <div class="col-sm-6">
                                    <div class="text-muted small fw-semibold">Student Name</div>
                                    <div class="fs-5 fw-bold text-gray-900">{{ $rectifyingStudentName }}</div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="text-muted small fw-semibold">Student ID / Reg. No.</div>
                                    <div class="fs-5 fw-bold font-monospace text-primary">{{ $rectifyingStudentId }}</div>
                                </div>
                                @if($rectifyingClassName)
                                    <div class="col-sm-6">
                                        <div class="text-muted small fw-semibold">Class / Program</div>
                                        <div class="fw-semibold text-gray-800">{{ $rectifyingClassName }}</div>
                                    </div>
                                @endif
                                <div class="col-sm-6">
                                    <div class="text-muted small fw-semibold">Current Stored Phone</div>
                                    <div class="fw-bold font-monospace {{ empty($rectifyingCurrentPhone) ? 'text-muted fst-italic' : 'text-danger' }}">
                                        {{ $rectifyingCurrentPhone ?: 'No phone recorded' }}
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="text-muted small fw-semibold">Validation Issue</div>
                                    <div class="badge badge-light-danger text-danger fw-semibold text-wrap text-start fs-7 p-2">
                                        <i class="fas fa-exclamation-triangle text-danger me-1"></i> {{ $rectifyReason }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Update Form -->
                        <form wire:submit.prevent="saveRectifiedContact">
                            <div class="mb-4">
                                <label class="form-label fw-bold required fs-6">Correct Ghanaian Mobile Phone Number</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-phone text-primary"></i></span>
                                    <input 
                                        type="text" 
                                        class="form-control form-control-lg @error('newMobileNumber') is-invalid @enderror" 
                                        wire:model="newMobileNumber" 
                                        placeholder="e.g. 0598036772 or 0244123456" 
                                        autofocus
                                    >
                                    @error('newMobileNumber')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-text mt-2 text-muted">
                                    Enter the student's valid mobile number (e.g. <code>0598036772</code> or <code>+233598036772</code>). Saving will update the student record in the database and immediately re-evaluate the batch!
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-6 pt-4 border-top">
                                <div>
                                    @if($rectifyingStudentDbId)
                                        <a href="{{ route('students.edit', $rectifyingStudentDbId) }}" target="_blank" class="btn btn-light-info btn-sm">
                                            <i class="fas fa-external-link-alt me-1"></i> Open Full Edit Page
                                        </a>
                                    @endif
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-light" wire:click="closeRectifyModal">Cancel</button>
                                    <button type="submit" class="btn btn-success" wire:loading.attr="disabled">
                                        <span wire:loading.remove wire:target="saveRectifiedContact">
                                            <i class="fas fa-check-circle me-1"></i> Save & Revalidate Batch
                                        </span>
                                        <span wire:loading wire:target="saveRectifiedContact">
                                            <span class="spinner-border spinner-border-sm me-1"></span> Saving & Revalidating...
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-header"><h3 class="card-title">Recent Result-Message Batches</h3></div>
        <div class="table-responsive">
            <table class="table table-row-dashed table-row-gray-300 align-middle gy-4 mb-0">
                <thead class="border-gray-200 fs-7 fw-bold text-muted text-uppercase">
                    <tr>
                        <th class="ps-6 min-w-250px">File</th>
                        <th class="min-w-125px">Status</th>
                        <th class="text-end min-w-80px">Rows</th>
                        <th class="text-center min-w-175px">Ready / Sent / Failed</th>
                        <th class="min-w-150px">Created</th>
                        <th class="text-end pe-6 min-w-80px">Action</th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-700">
            @forelse($recentBatches as $item)
                <tr>
                    <td class="ps-6"><span class="d-inline-block text-truncate mw-250px" title="{{ $item->original_filename }}">{{ $item->original_filename }}</span></td>
                    <td><span class="badge badge-light-{{ in_array($item->status, ['completed', 'validated']) ? 'success' : ($item->status === 'failed' ? 'danger' : 'warning') }}">{{ str_replace('_', ' ', ucfirst($item->status)) }}</span></td>
                    <td class="text-end">{{ number_format($item->total_rows) }}</td>
                    <td class="text-center"><span class="text-success">{{ number_format($item->ready_rows) }}</span><span class="text-muted mx-1">/</span><span class="text-primary">{{ number_format($item->sent_rows) }}</span><span class="text-muted mx-1">/</span><span class="text-danger">{{ number_format($item->failed_rows) }}</span></td>
                    <td class="text-muted text-nowrap">{{ $item->created_at->format('d M Y, H:i') }}</td>
                    <td class="text-end pe-6"><a class="btn btn-sm btn-light-primary" href="{{ route('communication.results-sms.upload', $item->public_id) }}">View</a></td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-8">No results SMS uploads yet.</td></tr>
            @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

