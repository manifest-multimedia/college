<x-dashboard.default title="Exam Center" pageActions="examcenter">
    <div class="pb-20 mb-10 row">
        <div class="flex-wrap p-8 shadow justify-content-center d-flex flex-md-nowrap card-rounded" style="background: linear-gradient(90deg, #20AA3E 0%, #03A588 100%);">
            <!--begin::Content-->
            <div class="text-center">
                <!--begin::Title-->
                <div class="mb-2 text-center text-white fs-1 fs-lg-2qx fw-bold">Start Building Your Exams, Assignments, and Questions
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor" class="bi bi-patch-check" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M10.354 6.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7 8.793l2.646-2.647a.5.5 0 0 1 .708 0"/>
                        <path d="m10.273 2.513-.921-.944.715-.698.622.637.89-.011a2.89 2.89 0 0 1 2.924 2.924l-.01.89.636.622a2.89 2.89 0 0 1 0 4.134l-.637.622.011.89a2.89 2.89 0 0 1-2.924 2.924l-.89-.01-.622.636a2.89 2.89 0 0 1-4.134 0l-.622-.637-.89.011a2.89 2.89 0 0 1-2.924-2.924l.01-.89-.636-.622a2.89 2.89 0 0 1 0-4.134l.637-.622-.011-.89a2.89 2.89 0 0 1 2.924-2.924l.89.01.622-.636a2.89 2.89 0 0 1 4.134 0l-.715.698a1.89 1.89 0 0 0-2.704 0l-.92.944-1.32-.016a1.89 1.89 0 0 0-1.911 1.912l.016 1.318-.944.921a1.89 1.89 0 0 0 0 2.704l.944.92-.016 1.32a1.89 1.89 0 0 0 1.912 1.911l1.318-.016.921.944a1.89 1.89 0 0 0 2.704 0l.92-.944 1.32.016a1.89 1.89 0 0 0 1.911-1.912l-.016-1.318.944-.921a1.89 1.89 0 0 0 0-2.704l-.944-.92.016-1.32a1.89 1.89 0 0 0-1.912-1.911z"/>
                    </svg>
                </div>
                <!--end::Title-->
                <!--begin::Description-->
                <div class="text-white opacity-75 fs-6 fs-lg-5 fw-semibold"></div>
            </div>
            <!--end::Content-->
        </div>
    </div>
    
    <div class="mt-20">
        <!-- Header -->
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">Create New Exam</h3>
            </div>
        </div>

        <!-- Error Messages -->
        <div id="errorMessages" class="alert alert-danger mb-3" style="display: none;">
            <ul class="mb-0" id="errorList"></ul>
        </div>

        <!-- Main Form -->
        <form id="examForm" method="POST" action="{{ route('exams.store') }}">
            @csrf
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
                                <select class="form-select" id="class" name="class">
                                    <option value="">Select a program</option>
                                    @foreach($classes as $class)
                                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Academic Year -->
                            <div class="mb-3">
                                <label for="year" class="form-label">Academic Year</label>
                                <select class="form-select" id="year" name="year">
                                    <option value="">Select academic year</option>
                                    @foreach($years as $year)
                                        <option value="{{ $year->id }}">{{ $year->name }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Semester -->
                            <div class="mb-3">
                                <label for="semester" class="form-label">Semester</label>
                                <select class="form-select" id="semester" name="semester">
                                    <option value="">Select semester</option>
                                    @foreach($semesters as $semester)
                                        <option value="{{ $semester->id }}">{{ $semester->name }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Subject -->
                            <div class="mb-3">
                                <label for="course_code" class="form-label">Subject</label>
                                <select class="form-select" id="course_code" name="course_code">
                                    <option value="">Select a subject</option>
                                </select>
                                <div class="invalid-feedback"></div>
                                <small class="form-text text-muted">Please select Program, Academic Year, and Semester first</small>
                            </div>

                            <!-- Exam Title -->
                            <div class="mb-3">
                                <label for="exam_title" class="form-label">Exam Title</label>
                                <input type="text" id="exam_title" name="exam_title" class="form-control" placeholder="Enter exam title">
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Exam Description -->
                            <div class="mb-3">
                                <label for="exam_description" class="form-label">Description (Optional)</label>
                                <textarea id="exam_description" name="exam_description" class="form-control" rows="3" placeholder="Enter exam description"></textarea>
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Exam Type -->
                            <div class="mb-3">
                                <label for="exam_type" class="form-label">Exam Type</label>
                                <select id="exam_type" name="exam_type" class="form-select">
                                    <option value="">Select exam type</option>
                                    <option value="mcq">MCQ</option>
                                    <option value="short_answer">Short Answer</option>
                                    <option value="essay">Essay</option>
                                    <option value="mixed">Mixed</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Exam Duration -->
                            <div class="mb-3">
                                <label for="exam_duration" class="form-label">Duration (minutes)</label>
                                <input type="number" id="exam_duration" name="exam_duration" class="form-control" min="1">
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Questions Per Session -->
                            <div class="mb-3">
                                <label for="questions_per_session" class="form-label">Total Questions Per Session</label>
                                <input type="number" id="questions_per_session" name="questions_per_session" class="form-control" min="1">
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- User Assignment (Super Admin Only) -->
                            @if(Auth::user()->role == 'Super Admin')
                                <div class="mb-3">
                                    <label for="user_id" class="form-label">Assign to User</label>
                                    <select class="form-select" id="user_id" name="user_id">
                                        <option value="">Select a user</option>
                                        @foreach($staffUsers as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback"></div>
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
                            <div id="questionSetsContainer">
                                <div id="questionSetsPlaceholder" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-question-circle fa-2x mb-2"></i>
                                        <p>No question sets available for the selected subject.</p>
                                        <p class="small">Please select a subject first to see available question sets.</p>
                                    </div>
                                </div>

                                <div id="questionSetsLoading" class="text-center py-4" style="display: none;">
                                    <div class="spinner-border text-primary mb-3" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="text-muted">Loading question sets...</p>
                                </div>

                                <div id="questionSetsList" style="display: none;" class="question-sets-container" style="max-height: 300px; overflow-y: auto;">
                                    <!-- Question sets will be loaded here -->
                                </div>
                            </div>
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
                                    <input type="datetime-local" id="start_date" name="start_date" class="form-control">
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="col-md-6">
                                    <label for="end_date" class="form-label">End Date & Time</label>
                                    <input type="datetime-local" id="end_date" name="end_date" class="form-control">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>

                            <!-- Exam Password -->
                            <div class="mb-3">
                                <label for="exam_password" class="form-label">Exam Password</label>
                                <div class="input-group">
                                    <input type="text" id="exam_password" name="exam_password" class="form-control" readonly>
                                    <button type="button" class="btn btn-outline-secondary" id="regeneratePasswordBtn">
                                        <i class="fas fa-sync"></i> Regenerate
                                    </button>
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Passing Mark -->
                            <div class="mb-3">
                                <label for="passing_mark" class="form-label">Passing Mark (%)</label>
                                <input type="number" id="passing_mark" name="passing_mark" class="form-control" min="0" max="100">
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Enable Proctoring -->
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="enable_proctoring" name="enable_proctoring" value="1">
                                    <label class="form-check-label" for="enable_proctoring">
                                        Enable Proctoring
                                    </label>
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary btn-lg w-100" id="submitBtn">
                            <i class="fas fa-plus"></i> Create Exam
                        </button>
                    </div>
                </div>
            </div>

            <!-- Hidden inputs for selected question sets -->
            <div id="hiddenInputs"></div>
        </form>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let selectedQuestionSets = {};
            let questionSetConfigs = {};
            let availableQuestionSets = [];

            // Generate initial password
            generatePassword();

            // Course loading when filters change
            function loadCourses() {
                const classId = document.getElementById('class').value;
                const yearId = document.getElementById('year').value;
                const semesterId = document.getElementById('semester').value;

                if (classId && yearId && semesterId) {
                    fetch('{{ route("exams.get-courses") }}?' + new URLSearchParams({
                        class_id: classId,
                        year_id: yearId,
                        semester_id: semesterId
                    }))
                    .then(response => response.json())
                    .then(data => {
                        const courseSelect = document.getElementById('course_code');
                        courseSelect.innerHTML = '<option value="">Select a subject</option>';
                        
                        if (data.success && data.courses.length > 0) {
                            data.courses.forEach(course => {
                                courseSelect.innerHTML += `<option value="${course.id}">${course.name} (${course.course_code})</option>`;
                            });
                        }
                        
                        // Clear question sets when courses change
                        resetQuestionSets();
                    })
                    .catch(error => {
                        console.error('Error loading courses:', error);
                        showError('Failed to load courses. Please try again.');
                    });
                } else {
                    document.getElementById('course_code').innerHTML = '<option value="">Select a subject</option>';
                    resetQuestionSets();
                }
            }

            // Question sets loading
            function loadQuestionSets(courseId) {
                showQuestionSetsLoading();
                
                fetch('{{ route("exams.get-question-sets") }}?' + new URLSearchParams({
                    course_id: courseId
                }))
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.question_sets.length > 0) {
                        availableQuestionSets = data.question_sets;
                        displayQuestionSets(data.question_sets);
                    } else {
                        showQuestionSetsPlaceholder();
                    }
                })
                .catch(error => {
                    console.error('Error loading question sets:', error);
                    showError('Failed to load question sets');
                    showQuestionSetsPlaceholder();
                });
            }

            function displayQuestionSets(questionSets) {
                let html = '';
                
                questionSets.forEach(set => {
                    const isSelected = selectedQuestionSets.hasOwnProperty(set.id);
                    const borderClass = isSelected ? 'border-primary bg-light' : 'border-light';
                    
                    html += `
                        <div class="question-set-item border rounded p-3 mb-2 ${borderClass}" data-set-id="${set.id}">
                            <div class="form-check">
                                <input class="form-check-input question-set-checkbox" type="checkbox" 
                                       value="${set.id}" id="questionSet${set.id}" ${isSelected ? 'checked' : ''}>
                                <label class="form-check-label w-100" for="questionSet${set.id}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>${set.name}</strong>
                                            <div class="text-muted small">${set.description || 'No description'}</div>
                                        </div>
                                        <span class="badge bg-secondary">${set.questions_count || 0} questions</span>
                                    </div>
                                </label>
                            </div>
                            
                            ${isSelected ? `
                                <div class="question-set-config mt-3 pt-2 border-top">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label class="form-label small">Questions to Pick</label>
                                            <input type="number" class="form-control form-control-sm questions-to-pick" 
                                                   min="1" max="${set.questions_count || 1}"
                                                   placeholder="All"
                                                   value="${questionSetConfigs[set.id]?.questions_to_pick || ''}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small">Shuffle Questions</label>
                                            <select class="form-select form-select-sm shuffle-questions">
                                                <option value="0" ${!questionSetConfigs[set.id]?.shuffle_questions ? 'selected' : ''}>No</option>
                                                <option value="1" ${questionSetConfigs[set.id]?.shuffle_questions ? 'selected' : ''}>Yes</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            ` : ''}
                        </div>
                    `;
                });
                
                document.getElementById('questionSetsList').innerHTML = html;
                document.getElementById('questionSetsList').style.display = 'block';
                document.getElementById('questionSetsPlaceholder').style.display = 'none';
                document.getElementById('questionSetsLoading').style.display = 'none';
                
                updateHiddenInputs();
            }

            function showQuestionSetsLoading() {
                document.getElementById('questionSetsLoading').style.display = 'block';
                document.getElementById('questionSetsList').style.display = 'none';
                document.getElementById('questionSetsPlaceholder').style.display = 'none';
            }

            function showQuestionSetsPlaceholder() {
                document.getElementById('questionSetsPlaceholder').style.display = 'block';
                document.getElementById('questionSetsList').style.display = 'none';
                document.getElementById('questionSetsLoading').style.display = 'none';
            }

            function resetQuestionSets() {
                selectedQuestionSets = {};
                questionSetConfigs = {};
                availableQuestionSets = [];
                showQuestionSetsPlaceholder();
                updateHiddenInputs();
            }

            function updateHiddenInputs() {
                let html = '';
                
                // Selected question sets
                Object.keys(selectedQuestionSets).forEach(setId => {
                    html += `<input type="hidden" name="selected_question_sets[]" value="${setId}">`;
                });
                
                // Question set configurations
                Object.entries(questionSetConfigs).forEach(([setId, config]) => {
                    if (selectedQuestionSets[setId]) {
                        html += `<input type="hidden" name="question_set_configs[${setId}][questions_to_pick]" value="${config.questions_to_pick || ''}">`;
                        html += `<input type="hidden" name="question_set_configs[${setId}][shuffle_questions]" value="${config.shuffle_questions ? '1' : '0'}">`;
                    }
                });
                
                document.getElementById('hiddenInputs').innerHTML = html;
            }

            // Event Handlers
            document.getElementById('class').addEventListener('change', loadCourses);
            document.getElementById('year').addEventListener('change', loadCourses);
            document.getElementById('semester').addEventListener('change', loadCourses);

            document.getElementById('course_code').addEventListener('change', function() {
                const courseId = this.value;
                if (courseId) {
                    loadQuestionSets(courseId);
                } else {
                    resetQuestionSets();
                }
            });

            // Question set selection (event delegation)
            document.addEventListener('change', function(e) {
                if (e.target.classList.contains('question-set-checkbox')) {
                    const setId = e.target.value;
                    const isChecked = e.target.checked;
                    const card = e.target.closest('.question-set-item');
                    
                    if (isChecked) {
                        const set = availableQuestionSets.find(s => s.id == setId);
                        if (set) {
                            selectedQuestionSets[setId] = set;
                            card.classList.add('border-primary', 'bg-light');
                            
                            // Initialize config if not exists
                            if (!questionSetConfigs[setId]) {
                                questionSetConfigs[setId] = {
                                    questions_to_pick: '',
                                    shuffle_questions: false
                                };
                            }
                            
                            // Add configuration panel
                            const configHtml = `
                                <div class="question-set-config mt-3 pt-2 border-top">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label class="form-label small">Questions to Pick</label>
                                            <input type="number" class="form-control form-control-sm questions-to-pick" 
                                                   min="1" max="${set.questions_count || 1}"
                                                   placeholder="All"
                                                   value="${questionSetConfigs[setId]?.questions_to_pick || ''}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small">Shuffle Questions</label>
                                            <select class="form-select form-select-sm shuffle-questions">
                                                <option value="0" ${!questionSetConfigs[setId]?.shuffle_questions ? 'selected' : ''}>No</option>
                                                <option value="1" ${questionSetConfigs[setId]?.shuffle_questions ? 'selected' : ''}>Yes</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            `;
                            
                            // Remove existing config if any
                            const existingConfig = card.querySelector('.question-set-config');
                            if (existingConfig) existingConfig.remove();
                            
                            // Add new config
                            card.insertAdjacentHTML('beforeend', configHtml);
                        }
                    } else {
                        delete selectedQuestionSets[setId];
                        delete questionSetConfigs[setId];
                        card.classList.remove('border-primary', 'bg-light');
                        
                        // Remove configuration panel
                        const configPanel = card.querySelector('.question-set-config');
                        if (configPanel) configPanel.remove();
                    }
                    
                    updateHiddenInputs();
                }
            });

            // Question set configuration changes (event delegation)
            document.addEventListener('input', function(e) {
                if (e.target.classList.contains('questions-to-pick')) {
                    const setId = e.target.closest('.question-set-item').dataset.setId;
                    if (!questionSetConfigs[setId]) questionSetConfigs[setId] = {};
                    questionSetConfigs[setId].questions_to_pick = e.target.value;
                    updateHiddenInputs();
                }
            });

            document.addEventListener('change', function(e) {
                if (e.target.classList.contains('shuffle-questions')) {
                    const setId = e.target.closest('.question-set-item').dataset.setId;
                    if (!questionSetConfigs[setId]) questionSetConfigs[setId] = {};
                    questionSetConfigs[setId].shuffle_questions = e.target.value === '1';
                    updateHiddenInputs();
                }
            });

            // Generate password
            function generatePassword() {
                fetch('{{ route("exams.generate-password") }}')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('exam_password').value = data.password;
                    }
                })
                .catch(error => {
                    console.error('Error generating password:', error);
                });
            }

            document.getElementById('regeneratePasswordBtn').addEventListener('click', generatePassword);

            // Form submission
            document.getElementById('examForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Basic client-side validation
                if (Object.keys(selectedQuestionSets).length === 0) {
                    showError('Please select at least one question set.');
                    return;
                }
                
                const submitBtn = document.getElementById('submitBtn');
                const originalText = submitBtn.innerHTML;
                
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating...';
                
                // Clear previous errors
                clearErrors();
                
                const formData = new FormData(this);
                
                fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showSuccess(data.message);
                        if (data.redirect) {
                            window.location.href = data.redirect;
                        }
                    } else {
                        if (data.errors) {
                            displayValidationErrors(data.errors);
                        }
                        showError(data.message || 'An error occurred. Please try again.');
                    }
                })
                .catch(error => {
                    console.error('Error submitting form:', error);
                    showError('An error occurred. Please try again.');
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                });
            });

            // Utility functions
            function clearErrors() {
                document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                document.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
                document.getElementById('errorMessages').style.display = 'none';
            }

            function displayValidationErrors(errors) {
                Object.entries(errors).forEach(([field, messages]) => {
                    const input = document.querySelector(`[name="${field}"]`);
                    if (input) {
                        input.classList.add('is-invalid');
                        const feedback = input.parentNode.querySelector('.invalid-feedback');
                        if (feedback) {
                            feedback.textContent = messages[0];
                        }
                    }
                });
            }

            function showError(message) {
                const errorDiv = document.getElementById('errorMessages');
                const errorList = document.getElementById('errorList');
                errorList.innerHTML = `<li>${message}</li>`;
                errorDiv.style.display = 'block';
                
                // Scroll to error
                errorDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }

            function showSuccess(message) {
                // Simple alert for now - you can enhance this
                alert('Success: ' + message);
            }
        });
    </script>
    @endpush
</x-dashboard.default>