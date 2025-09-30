<x-dashboard.default title="Create New Exam (AJAX)" pageActions="examcenter">
<div class="container-fluid px-4 py-6">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-gradient-primary text-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-plus-circle me-2"></i>Create New Exam
                        </h4>
                        <a href="{{ route('examcenter') }}" class="btn btn-light btn-sm">
                            <i class="fas fa-arrow-left me-1"></i> Back to Exam Center
                        </a>
                    </div>
                </div>

                <div class="card-body p-0">
                    <form id="examForm" method="POST" action="{{ route('exams.store') }}">
                        @csrf
                        <div class="row g-0">
                            <!-- Left Column: Exam Details -->
                            <div class="col-lg-6 border-end">
                                <div class="p-4">
                                    <h5 class="fw-bold text-primary mb-4">
                                        <i class="fas fa-file-alt me-2"></i>Exam Details
                                    </h5>

                                    <!-- Basic Filters -->
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <label for="class" class="form-label fw-semibold">Class <span class="text-danger">*</span></label>
                                            <select class="form-select" id="class" name="class" required>
                                                <option value="">Select Class</option>
                                                @foreach($classes as $class)
                                                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                                                @endforeach
                                            </select>
                                            <div class="invalid-feedback"></div>
                                        </div>
                                        
                                        <div class="col-md-4">
                                            <label for="year" class="form-label fw-semibold">Year <span class="text-danger">*</span></label>
                                            <select class="form-select" id="year" name="year" required>
                                                <option value="">Select Year</option>
                                                @foreach($years as $year)
                                                    <option value="{{ $year->id }}">{{ $year->name }}</option>
                                                @endforeach
                                            </select>
                                            <div class="invalid-feedback"></div>
                                        </div>
                                        
                                        <div class="col-md-4">
                                            <label for="semester" class="form-label fw-semibold">Semester <span class="text-danger">*</span></label>
                                            <select class="form-select" id="semester" name="semester" required>
                                                <option value="">Select Semester</option>
                                                @foreach($semesters as $semester)
                                                    <option value="{{ $semester->id }}">{{ $semester->name }}</option>
                                                @endforeach
                                            </select>
                                            <div class="invalid-feedback"></div>
                                        </div>
                                    </div>

                                    <!-- Course Selection -->
                                    <div class="mb-3">
                                        <label for="course_code" class="form-label fw-semibold">Course <span class="text-danger">*</span></label>
                                        <select class="form-select" id="course_code" name="course_code" required>
                                            <option value="">Select Course</option>
                                        </select>
                                        <div class="invalid-feedback"></div>
                                        <small class="form-text text-muted">Please select Class, Year, and Semester first</small>
                                    </div>

                                    <!-- Exam Basic Info -->
                                    <div class="mb-3">
                                        <label for="exam_title" class="form-label fw-semibold">Exam Title <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="exam_title" name="exam_title" placeholder="Enter exam title" required>
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="exam_description" class="form-label fw-semibold">Description</label>
                                        <textarea class="form-control" id="exam_description" name="exam_description" rows="3" placeholder="Enter exam description (optional)"></textarea>
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="exam_type" class="form-label fw-semibold">Exam Type <span class="text-danger">*</span></label>
                                            <select class="form-select" id="exam_type" name="exam_type" required>
                                                <option value="">Select Type</option>
                                                <option value="mcq">Multiple Choice</option>
                                                <option value="short_answer">Short Answer</option>
                                                <option value="essay">Essay</option>
                                                <option value="mixed">Mixed</option>
                                            </select>
                                            <div class="invalid-feedback"></div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label for="exam_duration" class="form-label fw-semibold">Duration (minutes) <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" id="exam_duration" name="exam_duration" min="1" placeholder="120" required>
                                            <div class="invalid-feedback"></div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="questions_per_session" class="form-label fw-semibold">Questions per Session <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="questions_per_session" name="questions_per_session" min="1" placeholder="50" required>
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    <!-- Date/Time Settings -->
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="start_date" class="form-label fw-semibold">Start Date & Time <span class="text-danger">*</span></label>
                                            <input type="datetime-local" class="form-control" id="start_date" name="start_date" required>
                                            <div class="invalid-feedback"></div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label for="end_date" class="form-label fw-semibold">End Date & Time <span class="text-danger">*</span></label>
                                            <input type="datetime-local" class="form-control" id="end_date" name="end_date" required>
                                            <div class="invalid-feedback"></div>
                                        </div>
                                    </div>

                                    <!-- Security Settings -->
                                    <div class="mb-3">
                                        <label for="exam_password" class="form-label fw-semibold">Exam Password <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="exam_password" name="exam_password" placeholder="Enter or generate password" required>
                                            <button type="button" class="btn btn-outline-secondary" id="generatePasswordBtn">
                                                <i class="fas fa-random me-1"></i> Generate
                                            </button>
                                        </div>
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    <!-- Advanced Settings -->
                                    <div class="mb-3">
                                        <label for="user_id" class="form-label fw-semibold">Assigned Staff Member</label>
                                        <select class="form-select" id="user_id" name="user_id">
                                            <option value="">Auto-assign to current user</option>
                                            @foreach($staffUsers as $user)
                                                <option value="{{ $user->id }}">{{ $user->name }} - {{ $user->email }}</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="passing_mark" class="form-label fw-semibold">Passing Mark (%)</label>
                                            <input type="number" class="form-control" id="passing_mark" name="passing_mark" min="0" max="100" placeholder="60">
                                            <div class="invalid-feedback"></div>
                                        </div>
                                        
                                        <div class="col-md-6 d-flex align-items-end">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="enable_proctoring" name="enable_proctoring" value="1">
                                                <label class="form-check-label fw-semibold" for="enable_proctoring">
                                                    Enable Proctoring
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column: Question Sets -->
                            <div class="col-lg-6">
                                <div class="p-4">
                                    <h5 class="fw-bold text-success mb-4">
                                        <i class="fas fa-question-circle me-2"></i>Question Sets
                                    </h5>

                                    <!-- Question Sets Loading/Error States -->
                                    <div id="questionSetsContainer">
                                        <div id="questionSetsPlaceholder" class="text-center py-5">
                                            <i class="fas fa-arrow-left text-muted fs-1 mb-3"></i>
                                            <p class="text-muted">Please select a course to load question sets</p>
                                        </div>

                                        <div id="questionSetsLoading" class="text-center py-5" style="display: none;">
                                            <div class="spinner-border text-primary mb-3" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                            <p class="text-muted">Loading question sets...</p>
                                        </div>

                                        <div id="questionSetsError" class="alert alert-warning" style="display: none;">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            <span id="errorMessage">No question sets found for this course</span>
                                        </div>

                                        <div id="questionSetsList" style="display: none;">
                                            <!-- Question sets will be loaded here -->
                                        </div>
                                    </div>

                                    <!-- Selected Question Sets Summary -->
                                    <div id="selectedSummary" class="mt-4" style="display: none;">
                                        <div class="card border-success">
                                            <div class="card-header bg-light-success">
                                                <h6 class="mb-0 text-success fw-bold">
                                                    <i class="fas fa-check-circle me-2"></i>Selected Question Sets
                                                </h6>
                                            </div>
                                            <div class="card-body p-3">
                                                <div id="selectedSetsList"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="card-footer bg-light py-3">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('examcenter') }}" class="btn btn-secondary">
                                    <i class="fas fa-times me-1"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <i class="fas fa-save me-1"></i> Create Exam
                                </button>
                            </div>
                        </div>

                        <!-- Hidden inputs for selected question sets -->
                        <div id="hiddenInputs"></div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    let selectedQuestionSets = {};
    let questionSetConfigs = {};
    let availableQuestionSets = [];

    // Course loading when filters change
    function loadCourses() {
        const classId = $('#class').val();
        const yearId = $('#year').val();
        const semesterId = $('#semester').val();

        if (classId && yearId && semesterId) {
            $.ajax({
                url: '{{ route("exams.get-courses") }}',
                method: 'GET',
                data: {
                    class_id: classId,
                    year_id: yearId,
                    semester_id: semesterId
                },
                success: function(response) {
                    const courseSelect = $('#course_code');
                    courseSelect.empty().append('<option value="">Select Course</option>');
                    
                    if (response.success && response.courses.length > 0) {
                        response.courses.forEach(course => {
                            courseSelect.append(`<option value="${course.id}">${course.name} (${course.course_code})</option>`);
                        });
                    }
                    
                    // Clear question sets when courses change
                    resetQuestionSets();
                },
                error: function() {
                    showError('Failed to load courses. Please try again.');
                }
            });
        } else {
            $('#course_code').empty().append('<option value="">Select Course</option>');
            resetQuestionSets();
        }
    }

    // Question sets loading
    function loadQuestionSets(courseId) {
        showQuestionSetsLoading();
        
        $.ajax({
            url: '{{ route("exams.get-question-sets") }}',
            method: 'GET',
            data: { course_id: courseId },
            success: function(response) {
                if (response.success && response.question_sets.length > 0) {
                    availableQuestionSets = response.question_sets;
                    displayQuestionSets(response.question_sets);
                } else {
                    showQuestionSetsError('No question sets found for this course');
                }
            },
            error: function() {
                showQuestionSetsError('Failed to load question sets');
            }
        });
    }

    function displayQuestionSets(questionSets) {
        let html = '<div class="question-sets-list">';
        
        questionSets.forEach(set => {
            const isSelected = selectedQuestionSets.hasOwnProperty(set.id);
            html += `
                <div class="card mb-3 question-set-card ${isSelected ? 'border-success' : ''}" data-set-id="${set.id}">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-start">
                            <div class="form-check me-3">
                                <input class="form-check-input question-set-checkbox" type="checkbox" 
                                       value="${set.id}" id="set_${set.id}" ${isSelected ? 'checked' : ''}>
                            </div>
                            <div class="flex-grow-1">
                                <label class="form-check-label fw-semibold" for="set_${set.id}">
                                    ${set.name}
                                </label>
                                <p class="text-muted small mb-2">${set.description || 'No description available'}</p>
                                <span class="badge bg-primary">${set.questions_count} questions</span>
                            </div>
                        </div>
                        
                        <!-- Configuration Panel -->
                        <div class="question-set-config mt-3 ${isSelected ? '' : 'd-none'}">
                            <div class="border-top pt-3">
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Questions to Pick</label>
                                        <input type="number" class="form-control form-control-sm questions-to-pick" 
                                               min="1" max="${set.questions_count}" 
                                               placeholder="All (${set.questions_count})"
                                               value="${questionSetConfigs[set.id]?.questions_to_pick || ''}">
                                        <small class="text-muted">Leave empty to use all questions</small>
                                    </div>
                                    <div class="col-md-6 d-flex align-items-end">
                                        <div class="form-check">
                                            <input class="form-check-input shuffle-questions" type="checkbox" 
                                                   id="shuffle_${set.id}" 
                                                   ${questionSetConfigs[set.id]?.shuffle_questions ? 'checked' : ''}>
                                            <label class="form-check-label small fw-semibold" for="shuffle_${set.id}">
                                                Shuffle Questions
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        
        html += '</div>';
        
        $('#questionSetsList').html(html).show();
        $('#questionSetsPlaceholder, #questionSetsError, #questionSetsLoading').hide();
        
        updateSelectedSummary();
    }

    function showQuestionSetsLoading() {
        $('#questionSetsLoading').show();
        $('#questionSetsList, #questionSetsPlaceholder, #questionSetsError').hide();
    }

    function showQuestionSetsError(message) {
        $('#errorMessage').text(message);
        $('#questionSetsError').show();
        $('#questionSetsList, #questionSetsPlaceholder, #questionSetsLoading').hide();
    }

    function resetQuestionSets() {
        selectedQuestionSets = {};
        questionSetConfigs = {};
        availableQuestionSets = [];
        $('#questionSetsPlaceholder').show();
        $('#questionSetsList, #questionSetsError, #questionSetsLoading, #selectedSummary').hide();
        updateHiddenInputs();
    }

    function updateSelectedSummary() {
        const selectedCount = Object.keys(selectedQuestionSets).length;
        
        if (selectedCount > 0) {
            let html = '';
            Object.values(selectedQuestionSets).forEach(set => {
                const config = questionSetConfigs[set.id] || {};
                const questionsToUse = config.questions_to_pick || set.questions_count;
                html += `
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <div>
                            <span class="fw-semibold">${set.name}</span>
                            <small class="text-muted d-block">
                                Using ${questionsToUse} of ${set.questions_count} questions
                                ${config.shuffle_questions ? ' • Shuffled' : ''}
                            </small>
                        </div>
                        <span class="badge bg-success">${questionsToUse} questions</span>
                    </div>
                `;
            });
            
            $('#selectedSetsList').html(html);
            $('#selectedSummary').show();
        } else {
            $('#selectedSummary').hide();
        }
        
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
        
        $('#hiddenInputs').html(html);
    }

    // Event Handlers
    $('#class, #year, #semester').change(loadCourses);

    $('#course_code').change(function() {
        const courseId = $(this).val();
        if (courseId) {
            loadQuestionSets(courseId);
        } else {
            resetQuestionSets();
        }
    });

    // Question set selection
    $(document).on('change', '.question-set-checkbox', function() {
        const setId = $(this).val();
        const isChecked = $(this).is(':checked');
        const card = $(this).closest('.question-set-card');
        const configPanel = card.find('.question-set-config');
        
        if (isChecked) {
            const set = availableQuestionSets.find(s => s.id == setId);
            if (set) {
                selectedQuestionSets[setId] = set;
                card.addClass('border-success');
                configPanel.removeClass('d-none');
                
                // Initialize config if not exists
                if (!questionSetConfigs[setId]) {
                    questionSetConfigs[setId] = {
                        questions_to_pick: '',
                        shuffle_questions: false
                    };
                }
            }
        } else {
            delete selectedQuestionSets[setId];
            delete questionSetConfigs[setId];
            card.removeClass('border-success');
            configPanel.addClass('d-none');
        }
        
        updateSelectedSummary();
    });

    // Question set configuration changes
    $(document).on('input', '.questions-to-pick', function() {
        const setId = $(this).closest('.question-set-card').data('set-id');
        if (!questionSetConfigs[setId]) questionSetConfigs[setId] = {};
        questionSetConfigs[setId].questions_to_pick = $(this).val();
        updateSelectedSummary();
    });

    $(document).on('change', '.shuffle-questions', function() {
        const setId = $(this).closest('.question-set-card').data('set-id');
        if (!questionSetConfigs[setId]) questionSetConfigs[setId] = {};
        questionSetConfigs[setId].shuffle_questions = $(this).is(':checked');
        updateSelectedSummary();
    });

    // Generate password
    $('#generatePasswordBtn').click(function() {
        $.ajax({
            url: '{{ route("exams.generate-password") }}',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    $('#exam_password').val(response.password);
                }
            }
        });
    });

    // Form submission
    $('#examForm').submit(function(e) {
        e.preventDefault();
        
        // Basic client-side validation
        if (Object.keys(selectedQuestionSets).length === 0) {
            showError('Please select at least one question set.');
            return;
        }
        
        const submitBtn = $('#submitBtn');
        const originalText = submitBtn.html();
        
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Creating...');
        
        // Clear previous errors
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    showSuccess(response.message);
                    if (response.redirect) {
                        window.location.href = response.redirect;
                    }
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    Object.entries(errors).forEach(([field, messages]) => {
                        const input = $(`[name="${field}"]`);
                        input.addClass('is-invalid');
                        input.siblings('.invalid-feedback').text(messages[0]);
                    });
                    showError('Please correct the highlighted errors.');
                } else {
                    showError(xhr.responseJSON?.message || 'An error occurred. Please try again.');
                }
            },
            complete: function() {
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });

    // Utility functions
    function showError(message) {
        // You can implement a toast/alert system here
        alert('Error: ' + message);
    }

    function showSuccess(message) {
        // You can implement a toast/alert system here
        alert('Success: ' + message);
    }
});
</script>
@endpush
</x-dashboard.default>