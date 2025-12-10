<div class="container mt-4" x-data="{ isConfirmModalOpen: false, questionId: null }">
    <!-- Exam Selection Dropdown -->
   

    <!-- Questions Section -->
    <div class="mb-4 card">
        <div class="card-header">
            <h3 class="card-title">Question Bank</h3>
        </div>
        <div class="card-body">


            @if($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(session()->has('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if(session()->has('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            @if(session()->has('message'))
                <div class="alert alert-success">
                    {{ session('message') }}
                </div>
            @endif

            <div class="mb-4">
                <label for="examSelect" class="form-label">Select Exam:</label>
                <select id="examSelect" class="form-select" wire:model="exam_id" wire:change="loadQuestions">
                    <option value="">Select Exam</option>
                    @foreach($exams as $exam)
                        <option value="{{ $exam->id }}">{{ $exam->course->course_code }} {{ $exam->course->name }} - {{ $exam->course->collegeClass->name . ' ' . $exam->course->year->name . ' (' . $exam->course->semester->name . ')' }}</option>
                    @endforeach
                </select>
            </div>

            @forelse($questions as $index => $question)
               <div class="p-4 mb-4 w-full rounded d-flex border bg-white shadow-sm">
                {{-- Question No --}}
                <div class="me-4 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; border-radius: 50%; background: linear-gradient(145deg, #f8f9fa, #e9ecef); border: 2px solid #dee2e6; flex-shrink: 0;">
                    <span class="fw-bold text-primary">{{ $index + 1 }}</span>
                </div>
                <div class="flex-grow-1">
                <div class="p-4 rounded border bg-light">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <span class="badge bg-primary px-3 py-2">Question {{ $index + 1 }}</span>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-primary" wire:click.prevent="saveQuestion({{ $index }})">
                                <i class="bi bi-save me-1"></i>Save
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="openConfirmModal({{ $question['id'] }})">
                                <i class="bi bi-trash me-1"></i>Delete
                            </button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="question{{ $index }}" class="form-label fw-bold">Question Text:</label>
                        <textarea rows="4" id="question{{ $index }}" class="form-control" wire:model="questions.{{ $index }}.question_text" placeholder="Enter your question here..."></textarea>
                    </div>

                    {{-- DEBUG: Show attachments data --}}
                    @if(isset($question['attachments']))
                        <div class="alert alert-info">
                            <strong>DEBUG:</strong> Attachments count: {{ count($question['attachments']) }}
                            @if(count($question['attachments']) > 0)
                                <br>First attachment type: {{ $question['attachments'][0]['attachment_type'] ?? 'N/A' }}
                                <br>First attachment path: {{ $question['attachments'][0]['file_path'] ?? 'N/A' }}
                            @endif
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <strong>DEBUG:</strong> No attachments key found in question array
                        </div>
                    @endif

                    {{-- Display attached images if any --}}
                    @if(isset($question['attachments']) && count($question['attachments']) > 0)
                        @php
                            $images = array_filter($question['attachments'], fn($attachment) => $attachment['attachment_type'] === 'image');
                        @endphp
                        @if(count($images) > 0)
                            <div class="mb-3">
                                <label class="form-label fw-bold">Question Images:</label>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($images as $image)
                                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('exams')->url($image['file_path']) }}" 
                                             alt="Question image" 
                                             class="img-thumbnail" 
                                             style="max-height: 150px; object-fit: contain;">
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endif

                    <div class="row">
                        <div class="mb-3 col-md-6">
                            <label for="section{{ $index }}" class="form-label">Section:</label>
                            <input type="text" id="section{{ $index }}" class="form-control" wire:model="questions.{{ $index }}.exam_section" placeholder="Section">
                        </div>
                        <div class="mb-3 col-md-6">
                            <label for="marks{{ $index }}" class="form-label">Marks:</label>
                            <input type="number" id="marks{{ $index }}" class="form-control" wire:model="questions.{{ $index }}.marks" placeholder="Marks">
                        </div>
                    </div>

                    <!-- Options Section -->
                    <div class="mt-4">
                        <label class="form-label fw-bold mb-3">
                            <i class="bi bi-list-check me-1"></i>Answer Options:
                        </label>
                        @foreach($question['options'] as $optIndex => $option)
                            <div class="mb-3 input-group">
                                <span class="input-group-text bg-light">
                                    {{ chr(65 + $optIndex) }}.
                                </span>
                                <input type="text" class="form-control" wire:model="questions.{{ $index }}.options.{{ $optIndex }}.option_text" placeholder="Enter option {{ chr(65 + $optIndex) }}">
                                <div class="input-group-text bg-light">
                                    <div class="form-check mb-0">
                                        <input type="checkbox" class="form-check-input" wire:model="questions.{{ $index }}.options.{{ $optIndex }}.is_correct" {{ $option['is_correct'] ? 'checked' : '' }}>
                                        <label class="form-check-label ms-1">Correct</label>
                                    </div>
                                </div>
                                <button class="btn btn-outline-danger" wire:click.prevent="removeOption({{ $index }}, {{ $optIndex }})">
                                    <i class="bi bi-trash me-1"></i>Remove
                                </button>
                            </div>
                        @endforeach
                        <button class="btn btn-outline-success" wire:click.prevent="addOption({{ $index }})">
                            <i class="bi bi-plus-circle me-1"></i>Add Option
                        </button>
                    </div>

                    <!-- Question Actions -->
                    <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center">
                        <button class="btn btn-outline-secondary" wire:click.prevent="resetQuestion({{ $index }})">
                            <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
                        </button>
                        <div class="btn-group">
                            <button class="btn btn-primary" wire:click.prevent="saveQuestion({{ $index }})">
                                <i class="bi bi-save me-1"></i>Save Changes
                            </button>
                            <button class="btn btn-outline-danger" onclick="openConfirmModal({{ $question['id'] }})">
                                <i class="bi bi-trash me-1"></i>Delete
                            </button>
                        </div>
                    </div>
                </div>
                </div>


               </div>
            @empty
            @if($exam_id)    
                <div class="container py-4 rounded border bg-light">
                    <p class="mt-3 text-center text-muted">
                        No questions available for the selected exam. Start by creating a new question, or importing questions.
                    </p>
                    
                    <x-bulk-upload :examId="$exam_id" />
                    <!-- Loading Spinner -->
                    <div wire:loading wire:target="importQuestions" class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Processing...</span>
                        </div>
                        <p class="mt-2 text-muted">Importing questions. Please wait...</p>
                    </div>
                </div>
            @else
            <p class="text-center text-muted">Please select an exam to view questions.</p>
            @endif
                 <!-- Bulk Import and Save Buttons -->
   
    
    @endforelse
    @if(count($questions)>0)
    <div class="d-flex justify-content-center">

        <button class="btn btn-success" wire:click.prevent="saveQuestions">Save Questions</button>

    </div>
    @endif
        </div>
    </div>

    <!-- Confirmation Modal -->
     <!-- Use backdrop: static -->
    <div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="confirmModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmModalLabel">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this question?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" onclick="confirmDelete()">Delete</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let questionIdToDelete = null;

    function openConfirmModal(id) {
        questionIdToDelete = id;
        var myModal = new bootstrap.Modal(document.getElementById('confirmModal'));
        myModal.show();
    }

    function confirmDelete() {
        if (questionIdToDelete !== null) {
            Livewire.emit('deleteQuestion', questionIdToDelete);
            questionIdToDelete = null;
            var myModal = bootstrap.Modal.getInstance(document.getElementById('confirmModal'));
            myModal.hide();
        }
    }
</script>
