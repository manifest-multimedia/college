<div class="container-fluid mt-4">
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

    @if(!$viewingQuestionSet)
        <!-- Question Sets View -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title m-0">Question Bank</h3>
                <div>
                    @if($exam_id)
                        <!-- When viewing from exam context, show exam info -->
                        <span class="badge bg-info me-2">
                            Exam: {{ $exams->firstWhere('id', $exam_id)->title ?? 'Unknown' }}
                        </span>
                    @endif
                    <button class="btn btn-primary" wire:click="$set('createNewSet', {{ !$createNewSet }})">
                        {{ $createNewSet ? 'Cancel' : 'Create New Set' }}
                    </button>
                </div>
            </div>
            <div class="card-body">
                @if($createNewSet)
                    <!-- Create New Question Set Form -->
                    <div class="p-4 mb-4 bg-light rounded border">
                        <h5 class="mb-3">Create New Question Set</h5>
                        <div class="mb-3">
                            <label for="subjectSelect" class="form-label">Subject <span class="text-danger">*</span></label>
                            <select id="subjectSelect" class="form-select" wire:model="subject_id" required>
                                <option value="">Select Subject</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}">{{ $subject->course_code }} - {{ $subject->name }}</option>
                                @endforeach
                            </select>
                            @error('subject_id') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Question Set Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" wire:model="newSetName" placeholder="Enter question set name" required>
                            @error('newSetName') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description (Optional)</label>
                            <textarea class="form-control" wire:model="newSetDescription" rows="3" placeholder="Describe this question set"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Difficulty Level</label>
                            <select class="form-select" wire:model="newSetDifficulty">
                                @foreach($difficultyLevels as $key => $level)
                                    <option value="{{ $key }}">{{ $level }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="text-end">
                            <button class="btn btn-success" wire:click="createQuestionSet">
                                <i class="bi bi-save me-1"></i> Create Question Set
                            </button>
                        </div>
                    </div>
                @else
                    @if(!$exam_id)
                        <!-- Subject Filter (only show if not in exam context) -->
                        <div class="mb-4">
                            <label for="subjectFilter" class="form-label fw-bold">Filter by Subject:</label>
                            <select id="subjectFilter" class="form-select" wire:model="subject_id">
                                <option value="">All Subjects</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}">{{ $subject->course_code }} - {{ $subject->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <!-- Exam Selection (when in exam context) -->
                        <div class="mb-4">
                            <label for="examSelect" class="form-label fw-bold">Select Exam:</label>
                            <select id="examSelect" class="form-select" wire:model="exam_id" wire:change="loadQuestions">
                                <option value="">Select Exam</option>
                                @foreach($exams as $exam)
                                    <option value="{{ $exam->id }}">{{ $exam->title }} - {{ $exam->course->course_code ?? 'N/A' }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <!-- Question Sets Cards Grid -->
                    <div class="row g-4 py-3">
                        @forelse($filteredQuestionSets as $set)
                            <div class="col-md-4 col-lg-3 mb-4">
                                <div class="card h-100">
                                    <div class="card-header p-3 {{ match($set->difficulty_level) {
                                        'easy' => 'bg-success-subtle',
                                        'hard' => 'bg-danger-subtle',
                                        default => 'bg-warning-subtle',
                                    } }}">
                                        <h5 class="card-title mb-0 fw-bold">{{ $set->name }}</h5>
                                    </div>
                                    <div class="card-body d-flex flex-column">
                                        <p class="text-muted small mb-1">
                                            <i class="bi bi-book"></i>
                                            <strong>Subject:</strong> {{ $set->course->course_code ?? 'N/A' }}
                                        </p>
                                        <p class="text-muted small mb-3">
                                            <i class="bi bi-bar-chart"></i>
                                            <strong>Difficulty:</strong> 
                                            <span class="badge {{ match($set->difficulty_level) {
                                                'easy' => 'bg-success',
                                                'hard' => 'bg-danger',
                                                default => 'bg-warning',
                                            } }}">
                                                {{ ucfirst($set->difficulty_level) }}
                                            </span>
                                        </p>
                                        
                                        <p class="card-text mb-3">
                                            {{ Str::limit($set->description, 100) ?: 'No description available.' }}
                                        </p>
                                        
                                        @php
                                            $questionCount = App\Models\Question::where('question_set_id', $set->id)->count();
                                        @endphp
                                        
                                        <div class="d-flex align-items-center mt-auto mb-2">
                                            <span class="badge bg-secondary me-2">{{ $questionCount }} Questions</span>
                                        </div>
                                        
                                        <div class="d-grid gap-2">
                                            <button class="btn btn-primary btn-sm" wire:click="viewQuestionSet({{ $set->id }})">
                                                <i class="bi bi-pencil-square me-1"></i> Manage Questions
                                            </button>
                                            
                                                                        <div class="btn-group w-100" role="group">
                                <a href="{{ route('question.sets.import', $set->id) }}" class="btn btn-outline-warning btn-sm">
                                    <i class="bi bi-upload me-1"></i>Import
                                </a>
                                <a href="{{ route('question.sets.questions.create', $set->id) }}" class="btn btn-outline-success btn-sm">
                                    <i class="bi bi-plus me-1"></i>Add Question
                                </a>
                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="alert alert-info text-center">
                                    <i class="bi bi-info-circle me-2"></i>
                                    No question sets found. Create your first question set to get started.
                                </div>
                            </div>
                        @endforelse
                    </div>
                @endif
            </div>
        </div>
    @else
        <!-- Question Set Questions View -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center pt-5">
                <div>
                    <button class="btn btn-sm btn-outline-secondary me-2" wire:click="backToQuestionSets">
                        <i class="bi bi-arrow-left me-1"></i> Back to Sets
                    </button>
                    <h4 class="d-inline m-0">{{ $currentQuestionSet->name ?? 'Question Set' }}</h4>
                    @if($currentQuestionSet)
                        <span class="badge {{ match($currentQuestionSet->difficulty_level) {
                            'easy' => 'bg-success',
                            'hard' => 'bg-danger',
                            default => 'bg-warning',
                        } }} ms-2">{{ ucfirst($currentQuestionSet->difficulty_level) }}</span>
                    @endif
                </div>
                <div>
                    <span class="badge bg-secondary">{{ count($questions) }} Questions</span>
            </div>
            <div class="card-body">
                @if($currentQuestionSet)
                    <div class="mb-4">
                        <div class="d-flex align-items-center mb-3">
                            <span class="me-3"><i class="bi bi-book fs-5"></i></span>
                            <div>
                                <p class="mb-0"><strong>Subject:</strong> {{ $currentQuestionSet->course->course_code ?? 'N/A' }} - {{ $currentQuestionSet->course->name ?? 'Unknown' }}</p>
                            </div>
                        </div>
                        
                        @if($currentQuestionSet->description)
                            <div class="d-flex align-items-center mb-3">
                                <span class="me-3"><i class="bi bi-info-circle fs-5"></i></span>
                                <div>
                                    <p class="mb-0">{{ $currentQuestionSet->description }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Advanced Filters and Controls -->
                <div class="mb-4">
                    <div class="row g-3">
                        <!-- Search and Filters -->
                        <div class="col-md-12">
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Search questions..." 
                                    wire:model.live="searchTerm">
                                <button class="btn btn-outline-secondary" type="button" 
                                    wire:click="$toggle('showAdvancedFilters')">
                                    <i class="bi bi-funnel"></i> Filters
                                </button>
                            </div>
                        </div>
                        
                    @if($showAdvancedFilters)
                        <div class="mt-3 p-3 bg-light rounded">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Question Type:</label>
                                    <select class="form-select form-select-sm" wire:model.live="filterType">
                                        <option value="">All Types</option>
                                        @foreach($questionTypes as $type => $label)
                                            <option value="{{ $type }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Difficulty:</label>
                                    <select class="form-select form-select-sm" wire:model.live="filterDifficulty">
                                        <option value="">All Difficulties</option>
                                        @foreach($difficultyLevels as $level => $label)
                                            <option value="{{ $level }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button class="btn btn-outline-secondary btn-sm w-100" wire:click="clearFilters">
                                        <i class="bi bi-x-circle"></i> Clear Filters
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif
                        <!-- Bulk Operations -->
                        <div class="col-md-12">
                            <div class="btn-group w-100" role="group">
                                <button class="btn {{ $bulkMode ? 'btn-warning' : 'btn-outline-secondary' }}" 
                                    wire:click="toggleBulkMode">
                                    <i class="bi bi-check2-square"></i> Bulk
                                </button>
                                <button class="btn btn-outline-info" wire:click="$toggle('showStatistics')">
                                    <i class="bi bi-bar-chart"></i> Stats
                                </button>
                                <a href="{{ route('question.sets.import', $question_set_id) }}" class="btn btn-outline-warning">
                                    <i class="bi bi-upload"></i> Import
                                </a>
                                <a href="{{ route('question.sets.questions.create', $question_set_id) }}" class="btn btn-outline-success">
                                    <i class="bi bi-plus-circle"></i> Add
                                </a>
                            </div>
                        </div>
                    </div>
                    
                </div>
                
                <!-- Question Set Statistics -->
                @if($showStatistics && $questionSetStats)
                    <div class="mb-4">
                        <div class="card bg-light">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="bi bi-bar-chart me-2"></i>Question Set Statistics</h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <div class="h4 mb-0 text-primary">{{ $questionSetStats['total_questions'] }}</div>
                                            <small class="text-muted">Total Questions</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <div class="h4 mb-0 text-success">{{ $questionSetStats['total_marks'] }}</div>
                                            <small class="text-muted">Total Marks</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <div class="h4 mb-0 text-info">{{ number_format($questionSetStats['avg_marks_per_question'], 1) }}</div>
                                            <small class="text-muted">Avg Marks/Question</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <div class="h4 mb-0 text-warning">{{ $questionSetStats['questions_with_explanations'] }}</div>
                                            <small class="text-muted">With Explanations</small>
                                        </div>
                                    </div>
                                </div>
                                
                                <hr>
                                
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <h6>Difficulty Distribution</h6>
                                        <div class="d-flex justify-content-between">
                                            <span class="badge bg-success">Easy: {{ $questionSetStats['difficulty_breakdown']['easy'] }}</span>
                                            <span class="badge bg-warning">Medium: {{ $questionSetStats['difficulty_breakdown']['medium'] }}</span>
                                            <span class="badge bg-danger">Hard: {{ $questionSetStats['difficulty_breakdown']['hard'] }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <h6>Question Types</h6>
                                        <div class="d-flex justify-content-between">
                                            <span class="badge bg-primary">MCQ: {{ $questionSetStats['type_breakdown']['MCQ'] }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                
                <!-- Bulk Operations Panel -->
                @if($bulkMode)
                    <div class="mb-4">
                        <div class="card border-warning">
                            <div class="card-header bg-warning bg-opacity-25">
                                <h6 class="mb-0"><i class="bi bi-check2-square me-2"></i>Bulk Operations ({{ count($selectedQuestions) }} selected)</h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3 mb-3">
                                    <div class="col-md-4">
                                        <button class="btn btn-outline-primary btn-sm w-100" wire:click="selectAllQuestions">
                                            <i class="bi bi-check-all"></i> Select All
                                        </button>
                                    </div>
                                    <div class="col-md-4">
                                        <button class="btn btn-outline-secondary btn-sm w-100" wire:click="deselectAllQuestions">
                                            <i class="bi bi-x-square"></i> Deselect All
                                        </button>
                                    </div>
                                    <div class="col-md-4">
                                        <button class="btn btn-outline-danger btn-sm w-100" wire:click="bulkDeleteQuestions"
                                            wire:confirm="Are you sure you want to delete the selected questions?"
                                            {{ empty($selectedQuestions) ? 'disabled' : '' }}>
                                            <i class="bi bi-trash"></i> Delete Selected
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Move to Question Set:</label>
                                        <div class="input-group">
                                            <select class="form-select form-select-sm" wire:model="targetQuestionSetForMove">
                                                <option value="">Select question set...</option>
                                                @foreach($availableQuestionSetsForMove as $set)
                                                    <option value="{{ $set->id }}">{{ $set->name }}</option>
                                                @endforeach
                                            </select>
                                            <button class="btn btn-outline-success btn-sm" wire:click="bulkMoveQuestions"
                                                {{ empty($selectedQuestions) || !$targetQuestionSetForMove ? 'disabled' : '' }}>
                                                <i class="bi bi-arrow-right"></i> Move
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label class="form-label">Bulk Update Difficulty:</label>
                                        <div class="btn-group w-100" role="group">
                                            <button class="btn btn-outline-success btn-sm" wire:click="bulkUpdateDifficulty('easy')"
                                                {{ empty($selectedQuestions) ? 'disabled' : '' }}>Easy</button>
                                            <button class="btn btn-outline-warning btn-sm" wire:click="bulkUpdateDifficulty('medium')"
                                                {{ empty($selectedQuestions) ? 'disabled' : '' }}>Medium</button>
                                            <button class="btn btn-outline-danger btn-sm" wire:click="bulkUpdateDifficulty('hard')"
                                                {{ empty($selectedQuestions) ? 'disabled' : '' }}>Hard</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                
                          
 
                @forelse($questions as $index => $question)
                    <div class="p-4 mb-4 w-full rounded border bg-white shadow-sm hover:shadow-md transition-shadow {{ $bulkMode && in_array($question['id'] ?? null, $selectedQuestions) ? 'border-warning bg-warning bg-opacity-10' : '' }}">
                        <div class="d-flex">
                            @if($bulkMode)
                                <div class="me-3 d-flex justify-content-center align-items-center">
                                    <input type="checkbox" class="form-check-input" 
                                        wire:model.live="selectedQuestions" 
                                        value="{{ $question['id'] }}"
                                        {{ !isset($question['id']) ? 'disabled' : '' }}
                                        id="question-{{ $question['id'] ?? 'new' }}">
                                </div>
                            @endif
                            <div class="me-4 d-flex justify-content-center align-items-center rounded-circle bg-primary text-white" style="width: 48px; height: 48px; min-width: 48px; font-size: 1.1rem">
                                <span class="fw-bold">{{ $index + 1 }}</span>
                            </div>
                            <div class="flex-grow-1">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Question Text:</label>
                                    <textarea rows="3" class="form-control" wire:model="questions.{{ $index }}.question_text" placeholder="Enter Question"></textarea>
                                </div>

                                <div class="row">
                                    <div class="mb-3 col-md-3">
                                        <label class="form-label">Section:</label>
                                        <input type="text" class="form-control" wire:model="questions.{{ $index }}.exam_section" placeholder="Section">
                                    </div>
                                    <div class="mb-3 col-md-3">
                                        <label class="form-label">Marks:</label>
                                        <input type="number" class="form-control" wire:model="questions.{{ $index }}.marks" placeholder="Marks">
                                    </div>
                                    <div class="mb-3 col-md-3">
                                        <label class="form-label">Type:</label>
                                        <select class="form-control" wire:model="questions.{{ $index }}.type">
                                            @foreach($questionTypes as $type => $label)
                                                <option value="{{ $type }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3 col-md-3">
                                        <label class="form-label">Difficulty:</label>
                                        <select class="form-control" wire:model="questions.{{ $index }}.difficulty_level">
                                            @foreach($difficultyLevels as $level => $label)
                                                <option value="{{ $level }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Explanation (Optional):</label>
                                    <textarea class="form-control" wire:model="questions.{{ $index }}.explanation" placeholder="Explanation for the correct answer" rows="2"></textarea>
                                </div>

                                <!-- Options Section (for MCQ) -->
                                @if(!isset($question['type']) || $question['type'] === 'MCQ')
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Answer Options:</label>
                                        @foreach($question['options'] as $optIndex => $option)
                                            <div class="mb-2 input-group">
                                                <div class="input-group-text">
                                                    <input type="checkbox" 
                                                        wire:model.live="questions.{{ $index }}.options.{{ $optIndex }}.is_correct" 
                                                        {{ isset($option['is_correct']) && $option['is_correct'] ? 'checked' : '' }}
                                                        aria-label="Correct option checkbox">
                                                </div>
                                                <input type="text" class="form-control" 
                                                    wire:model="questions.{{ $index }}.options.{{ $optIndex }}.option_text" 
                                                    placeholder="Option {{ $optIndex + 1 }}">
                                                <button class="btn btn-outline-danger" 
                                                    wire:click.prevent="removeOption({{ $index }}, {{ $optIndex }})">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        @endforeach
                                        <button class="btn btn-sm btn-outline-secondary" 
                                            wire:click.prevent="addOption({{ $index }})">
                                            <i class="bi bi-plus-circle me-1"></i> Add Option
                                        </button>
                                    </div>
                                @endif

                                <div class="text-end mt-3">
                                    <button class="btn btn-sm btn-success me-2" 
                                        wire:click.prevent="saveQuestion({{ $index }})">
                                        <i class="bi bi-save me-1"></i> Save Question
                                    </button>
                                    <button class="btn btn-sm btn-danger" 
                                        wire:click.prevent="deleteQuestion({{ $question['id'] ?? 0 }})"
                                        wire:confirm="Are you sure you want to delete this question?">
                                        <i class="bi bi-trash me-1"></i> Delete Question
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="alert alert-info text-center">
                        <i class="bi bi-info-circle fs-4 mb-3 d-block"></i>
                        <p class="mb-1">No questions in this set yet.</p>
                        <p>Click the "Add Question" button to create your first question.</p>
                    </div>
                @endforelse

                @if(count($questions) > 0)
                    <div class="d-grid gap-2 col-md-6 mx-auto mt-4">
                        <button class="btn btn-success btn-lg" wire:click.prevent="saveQuestions">
                            <i class="bi bi-save me-2"></i> Save All Questions
                        </button>
                    </div>
                @endif
            </div>
        </div>
    @endif
    
    <!-- Duplicate Question Set Modal -->
    @if($duplicateSetId)
        <div class="modal fade show d-block" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Duplicate Question Set</h5>
                        <button type="button" class="btn-close" wire:click="$set('duplicateSetId', null)"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">New Question Set Name</label>
                            <input type="text" class="form-control" wire:model="newDuplicateSetName" placeholder="Enter new name">
                            @error('newDuplicateSetName') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <p class="text-muted small">
                            <i class="bi bi-info-circle"></i>
                            This will create a copy of the question set with all its questions and options.
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('duplicateSetId', null)">Cancel</button>
                        <button type="button" class="btn btn-success" wire:click="confirmDuplicate">
                            <i class="bi bi-copy me-1"></i>Duplicate Question Set
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>