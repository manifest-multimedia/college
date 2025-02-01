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
               <div class="p-10 mb-4 w-full rounded d-flex border-light">


                {{-- Question No --}}
                <div class="p-5 rounded border me-3 d-flex align-items-center justify-content-center bg-light"  style="width: 80px; height: 80px; border-radius: 50%; background-color: #f0f0f0;">
                  Q  {{ $index + 1 }}
                </div>
                <div class="flex-grow-1">
                <div class="p-10 mb-4 rounded border bg-light">
                    <div class="mb-3">
                        <label for="question{{ $index }}" class="form-label">Question Text:</label>
                        <textarea rows="7" id="question{{ $index }}" class="form-control" wire:model="questions.{{ $index }}.question_text" placeholder="Enter Question"></textarea>
                    </div>

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
                    <div>
                        <label class="form-label">Options:</label>
                        @foreach($question['options'] as $optIndex => $option)
                            <div class="mb-2 input-group">
                                <input type="text" class="form-control" wire:model="questions.{{ $index }}.options.{{ $optIndex }}.option_text" placeholder="Option {{ $optIndex + 1 }}">
                                
             <span class="input-group-text">
                 <input type="checkbox" wire:model="questions.{{ $index }}.options.{{ $optIndex }}.is_correct" {{ $option['is_correct'] ? 'checked' : '' }}>
                 <label class="form-check-label ms-1">Correct</label>
             </span>
                                
                                <button class="btn btn-danger" wire:click.prevent="removeOption({{ $index }}, {{ $optIndex }})">Remove Option</button>
                            </div>
                        @endforeach
                        <button class="mt-2 btn btn-success btn-sm" wire:click.prevent="addOption({{ $index }})">Add Option</button>
                       

                    </div>

                    <!-- Delete Question Button -->
                    <div class="mt-3 text-end">
                        <button class="btn btn-primary btn-sm" wire:click.prevent="saveQuestion({{ $index }})">Save Question</button>
                        <button class="btn btn-dark btn-sm" onclick="openConfirmModal({{ $question['id'] }})">Delete Question</button>
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
