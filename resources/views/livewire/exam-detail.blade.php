<div>
    <!-- Success Message -->
    @if(session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    
    <!-- Error Message -->
    @if(session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Exam Header -->
    <div class="card mb-5 mb-xl-10">
        <div class="card-header border-0 d-flex justify-content-between align-items-center">
            <div class="card-title m-0">
                <h3 class="fw-bold m-0">{{ $exam->course ? $exam->course->name : 'Exam Details' }}</h3>
                <span class="badge badge-light-{{ match($exam->status) {
                    'upcoming' => 'primary',
                    'active' => 'success',
                    'completed' => 'info',
                    default => 'secondary'
                } }} mt-2">{{ ucfirst($exam->status) }}</span>
            </div>
            <div class="d-flex gap-2">
                @if(Auth::user()->id === $exam->user_id || 
                    (method_exists(Auth::user(), 'hasRole') && Auth::user()->hasRole(['admin', 'Super Admin', 'System', 'Administrator'])) ||
                    in_array(Auth::user()->role ?? '', ['admin', 'Super Admin', 'System', 'Administrator']))
                    <button class="btn btn-sm btn-primary" wire:click="startEditing">
                        <i class="ki-duotone ki-pencil fs-3">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        Edit Exam
                    </button>
                    <button class="btn btn-sm btn-danger" wire:click="confirmDelete">
                        <i class="ki-duotone ki-trash fs-3">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                            <span class="path4"></span>
                            <span class="path5"></span>
                        </i>
                        Delete
                    </button>
                @endif
            </div>
        </div>
        <div class="card-body border-top p-9">
            <div class="row mb-7">
                <div class="col-lg-6">
                    <h5 class="mb-4">Basic Information</h5>
                    <div class="row mb-3">
                        <label class="col-lg-4 fw-semibold text-muted">Course</label>
                        <div class="col-lg-8">
                            <span class="fw-bold fs-6 text-gray-800">
                                {{ $exam->course ? $exam->course->name : 'N/A' }}
                                @if($exam->course && $exam->course->course_code)
                                    ({{ $exam->course->course_code }})
                                @endif
                            </span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-lg-4 fw-semibold text-muted">Duration</label>
                        <div class="col-lg-8">
                            <span class="fw-bold fs-6 text-gray-800">{{ $exam->duration }} minutes</span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-lg-4 fw-semibold text-muted">Questions Per Session</label>
                        <div class="col-lg-8">
                            <span class="fw-bold fs-6 text-gray-800">{{ $exam->questions_per_session }}</span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-lg-4 fw-semibold text-muted">Total Questions</label>
                        <div class="col-lg-8">
                            <span class="fw-bold fs-6 text-gray-800">{{ $totalQuestions }}</span>
                        </div>
                    </div>

                    @if($exam->passing_percentage)
                        <div class="row mb-3">
                            <label class="col-lg-4 fw-semibold text-muted">Passing Percentage</label>
                            <div class="col-lg-8">
                                <span class="fw-bold fs-6 text-gray-800">{{ $exam->passing_percentage }}%</span>
                            </div>
                        </div>
                    @endif

                    @if((method_exists(Auth::user(), 'hasRole') && Auth::user()->hasRole(['admin', 'Super Admin', 'System', 'Administrator'])) ||
                        in_array(Auth::user()->role ?? '', ['admin', 'Super Admin', 'System', 'Administrator']))
                        <div class="row mb-3">
                            <label class="col-lg-4 fw-semibold text-muted">Exam Password</label>
                            <div class="col-lg-8">
                                <span class="badge badge-light-success fs-6">{{ $exam->password }}</span>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="col-lg-6">
                    <h5 class="mb-4">Schedule & Settings</h5>
                    <div class="row mb-3">
                        <label class="col-lg-4 fw-semibold text-muted">Start Date</label>
                        <div class="col-lg-8">
                            <span class="fw-bold fs-6 text-gray-800">
                                {{ $exam->start_date?->format('M d, Y h:i A') ?? 'Not set' }}
                            </span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-lg-4 fw-semibold text-muted">End Date</label>
                        <div class="col-lg-8">
                            <span class="fw-bold fs-6 text-gray-800">
                                {{ $exam->end_date?->format('M d, Y h:i A') ?? 'Not set' }}
                            </span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-lg-4 fw-semibold text-muted">Class</label>
                        <div class="col-lg-8">
                            <span class="fw-bold fs-6 text-gray-800">
                                @if($exam->course && $exam->course->collegeClass && $exam->course->year && $exam->course->semester)
                                    {{ $exam->course->collegeClass->name }} - {{ $exam->course->year->name }} ({{ $exam->course->semester->name }})
                                @else
                                    No details available
                                @endif
                            </span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-lg-4 fw-semibold text-muted">Created By</label>
                        <div class="col-lg-8">
                            <span class="fw-bold fs-6 text-gray-800">{{ $exam->user ? $exam->user->name : 'Unknown' }}</span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-lg-4 fw-semibold text-muted">Created On</label>
                        <div class="col-lg-8">
                            <span class="fw-bold fs-6 text-gray-800">{{ $exam->created_at->format('M d, Y h:i A') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="separator separator-dashed my-6"></div>
            <div class="d-flex gap-3">
                <a href="{{ route('questionbank.with.slug', $exam->slug ? $exam->slug : $exam->id) }}" class="btn btn-light-primary">
                    <i class="ki-duotone ki-notepad-edit fs-3">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    Manage Question Bank
                </a>
                
                @if((method_exists(Auth::user(), 'hasRole') && Auth::user()->hasRole(['admin', 'Super Admin', 'System', 'Administrator'])) ||
                    in_array(Auth::user()->role ?? '', ['admin', 'Super Admin', 'System', 'Administrator']))
                    <a href="{{ route('exams.edit', $exam->slug ? $exam->slug : $exam->id) }}" class="btn btn-light-info">
                        <i class="ki-duotone ki-pencil fs-3">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        Edit Details
                    </a>
                    
                    <a href="{{ route('exams.preview', $exam) }}" class="btn btn-light-primary">
                        <i class="ki-duotone ki-eye fs-3">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                        Preview Exam
                    </a>
                    
                    <a href="{{ route('exams.results', ['exam_id' => $exam->id]) }}" class="btn btn-light-success">
                        <i class="ki-duotone ki-chart fs-3">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        View Results
                    </a>
                @endif
            </div>
        </div>
    </div>

    <!-- Active Participants Section (Only visible when exam is active) -->
    @if($exam->status === 'active')
        <div class="mb-5 mb-xl-10">
            @livewire('active-exam-sessions', ['exam' => $exam, 'expectedParticipants' => $expectedParticipants ?? 0], key('active-sessions-'.$exam->id))
        </div>
    @endif

    <!-- Question Sets Section -->
    @if($exam->questionSets && $exam->questionSets->count() > 0)
        <div class="card mb-5 mb-xl-10">
            <div class="card-header border-0">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold fs-3 mb-1">Question Sets</span>
                    <span class="text-muted mt-1 fw-semibold fs-7">{{ $exam->questionSets->count() }} set(s) assigned</span>
                </h3>
            </div>
            <div class="card-body py-3">
                <div class="table-responsive">
                    <table class="table align-middle gs-0 gy-4">
                        <thead>
                            <tr class="fw-bold text-muted bg-light">
                                <th class="ps-4 min-w-200px rounded-start">Question Set</th>
                                <th class="min-w-100px">Total Questions</th>
                                <th class="min-w-100px">Questions to Pick</th>
                                <th class="min-w-100px">Shuffle</th>
                                <th class="min-w-100px rounded-end">Difficulty</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($exam->questionSets as $set)
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="d-flex justify-content-start flex-column">
                                                <span class="text-dark fw-bold fs-6">{{ $set->name }}</span>
                                                @if($set->description)
                                                    <span class="text-muted fw-semibold text-muted d-block fs-7">
                                                        {{ Str::limit($set->description, 50) }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-dark fw-bold d-block fs-6">{{ $set->questions->count() }}</span>
                                    </td>
                                    <td>
                                        <span class="text-dark fw-bold d-block fs-6">
                                            {{ $set->pivot->questions_to_pick > 0 ? $set->pivot->questions_to_pick : 'All' }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($set->pivot->shuffle_questions)
                                            <span class="badge badge-light-success">Yes</span>
                                        @else
                                            <span class="badge badge-light-secondary">No</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-light-{{ match($set->difficulty_level) {
                                            'easy' => 'success',
                                            'hard' => 'danger',
                                            default => 'warning',
                                        } }}">
                                            {{ ucfirst($set->difficulty_level) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- Edit Modal -->
    <div class="modal fade" id="editExamModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Exam</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" wire:click="cancelEditing"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="updateExam">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="examDuration" class="form-label required">Duration (minutes)</label>
                                <input type="number" class="form-control @error('examDuration') is-invalid @enderror" id="examDuration" wire:model="examDuration">
                                @error('examDuration') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="questionsPerSession" class="form-label required">Questions Per Session</label>
                                <input type="number" class="form-control @error('questionsPerSession') is-invalid @enderror" id="questionsPerSession" wire:model="questionsPerSession">
                                @error('questionsPerSession') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="startDate" class="form-label">Start Date & Time</label>
                                <input type="datetime-local" class="form-control @error('startDate') is-invalid @enderror" id="startDate" wire:model="startDate">
                                @error('startDate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="endDate" class="form-label">End Date & Time</label>
                                <input type="datetime-local" class="form-control @error('endDate') is-invalid @enderror" id="endDate" wire:model="endDate">
                                @error('endDate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="passingPercentage" class="form-label">Passing Percentage</label>
                                <input type="number" step="0.01" class="form-control @error('passingPercentage') is-invalid @enderror" id="passingPercentage" wire:model="passingPercentage">
                                @error('passingPercentage') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label required">Status</label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status" wire:model="status">
                                    @foreach($statusOptions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="modal-footer px-0 pb-0">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal" wire:click="cancelEditing">Cancel</button>
                            <button type="submit" class="btn btn-primary">
                                <span class="indicator-label">Save Changes</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteConfirmationModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" wire:click="cancelDelete"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this exam? This action cannot be undone.</p>
                    <div class="alert alert-warning">
                        <i class="ki-duotone ki-information-5 fs-2x text-warning me-4">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                        All questions, responses, and results associated with this exam will be permanently deleted.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" wire:click="cancelDelete">Cancel</button>
                    <button type="button" class="btn btn-danger" wire:click="deleteExam">Delete Exam</button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('livewire:init', function () {
        let editModal;
        let deleteModal;

        Livewire.on('startEditing', () => {
            if (!editModal) {
                editModal = new bootstrap.Modal(document.getElementById('editExamModal'));
            }
            editModal.show();
        });

        Livewire.on('examUpdated', () => {
            if (editModal) {
                editModal.hide();
            }
        });

        Livewire.on('confirmDelete', () => {
            if (!deleteModal) {
                deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmationModal'));
            }
            deleteModal.show();
        });

        Livewire.on('examDeleted', () => {
            if (deleteModal) {
                deleteModal.hide();
            }
        });
    });
</script>
@endpush
