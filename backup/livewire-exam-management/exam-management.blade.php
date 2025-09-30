<div>
    <div class="mt-20">
        <!-- Header -->
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">Create New Exam</h3>
            </div>
        </div>

        <!-- Error Messages -->
        @if($errors->any())
            <div class="alert alert-danger mb-3">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Main Form -->
        <form wire:submit.prevent="createExam">
            <div class="row">
                <!-- Left Column - Basic Information -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Basic Information</h5>
                        </div>
                        <div class="card-body">
                            <!-- Program -->
                            <div class="mb-3">
                                <label for="class" class="form-label">Program</label>
                                <select class="form-select" wire:model.live="class" id="class">
                                    <option value="">Select a program</option>
                                    @foreach ($classes as $class)
                                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                                    @endforeach
                                </select>
                                @error('class')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Academic Year -->
                            <div class="mb-3">
                                <label for="year" class="form-label">Academic Year</label>
                                <select class="form-select" wire:model.live="year" id="year">
                                    <option value="">Select academic year</option>
                                    @foreach ($years as $year)
                                        <option value="{{ $year->id }}">{{ $year->name }}</option>
                                    @endforeach
                                </select>
                                @error('year')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Semester -->
                            <div class="mb-3">
                                <label for="semester" class="form-label">Semester</label>
                                <select class="form-select" wire:model.live="semester" id="semester">
                                    <option value="">Select semester</option>
                                    @foreach ($semesters as $semester)
                                        <option value="{{ $semester->id }}">{{ $semester->name }}</option>
                                    @endforeach
                                </select>
                                @error('semester')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Subject -->
                            <div class="mb-3">
                                <label for="course_code" class="form-label">Subject</label>
                                <select class="form-select" wire:model.live="course_code" id="course_code">
                                    <option value="">Select a subject</option>
                                    @foreach ($courses as $course)
                                        <option value="{{ $course->id }}">{{ $course->name }} ({{ $course->course_code }})</option>
                                    @endforeach
                                </select>
                                @error('course_code')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Exam Title -->
                            <div class="mb-3">
                                <label for="exam_title" class="form-label">Exam Title</label>
                                <input type="text" id="exam_title" class="form-control" wire:model="exam_title" placeholder="Enter exam title">
                                @error('exam_title')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Exam Description -->
                            <div class="mb-3">
                                <label for="exam_description" class="form-label">Description (Optional)</label>
                                <textarea id="exam_description" class="form-control" wire:model="exam_description" rows="3" placeholder="Enter exam description"></textarea>
                                @error('exam_description')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Exam Type -->
                            <div class="mb-3">
                                <label for="exam_type" class="form-label">Exam Type</label>
                                <select id="exam_type" class="form-select" wire:model="exam_type">
                                    <option value="">Select exam type</option>
                                    <option value="mcq">MCQ</option>
                                    <option value="short_answer">Short Answer</option>
                                    <option value="essay">Essay</option>
                                    <option value="mixed">Mixed</option>
                                </select>
                                @error('exam_type')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Exam Duration -->
                            <div class="mb-3">
                                <label for="exam_duration" class="form-label">Duration (minutes)</label>
                                <input type="number" id="exam_duration" class="form-control" wire:model="exam_duration" min="1">
                                @error('exam_duration')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Questions Per Session -->
                            <div class="mb-3">
                                <label for="questions_per_session" class="form-label">Total Questions Per Session</label>
                                <input type="number" id="questions_per_session" class="form-control" wire:model="questions_per_session" min="1">
                                @error('questions_per_session')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- User Assignment (Super Admin Only) -->
                            @if(Auth::user()->role == 'Super Admin')
                                <div class="mb-3">
                                    <label for="user_id" class="form-label">Assign to User</label>
                                    <select class="form-select" wire:model="user_id" id="user_id">
                                        <option value="">Select a user</option>
                                        @foreach ($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                        @endforeach
                                    </select>
                                    @error('user_id')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Right Column - Question Sets & Advanced Settings -->
                <div class="col-md-6">
                    <!-- Question Sets Selection -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h5 class="card-title">Question Sets</h5>
                            <p class="text-muted small mb-0">Select question sets to include in this exam</p>
                        </div>
                        <div class="card-body">
                            @if($availableQuestionSets && count($availableQuestionSets) > 0)
                                <div class="question-sets-container" style="max-height: 300px; overflow-y: auto;">
                                    @foreach($availableQuestionSets as $questionSet)
                                        <div class="question-set-item border rounded p-3 mb-2 {{ in_array($questionSet->id, $selectedQuestionSets) ? 'border-primary bg-light' : 'border-light' }}">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" 
                                                       wire:click="toggleQuestionSet({{ $questionSet->id }})"
                                                       {{ in_array($questionSet->id, $selectedQuestionSets) ? 'checked' : '' }}
                                                       id="questionSet{{ $questionSet->id }}">
                                                <label class="form-check-label w-100" for="questionSet{{ $questionSet->id }}">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <strong>{{ $questionSet->name }}</strong>
                                                            <div class="text-muted small">{{ $questionSet->description ?? 'No description' }}</div>
                                                        </div>
                                                        <span class="badge bg-secondary">{{ $questionSet->questions_count ?? 0 }} questions</span>
                                                    </div>
                                                </label>
                                            </div>
                                            
                                            @if(in_array($questionSet->id, $selectedQuestionSets))
                                                <div class="mt-3 pt-2 border-top">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <label class="form-label small">Questions to Pick</label>
                                                            <input type="number" class="form-control form-control-sm" 
                                                                   wire:model="questionSetConfigs.{{ $questionSet->id }}.questions_to_pick"
                                                                   min="1" max="{{ $questionSet->questions_count ?? 1 }}"
                                                                   placeholder="All">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label small">Shuffle Questions</label>
                                                            <select class="form-select form-select-sm" 
                                                                    wire:model="questionSetConfigs.{{ $questionSet->id }}.shuffle_questions">
                                                                <option value="0">No</option>
                                                                <option value="1">Yes</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-question-circle fa-2x mb-2"></i>
                                        <p>No question sets available for the selected subject.</p>
                                        <p class="small">Please select a subject first to see available question sets.</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Advanced Settings -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Advanced Settings</h5>
                        </div>
                        <div class="card-body">
                            <!-- Exam Dates -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="start_date" class="form-label">Start Date & Time</label>
                                    <input type="datetime-local" id="start_date" class="form-control" wire:model="start_date">
                                    @error('start_date')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="end_date" class="form-label">End Date & Time</label>
                                    <input type="datetime-local" id="end_date" class="form-control" wire:model="end_date">
                                    @error('end_date')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <!-- Exam Password -->
                            <div class="mb-3">
                                <label for="exam_password" class="form-label">Exam Password</label>
                                <div class="input-group">
                                    <input type="text" id="exam_password" class="form-control" wire:model="exam_password" readonly>
                                    <button type="button" class="btn btn-outline-secondary" wire:click="regeneratePassword()">
                                        <i class="fas fa-sync"></i> Regenerate
                                    </button>
                                </div>
                                @error('exam_password')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Passing Mark -->
                            <div class="mb-3">
                                <label for="passing_mark" class="form-label">Passing Mark (%)</label>
                                <input type="number" id="passing_mark" class="form-control" wire:model="passing_mark" min="0" max="100">
                                @error('passing_mark')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Enable Proctoring -->
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" wire:model="enable_proctoring" id="enable_proctoring">
                                    <label class="form-check-label" for="enable_proctoring">
                                        Enable Proctoring
                                    </label>
                                </div>
                                @error('enable_proctoring')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            <i class="fas fa-plus"></i> Create Exam
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
