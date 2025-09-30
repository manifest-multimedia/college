<div>
    <!-- Success/Error Messages -->
    @if(session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(!$questionSet)
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>Error:</strong> Question set not found.
            <a href="{{ route('question.sets') }}" class="btn btn-primary btn-sm ms-2">
                <i class="bi bi-arrow-left me-1"></i>Back to Question Sets
            </a>
        </div>
    @else
        <!-- Question Set Info -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex align-items-center">
                <div class="bg-primary text-white rounded-circle p-2 me-3">
                    <i class="bi bi-collection fs-4"></i>
                </div>
                <div>
                    <h5 class="mb-1">{{ $questionSet->name }}</h5>
                    <p class="text-muted mb-0">
                        <i class="bi bi-book me-1"></i>{{ $questionSet->course->name ?? 'N/A' }} • 
                        <i class="bi bi-question-circle me-1"></i>{{ $questionsCount }} questions
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Question Form -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-{{ $isEditing ? 'pencil' : 'plus-circle' }} me-2"></i>
                        {{ $isEditing ? 'Edit Question' : 'Create New Question' }}
                    </h5>
                </div>
                <div class="card-body">
                    <form wire:submit="save">
                        <!-- Question Text -->
                        <div class="mb-4">
                            <label for="questionText" class="form-label">
                                Question Text <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control @error('questionText') is-invalid @enderror" 
                                      id="questionText" rows="4" wire:model="questionText" 
                                      placeholder="Enter the question text..."></textarea>
                            @error('questionText')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Provide a clear and concise question</small>
                        </div>

                        <div class="row">
                            <!-- Question Type -->
                            <div class="col-md-4">
                                <div class="mb-4">
                                    <label for="questionType" class="form-label">
                                        Question Type <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('questionType') is-invalid @enderror" 
                                            id="questionType" wire:model="questionType">
                                        <option value="multiple_choice">Multiple Choice</option>
                                        <option value="true_false">True/False</option>
                                        <option value="short_answer">Short Answer</option>
                                    </select>
                                    @error('questionType')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Difficulty Level -->
                            <div class="col-md-4">
                                <div class="mb-4">
                                    <label for="difficultyLevel" class="form-label">
                                        Difficulty Level <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('difficultyLevel') is-invalid @enderror" 
                                            id="difficultyLevel" wire:model="difficultyLevel">
                                        <option value="easy">Easy</option>
                                        <option value="medium">Medium</option>
                                        <option value="hard">Hard</option>
                                    </select>
                                    @error('difficultyLevel')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Marks -->
                            <div class="col-md-4">
                                <div class="mb-4">
                                    <label for="marks" class="form-label">
                                        Marks <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" class="form-control @error('marks') is-invalid @enderror" 
                                           id="marks" wire:model="marks" min="1" max="100">
                                    @error('marks')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Options Section -->
                        @if($questionType === 'multiple_choice')
                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <label class="form-label">
                                        Answer Options <span class="text-danger">*</span>
                                    </label>
                                    <div>
                                        <button type="button" class="btn btn-outline-primary btn-sm" 
                                                wire:click="addOption" @if(count($options) >= 10) disabled @endif>
                                            <i class="bi bi-plus me-1"></i>Add Option
                                        </button>
                                    </div>
                                </div>

                                @error('options')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror

                                <div class="options-container">
                                    @foreach($options as $index => $option)
                                        <div class="card mb-3">
                                            <div class="card-body">
                                                <div class="row align-items-center">
                                                    <!-- Correct Answer Radio -->
                                                    <div class="col-md-1">
                                                        <div class="form-check">
                                                            <input class="form-check-input @error('correctOption') is-invalid @enderror" 
                                                                   type="radio" name="correctOption" 
                                                                   id="correct{{ $index }}" 
                                                                   value="{{ $index }}" 
                                                                   wire:model="correctOption">
                                                            <label class="form-check-label" for="correct{{ $index }}">
                                                                <span class="badge bg-secondary">{{ chr(65 + $index) }}</span>
                                                            </label>
                                                        </div>
                                                    </div>

                                                    <!-- Option Text -->
                                                    <div class="col-md-10">
                                                        <input type="text" class="form-control @error('options.'.$index) is-invalid @enderror" 
                                                               wire:model="options.{{ $index }}" 
                                                               placeholder="Enter option {{ chr(65 + $index) }}...">
                                                        @error('options.'.$index)
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                    <!-- Remove Button -->
                                                    <div class="col-md-1">
                                                        @if(count($options) > 2)
                                                            <button type="button" class="btn btn-outline-danger btn-sm" 
                                                                    wire:click="removeOption({{ $index }})">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                @error('correctOption')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                                
                                <small class="text-muted">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Select the radio button next to the correct answer. Minimum 2 options required.
                                </small>
                            </div>
                        @endif

                        <!-- Section/Category -->
                        <div class="mb-4">
                            <label for="examSection" class="form-label">Section/Category</label>
                            <input type="text" class="form-control @error('examSection') is-invalid @enderror" 
                                   id="examSection" wire:model="examSection" 
                                   placeholder="e.g., Chapter 1, Mathematics, etc.">
                            @error('examSection')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Optional: Categorize the question for organization</small>
                        </div>

                        <!-- Explanation -->
                        <div class="mb-4">
                            <label for="explanation" class="form-label">Explanation</label>
                            <textarea class="form-control @error('explanation') is-invalid @enderror" 
                                      id="explanation" rows="3" wire:model="explanation" 
                                      placeholder="Explain why this is the correct answer..."></textarea>
                            @error('explanation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Optional: Provide an explanation for the correct answer</small>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex justify-content-between">
                            <div>
                                <button type="button" class="btn btn-outline-secondary me-2" wire:click="resetForm">
                                    <i class="bi bi-arrow-clockwise me-1"></i>Reset
                                </button>
                                <a href="{{ route('question.sets.questions', $questionSetId) }}" class="btn btn-outline-danger">
                                    <i class="bi bi-x-lg me-1"></i>Cancel
                                </a>
                            </div>
                            <button type="submit" class="btn btn-primary" 
                                    wire:loading.attr="disabled" wire:target="save">
                                <span wire:loading.remove wire:target="save">
                                    <i class="bi bi-{{ $isEditing ? 'check-lg' : 'plus-lg' }} me-1"></i>
                                    {{ $isEditing ? 'Update Question' : 'Create Question' }}
                                </span>
                                <span wire:loading wire:target="save">
                                    <i class="bi bi-hourglass-split me-1"></i>
                                    {{ $isEditing ? 'Updating...' : 'Creating...' }}
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Question Guidelines -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-lightbulb me-2"></i>Question Guidelines
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="fw-bold">Writing Good Questions:</h6>
                        <ul class="small">
                            <li>Be clear and concise</li>
                            <li>Avoid ambiguous wording</li>
                            <li>Test specific knowledge</li>
                            <li>Use proper grammar</li>
                            <li>Avoid negative statements</li>
                        </ul>
                    </div>

                    <div class="mb-3">
                        <h6 class="fw-bold">Multiple Choice Tips:</h6>
                        <ul class="small">
                            <li>All options should be plausible</li>
                            <li>Keep options similar in length</li>
                            <li>Avoid "all of the above"</li>
                            <li>Only one option should be correct</li>
                            <li>Use 3-5 options typically</li>
                        </ul>
                    </div>

                    <div class="alert alert-info small">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Difficulty Levels:</strong><br>
                        <strong>Easy:</strong> Basic recall<br>
                        <strong>Medium:</strong> Application<br>
                        <strong>Hard:</strong> Analysis & synthesis
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-lightning me-2"></i>Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('question.sets.import', $questionSetId) }}" class="btn btn-outline-primary">
                            <i class="bi bi-upload me-1"></i>Bulk Import Questions
                        </a>
                        <a href="{{ route('question.sets.questions', $questionSetId) }}" class="btn btn-outline-secondary">
                            <i class="bi bi-list me-1"></i>View All Questions
                        </a>
                        <a href="{{ route('question.sets.show', $questionSetId) }}" class="btn btn-outline-info">
                            <i class="bi bi-eye me-1"></i>Question Set Details
                        </a>
                    </div>
                </div>
            </div>

            <!-- Preview -->
            @if(!empty($questionText))
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="bi bi-eye me-2"></i>Preview
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="question-preview">
                            <h6>{{ $questionText }}</h6>
                            
                            @if($questionType === 'multiple_choice' && count(array_filter($options)) > 0)
                                <div class="options-preview mt-3">
                                    @foreach($options as $index => $option)
                                        @if(!empty(trim($option)))
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" 
                                                       name="preview" disabled 
                                                       @if($index == $correctOption) checked @endif>
                                                <label class="form-check-label">
                                                    <strong>{{ chr(65 + $index) }}.</strong> {{ $option }}
                                                    @if($index == $correctOption)
                                                        <span class="badge bg-success ms-2">Correct</span>
                                                    @endif
                                                </label>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @endif

                            @if(!empty($explanation))
                                <div class="mt-3 p-2 bg-light rounded">
                                    <small><strong>Explanation:</strong> {{ $explanation }}</small>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Loading Overlay -->
    <div wire:loading wire:target="save" class="position-fixed top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center" 
         style="background-color: rgba(0,0,0,0.5); z-index: 9999;">
        <div class="bg-white p-4 rounded shadow">
            <div class="text-center">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <div>
                    <span wire:loading wire:target="save">{{ $isEditing ? 'Updating' : 'Creating' }} question...</span>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>