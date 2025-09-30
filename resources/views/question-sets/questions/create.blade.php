<x-dashboard.default>
    <x-slot name="title">
        Add Question
    </x-slot>

    <div class="container-fluid">
        <!-- Breadcrumb Navigation -->
        <div class="row">
            <div class="col-12">
                <nav aria-label="breadcrumb" class="mb-4">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}" class="text-decoration-none">
                                <i class="bi bi-house-door me-1"></i>Dashboard
                            </a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('question.sets') }}" class="text-decoration-none">
                                <i class="bi bi-collection me-1"></i>Question Sets
                            </a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('question.sets.questions', $questionSetId) }}" class="text-decoration-none">
                                <i class="bi bi-list me-1"></i>Questions
                            </a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">
                            <i class="bi bi-plus-circle me-1"></i>Add Question
                        </li>
                    </ol>
                </nav>
            </div>
        </div>

        <!-- Page Header -->
        <div class="row">
            <div class="col-12">
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="h3 mb-0 text-gray-800">
                                <i class="bi bi-plus-circle me-2"></i>Add Question
                            </h1>
                            <p class="text-muted mb-0">Create a new question for the question set</p>
                        </div>
                        <div>
                            <a href="{{ route('question.sets.questions', $questionSetId) }}" class="btn btn-outline-secondary me-2">
                                <i class="bi bi-arrow-left me-1"></i>Back to Questions
                            </a>
                            <a href="{{ route('question.sets.import', $questionSetId) }}" class="btn btn-primary">
                                <i class="bi bi-upload me-1"></i>Bulk Import
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Question Form -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-plus-circle me-2"></i>New Question
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="questionForm">
                            @csrf
                            
                            <!-- Question Text -->
                            <div class="mb-3">
                                <label for="question" class="form-label">Question Text *</label>
                                <textarea class="form-control" id="question" name="question" rows="4" required 
                                          placeholder="Enter your question here..."></textarea>
                            </div>
                            
                            <!-- Question Type -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="question_type" class="form-label">Question Type</label>
                                        <select class="form-control" id="question_type" name="question_type">
                                            <option value="multiple_choice">Multiple Choice</option>
                                            <option value="true_false">True/False</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="marks" class="form-label">Marks</label>
                                        <input type="number" class="form-control" id="marks" name="marks" 
                                               value="1" min="1" max="100">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Options Container -->
                            <div id="optionsContainer">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="mb-0">Answer Options</h6>
                                    <button type="button" id="addOptionBtn" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-plus"></i> Add Option
                                    </button>
                                </div>
                                
                                <!-- Default Options -->
                                <div class="option-group mb-3" data-option="1">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <input type="text" class="form-control" name="options[1][text]" 
                                                   placeholder="Option A" required>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="correct_option" 
                                                       value="1" id="correct_1">
                                                <label class="form-check-label" for="correct_1">Correct</label>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-option" 
                                                    style="display: none;">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="option-group mb-3" data-option="2">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <input type="text" class="form-control" name="options[2][text]" 
                                                   placeholder="Option B" required>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="correct_option" 
                                                       value="2" id="correct_2">
                                                <label class="form-check-label" for="correct_2">Correct</label>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-option" 
                                                    style="display: none;">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Additional Fields -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="explanation" class="form-label">Explanation (Optional)</label>
                                        <textarea class="form-control" id="explanation" name="explanation" rows="3" 
                                                  placeholder="Explain why this is the correct answer..."></textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="exam_section" class="form-label">Section (Optional)</label>
                                        <input type="text" class="form-control" id="exam_section" name="exam_section" 
                                               placeholder="e.g., Mathematics, Science, etc.">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class="d-flex justify-content-end">
                                <button type="button" class="btn btn-secondary me-2" onclick="window.history.back()">
                                    <i class="bi bi-x-circle me-1"></i>Cancel
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle me-1"></i>Create Question
                                </button>
                            </div>
                        </form>
                        
                        <!-- Loading Indicator -->
                        <div id="loadingIndicator" class="text-center mt-4" style="display: none;">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Saving question...</p>
                        </div>
                        
                        <!-- Error Messages -->
                        <div id="errorContainer" class="alert alert-danger mt-4" style="display: none;">
                            <ul id="errorList" class="mb-0"></ul>
                        </div>
                        
                        <!-- Success Messages -->
                        <div id="successContainer" class="alert alert-success mt-4" style="display: none;">
                            <p id="successMessage" class="mb-0"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    $(document).ready(function() {
        let optionCount = 2;
        const maxOptions = 6;
        
        // Handle question type change
        $('#question_type').change(function() {
            const type = $(this).val();
            if (type === 'true_false') {
                setupTrueFalse();
            } else {
                setupMultipleChoice();
            }
        });
        
        // Add option button
        $('#addOptionBtn').click(function() {
            if ($('.option-group').length < maxOptions) {
                addOption();
            }
            updateRemoveButtons();
        });
        
        // Remove option
        $(document).on('click', '.remove-option', function() {
            $(this).closest('.option-group').remove();
            updateRemoveButtons();
        });
        
        // Form submission
        $('#questionForm').submit(function(e) {
            e.preventDefault();
            
            if (!validateForm()) {
                return;
            }
            
            const formData = $(this).serialize();
            
            showLoading();
            hideMessages();
            
            $.ajax({
                url: '{{ route('question.sets.questions.store', $questionSetId) }}',
                method: 'POST',
                data: formData,
                success: function(response) {
                    hideLoading();
                    
                    if (response.success) {
                        showSuccess(response.message);
                        
                        // Reset form
                        $('#questionForm')[0].reset();
                        
                        // Optional: Redirect after delay
                        setTimeout(function() {
                            window.location.href = '{{ route('question.sets.questions', $questionSetId) }}';
                        }, 1500);
                    } else {
                        showError([response.message || 'Failed to create question']);
                    }
                },
                error: function(xhr) {
                    hideLoading();
                    
                    if (xhr.status === 422) {
                        // Validation errors
                        const errors = xhr.responseJSON.errors;
                        const errorMessages = [];
                        
                        Object.keys(errors).forEach(function(key) {
                            errors[key].forEach(function(message) {
                                errorMessages.push(message);
                            });
                        });
                        
                        showError(errorMessages);
                    } else {
                        showError(['An error occurred while creating the question']);
                    }
                }
            });
        });
        
        function setupTrueFalse() {
            // Clear existing options
            $('.option-group').remove();
            
            // Add True/False options
            const trueOption = createOptionHtml(1, 'True');
            const falseOption = createOptionHtml(2, 'False');
            
            $('#optionsContainer').append(trueOption + falseOption);
            
            // Hide add option button for true/false
            $('#addOptionBtn').hide();
            
            optionCount = 2;
            updateRemoveButtons();
        }
        
        function setupMultipleChoice() {
            // Show add option button
            $('#addOptionBtn').show();
            
            // If we don't have enough options, add defaults
            if ($('.option-group').length < 2) {
                $('.option-group').remove();
                
                const optionA = createOptionHtml(1, 'Option A');
                const optionB = createOptionHtml(2, 'Option B');
                
                $('#optionsContainer').append(optionA + optionB);
                optionCount = 2;
            }
            
            updateRemoveButtons();
        }
        
        function addOption() {
            optionCount++;
            const optionLabels = ['A', 'B', 'C', 'D', 'E', 'F'];
            const label = optionLabels[optionCount - 1] || String.fromCharCode(64 + optionCount);
            
            const optionHtml = createOptionHtml(optionCount, `Option ${label}`);
            $('#optionsContainer').append(optionHtml);
        }
        
        function createOptionHtml(number, placeholder) {
            return `
                <div class="option-group mb-3" data-option="${number}">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="options[${number}][text]" 
                                   placeholder="${placeholder}" required>
                        </div>
                        <div class="col-md-2">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="correct_option" 
                                       value="${number}" id="correct_${number}">
                                <label class="form-check-label" for="correct_${number}">Correct</label>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-sm btn-outline-danger remove-option">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }
        
        function updateRemoveButtons() {
            const options = $('.option-group');
            const questionType = $('#question_type').val();
            
            if (questionType === 'true_false' || options.length <= 2) {
                $('.remove-option').hide();
            } else {
                $('.remove-option').show();
            }
        }
        
        function validateForm() {
            const question = $('#question').val().trim();
            const correctOption = $('input[name="correct_option"]:checked').val();
            const options = $('input[name^="options"]');
            
            if (!question) {
                showError(['Please enter a question']);
                return false;
            }
            
            if (!correctOption) {
                showError(['Please select the correct answer']);
                return false;
            }
            
            // Check if at least 2 options are filled
            let filledOptions = 0;
            options.each(function() {
                if ($(this).val().trim()) {
                    filledOptions++;
                }
            });
            
            if (filledOptions < 2) {
                showError(['Please provide at least 2 answer options']);
                return false;
            }
            
            return true;
        }
        
        function showLoading() {
            $('#loadingIndicator').show();
            $('#questionForm button[type="submit"]').prop('disabled', true);
        }
        
        function hideLoading() {
            $('#loadingIndicator').hide();
            $('#questionForm button[type="submit"]').prop('disabled', false);
        }
        
        function showError(messages) {
            const errorList = $('#errorList');
            errorList.empty();
            
            messages.forEach(function(message) {
                errorList.append('<li>' + message + '</li>');
            });
            
            $('#errorContainer').show();
            $('#successContainer').hide();
            
            // Scroll to error
            $('html, body').animate({
                scrollTop: $('#errorContainer').offset().top - 100
            }, 500);
        }
        
        function showSuccess(message) {
            $('#successMessage').text(message);
            $('#successContainer').show();
            $('#errorContainer').hide();
            
            // Scroll to success
            $('html, body').animate({
                scrollTop: $('#successContainer').offset().top - 100
            }, 500);
        }
        
        function hideMessages() {
            $('#errorContainer').hide();
            $('#successContainer').hide();
        }
        
        // Initialize
        updateRemoveButtons();
    });
    </script>
    @endpush
</x-dashboard.default>