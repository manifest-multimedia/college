<div>
    <div class="container">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="card-title mb-0">Edit Exam</h3>
                    <!-- Total Questions -->
                    <p class="text-muted mb-0">
                       {{ $totalQuestions }} Total Questions
                    </p>
                </div>
                <button type="button" class="btn btn-outline-primary" wire:click="toggleQuestionSetManagement">
                    <i class="bi bi-collection"></i>
                    {{ $managingQuestionSets ? 'Hide Question Sets' : 'Manage Question Sets' }}
                </button>
            </div>
            
            <div class="card-body">
                @if (session()->has('message'))
                    <div class="alert alert-success">
                        {{ session('message') }}
                    </div>
                @endif

                @if (session()->has('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif

                <!-- Display Course Info (Non-editable) -->
                <div class="mb-4">
                    <h4>{{ $course->name }}</h4>
                    <p class="text-muted">{{ $exam->description }}</p>
                </div>

                @if($managingQuestionSets)
                    <!-- Question Set Management Section -->
                    <div class="mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="bi bi-collection me-2"></i>Question Set Assignment
                                </h5>
                            </div>
                            <div class="card-body">
                                <!-- Assign New Question Set -->
                                <div class="mb-4">
                                    <h6>Assign Question Set</h6>
                                    <div class="row g-3 align-items-end">
                                        <div class="col-md-8">
                                            <label for="questionSetSelect" class="form-label">Available Question Sets</label>
                                            <select id="questionSetSelect" class="form-select" wire:model="selectedQuestionSetId">
                                                <option value="">Select a question set...</option>
                                                @foreach($availableQuestionSets as $questionSet)
                                                    @php
                                                        $isAlreadyAssigned = $assignedQuestionSets->contains('id', $questionSet->id);
                                                        $questionCount = $questionSet->questions()->count();
                                                    @endphp
                                                    @if(!$isAlreadyAssigned)
                                                        <option value="{{ $questionSet->id }}">
                                                            {{ $questionSet->name }} ({{ $questionCount }} questions) - {{ ucfirst($questionSet->difficulty_level) }}
                                                        </option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <button type="button" class="btn btn-success w-100" wire:click="assignQuestionSet" 
                                                {{ !$selectedQuestionSetId ? 'disabled' : '' }}>
                                                <i class="bi bi-plus-circle me-1"></i>Assign Set
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Currently Assigned Question Sets -->
                                @if($assignedQuestionSets->count() > 0)
                                    <div>
                                        <h6>Currently Assigned Question Sets</h6>
                                        <div class="table-responsive">
                                            <table class="table table-bordered">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Question Set</th>
                                                        <th>Total Questions</th>
                                                        <th>Questions to Pick</th>
                                                        <th>Shuffle</th>
                                                        <th>Difficulty</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($assignedQuestionSets as $questionSet)
                                                        @php
                                                            $questionCount = $questionSet->questions()->count();
                                                            $questionsToPick = $questionsToPickPerSet[$questionSet->id] ?? 0;
                                                            $effectiveQuestions = $questionsToPick > 0 && $questionsToPick <= $questionCount ? $questionsToPick : $questionCount;
                                                        @endphp
                                                        <tr>
                                                            <td>
                                                                <strong>{{ $questionSet->name }}</strong>
                                                                <br>
                                                                <small class="text-muted">{{ Str::limit($questionSet->description, 50) }}</small>
                                                            </td>
                                                            <td>{{ $questionCount }}</td>
                                                            <td>
                                                                <input type="number" class="form-control form-control-sm" 
                                                                    wire:model="questionsToPickPerSet.{{ $questionSet->id }}"
                                                                    wire:change="updateQuestionSetConfig({{ $questionSet->id }})"
                                                                    min="0" max="{{ $questionCount }}"
                                                                    placeholder="All ({{ $questionCount }})" style="width: 100px;">
                                                                <small class="text-muted">0 = use all</small>
                                                            </td>
                                                            <td>
                                                                <div class="form-check form-switch">
                                                                    <input class="form-check-input" type="checkbox" 
                                                                        wire:model="shuffleQuestionsPerSet.{{ $questionSet->id }}"
                                                                        wire:change="updateQuestionSetConfig({{ $questionSet->id }})"
                                                                        id="shuffle_{{ $questionSet->id }}">
                                                                    <label class="form-check-label" for="shuffle_{{ $questionSet->id }}">
                                                                        {{ $shuffleQuestionsPerSet[$questionSet->id] ?? false ? 'Yes' : 'No' }}
                                                                    </label>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <span class="badge {{ match($questionSet->difficulty_level) {
                                                                    'easy' => 'bg-success',
                                                                    'hard' => 'bg-danger',
                                                                    default => 'bg-warning',
                                                                } }}">
                                                                    {{ ucfirst($questionSet->difficulty_level) }}
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <button type="button" class="btn btn-outline-danger btn-sm" 
                                                                    wire:click="removeQuestionSet({{ $questionSet->id }})"
                                                                    wire:confirm="Are you sure you want to remove this question set from the exam?">
                                                                    <i class="bi bi-trash"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        
                                        <div class="mt-3 p-3 bg-light rounded">
                                            <h6>Question Summary</h6>
                                            <p class="mb-0">
                                                <strong>Total Questions Available:</strong> {{ $totalQuestions }}
                                                <br>
                                                <small class="text-muted">
                                                    This includes questions from assigned question sets and any direct questions.
                                                </small>
                                            </p>
                                        </div>
                                    </div>
                                @else
                                    <div class="alert alert-info">
                                        <i class="bi bi-info-circle me-2"></i>
                                        No question sets assigned yet. Assign question sets to enable dynamic question selection for this exam.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                <form wire:submit.prevent="updateExam">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="duration" class="form-label">Duration (minutes)</label>
                                <input type="number" class="form-control @error('duration') is-invalid @enderror" 
                                    wire:model="duration" id="duration">
                                @error('duration') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="questions_per_session" class="form-label">Questions Per Session</label>
                                <input type="number" class="form-control @error('questions_per_session') is-invalid @enderror" 
                                    wire:model="questions_per_session" id="questions_per_session">
                                @error('questions_per_session') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password" class="form-label">Exam Password</label>
                                <input type="text" class="form-control @error('password') is-invalid @enderror" 
                                    wire:model="password" id="password">
                                @error('password') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status (Current: {{ $status }})</label>
                                <select class="form-select @error('status') is-invalid @enderror" 
                                    wire:model="status" id="status">
                                    <option value="">Select Status</option>
                                    <option value="upcoming">Upcoming</option>
                                    <option value="active">Active</option>
                                    <option value="completed">Completed</option>
                                </select>
                                @error('status') 
                                    <span class="invalid-feedback">{{ $message }}</span> 
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="passing_percentage" class="form-label">Passing Percentage</label>
                        <input type="number" step="0.01" class="form-control @error('passing_percentage') is-invalid @enderror" 
                            wire:model="passing_percentage" id="passing_percentage">
                        @error('passing_percentage') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-secondary" wire:click="cancel">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Exam</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div> 