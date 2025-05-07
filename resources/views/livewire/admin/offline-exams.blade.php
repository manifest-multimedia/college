<div>
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="card-title">
                    <i class="fas fa-clipboard-list me-2"></i> Offline Exams Management
                </h1>
                <div>
                    @can('create offline exams')
                        <button wire:click="create" class="btn btn-primary">
                            <i class="fas fa-plus"></i> New Offline Exam
                        </button>
                    @endcan
                </div>
            </div>
        </div>
        <div class="card-body">
            <!-- Filters -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Search exams...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select wire:model.live="statusFilter" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="draft">Draft</option>
                        <option value="published">Published</option>
                        <option value="completed">Completed</option>
                        <option value="canceled">Canceled</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select wire:model.live="typeFilter" class="form-select">
                        <option value="">All Types</option>
                        @foreach($examTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select wire:model.live="perPage" class="form-select">
                        <option value="10">10 per page</option>
                        <option value="25">25 per page</option>
                        <option value="50">50 per page</option>
                        <option value="100">100 per page</option>
                    </select>
                </div>
            </div>

            <!-- Exams Table -->
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="bg-primary text-white">
                        <tr>
                            <th>Title</th>
                            <th>Course</th>
                            <th>Date & Time</th>
                            <th>Venue</th>
                            <th>Status</th>
                            <th>Clearance Threshold</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($exams as $exam)
                            <tr>
                                <td>{{ $exam->title }}</td>
                                <td>{{ $exam->course->title ?? 'N/A' }}</td>
                                <td>{{ $exam->date->format('M d, Y g:i A') }}</td>
                                <td>{{ $exam->venue }}</td>
                                <td>
                                    <span class="badge bg-{{ $exam->status === 'published' ? 'success' : ($exam->status === 'draft' ? 'warning' : ($exam->status === 'canceled' ? 'danger' : 'info')) }}">
                                        {{ ucfirst($exam->status) }}
                                    </span>
                                </td>
                                <td>{{ $exam->clearance_threshold }}%</td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" wire:click="viewDetails({{ $exam->id }})" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        @can('update offline exams')
                                            <button type="button" wire:click="edit({{ $exam->id }})" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        @endcan
                                        @if($exam->status === 'published')
                                            @can('manage clearances')
                                                <button type="button" wire:click="confirmClearanceProcess({{ $exam->id }})" class="btn btn-sm btn-success">
                                                    <i class="fas fa-sync"></i> Process
                                                </button>
                                            @endcan
                                        @endif
                                        @can('delete offline exams')
                                            <button type="button" wire:click="confirmDeletion({{ $exam->id }})" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                                        <h5>No offline exams found</h5>
                                        <p class="text-muted">
                                            @if($search)
                                                No exams match your search criteria.
                                            @elseif($statusFilter || $typeFilter)
                                                No exams match the selected filters.
                                            @else
                                                Get started by creating your first offline exam.
                                            @endif
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-4">
                <div>
                    Showing {{ $exams->firstItem() ?? 0 }} to {{ $exams->lastItem() ?? 0 }} of {{ $exams->total() }} exams
                </div>
                <div>
                    {{ $exams->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Create/Edit Form Modal -->
    <div class="modal fade" id="formModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $formMode === 'create' ? 'Create New' : 'Edit' }} Offline Exam</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit="save">
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                                <input type="text" id="title" wire:model="title" class="form-control @error('title') is-invalid @enderror">
                                @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="description" class="form-label">Description</label>
                                <textarea id="description" wire:model="description" class="form-control @error('description') is-invalid @enderror" rows="3"></textarea>
                                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="date" class="form-label">Date & Time <span class="text-danger">*</span></label>
                                <input type="datetime-local" id="date" wire:model="date" class="form-control @error('date') is-invalid @enderror">
                                @error('date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="duration" class="form-label">Duration (minutes) <span class="text-danger">*</span></label>
                                <input type="number" id="duration" wire:model="duration" class="form-control @error('duration') is-invalid @enderror">
                                @error('duration') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="course_id" class="form-label">Course <span class="text-danger">*</span></label>
                                <select id="course_id" wire:model="course_id" class="form-select @error('course_id') is-invalid @enderror">
                                    <option value="">Select Course</option>
                                    @foreach(\App\Models\Subject::orderBy('name')->get() as $course)
                                        <option value="{{ $course->id }}">{{ $course->name }}</option>
                                    @endforeach
                                </select>
                                @error('course_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="type_id" class="form-label">Exam Type</label>
                                <select id="type_id" wire:model="type_id" class="form-select @error('type_id') is-invalid @enderror">
                                    <option value="">Select Type</option>
                                    @foreach($examTypes as $type)
                                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                                @error('type_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="venue" class="form-label">Venue <span class="text-danger">*</span></label>
                                <input type="text" id="venue" wire:model="venue" class="form-control @error('venue') is-invalid @enderror">
                                @error('venue') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="proctor_id" class="form-label">Proctor</label>
                                <select id="proctor_id" wire:model="proctor_id" class="form-select @error('proctor_id') is-invalid @enderror">
                                    <option value="">Select Proctor</option>
                                    @foreach(\App\Models\User::role('lecturer')->orderBy('name')->get() as $proctor)
                                        <option value="{{ $proctor->id }}">{{ $proctor->name }}</option>
                                    @endforeach
                                </select>
                                @error('proctor_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="clearance_threshold" class="form-label">Clearance Threshold (%)</label>
                                <input type="number" id="clearance_threshold" wire:model="clearance_threshold" class="form-control @error('clearance_threshold') is-invalid @enderror" min="0" max="100">
                                <small class="text-muted">Percentage of fees required for clearance. Default is 60%</small>
                                @error('clearance_threshold') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="passing_percentage" class="form-label">Passing Percentage</label>
                                <input type="number" id="passing_percentage" wire:model="passing_percentage" class="form-control @error('passing_percentage') is-invalid @enderror" min="0" max="100">
                                @error('passing_percentage') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select id="status" wire:model="status" class="form-select @error('status') is-invalid @enderror">
                                    <option value="draft">Draft</option>
                                    <option value="published">Published</option>
                                    <option value="completed">Completed</option>
                                    <option value="canceled">Canceled</option>
                                </select>
                                @if($status === 'published')
                                    <small class="text-success">
                                        <i class="fas fa-info-circle"></i> When published, clearance checks for students will be processed automatically.
                                    </small>
                                @endif
                                @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end mt-4">
                            <button type="button" class="btn btn-secondary me-2" wire:click="cancelForm">Cancel</button>
                            <button type="submit" class="btn btn-primary">
                                {{ $formMode === 'create' ? 'Create Exam' : 'Update Exam' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Details Modal -->
    <div class="modal fade" id="detailsModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">Exam Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if($selectedExam)
                    <div class="row">
                        <div class="col-md-12 mb-4">
                            <h5 class="fw-bold">{{ $selectedExam->title }}</h5>
                            <p class="text-muted">{{ $selectedExam->description }}</p>
                        </div>
                        
                        <div class="col-md-6">
                            <ul class="list-group mb-4">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Status
                                    <span class="badge bg-{{ $selectedExam->status === 'published' ? 'success' : ($selectedExam->status === 'draft' ? 'warning' : ($selectedExam->status === 'canceled' ? 'danger' : 'info')) }}">
                                        {{ ucfirst($selectedExam->status) }}
                                    </span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Course
                                    <span>{{ $selectedExam->course->title ?? 'N/A' }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Type
                                    <span>{{ $selectedExam->type->name ?? 'N/A' }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Venue
                                    <span>{{ $selectedExam->venue }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Created By
                                    <span>{{ $selectedExam->user->name ?? 'N/A' }}</span>
                                </li>
                            </ul>
                        </div>
                        
                        <div class="col-md-6">
                            <ul class="list-group mb-4">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Date & Time
                                    <span>{{ $selectedExam->date->format('M d, Y g:i A') }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Duration
                                    <span>{{ $selectedExam->duration }} minutes</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Proctor
                                    <span>{{ $selectedExam->proctor->name ?? 'Not Assigned' }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Clearance Threshold
                                    <span>{{ $selectedExam->clearance_threshold }}%</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Passing Score
                                    <span>{{ $selectedExam->passing_percentage }}%</span>
                                </li>
                            </ul>
                        </div>
                        
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h1 class="display-4">{{ $selectedExam->clearances_count }}</h1>
                                            <p class="text-muted">Students Cleared</p>
                                            @can('view clearances')
                                                <a href="{{ route('admin.clearances.index', ['exam_id' => $selectedExam->id, 'exam_type' => 'offline']) }}" class="btn btn-sm btn-primary">
                                                    View Clearances
                                                </a>
                                            @endcan
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h1 class="display-4">{{ $selectedExam->exam_entry_tickets_count }}</h1>
                                            <p class="text-muted">Entry Tickets Issued</p>
                                            @can('view entry tickets')
                                                <a href="{{ route('admin.tickets.index', ['exam_id' => $selectedExam->id, 'exam_type' => 'offline']) }}" class="btn btn-sm btn-primary">
                                                    View Tickets
                                                </a>
                                            @endcan
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteConfirmationModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this offline exam? This action cannot be undone.</p>
                    <p class="text-danger"><strong>Note:</strong> You cannot delete exams that have associated clearances or entry tickets.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="cancelDeletion">Cancel</button>
                    <button type="button" class="btn btn-danger" wire:click="delete">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Clearance Process Confirmation Modal -->
    <div class="modal fade" id="clearanceProcessModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Process Clearances</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to process clearances for all students for this exam?</p>
                    <p>This will check each student's fee payment status against the exam's clearance threshold and update their clearance records accordingly.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="cancelClearanceProcess">Cancel</button>
                    <button type="button" class="btn btn-success" wire:click="processClearance">
                        <i class="fas fa-sync-alt"></i> Process Clearances
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('livewire:initialized', () => {
            const formModal = new bootstrap.Modal(document.getElementById('formModal'));
            const detailsModal = new bootstrap.Modal(document.getElementById('detailsModal'));
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmationModal'));
            const clearanceModal = new bootstrap.Modal(document.getElementById('clearanceProcessModal'));
            
            @this.on('open-details-modal', () => {
                detailsModal.show();
            });
            
            // Show/hide form modal
            Livewire.on('showFormModal', () => {
                formModal.show();
            });
            
            Livewire.on('hideFormModal', () => {
                formModal.hide();
            });
            
            // Show delete confirmation modal when needed
            Livewire.on('confirmingDeletion', () => {
                deleteModal.show();
            });
            
            Livewire.on('deletionComplete', () => {
                deleteModal.hide();
            });
            
            // Show clearance process confirmation modal when needed
            Livewire.on('confirmingClearanceProcess', () => {
                clearanceModal.show();
            });
            
            Livewire.on('clearanceProcessComplete', () => {
                clearanceModal.hide();
            });
            
            // Watch property changes and show/hide form modal
            Livewire.watch('showForm', value => {
                if (value) {
                    formModal.show();
                } else {
                    formModal.hide();
                }
            });
            
            // Watch property changes and show/hide confirmation modals
            Livewire.watch('confirmingDeletion', value => {
                if (value) {
                    deleteModal.show();
                } else {
                    deleteModal.hide();
                }
            });
            
            Livewire.watch('confirmingClearanceProcess', value => {
                if (value) {
                    clearanceModal.show();
                } else {
                    clearanceModal.hide();
                }
            });
        });
    </script>
    @endpush
</div>
