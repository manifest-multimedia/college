<div class="container my-5 {{ $examExpired && !$canStillSubmit ? 'exam-expired' : '' }}">
    {{-- Preview Mode Banner (Staff Only) --}}
    @if(isset($isPreview) && $isPreview)
        <div class="alert alert-info mb-4 d-flex justify-content-between align-items-center">
            <div>
                <i class="bi bi-eye me-2"></i> <strong>Preview Mode</strong> — Viewing as: {{ $student_name }} | No data will be saved
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-secondary">Theme: {{ ucfirst($theme) }}</span>
                <button wire:click="switchTheme('default')" class="btn btn-sm btn-light {{ $theme === 'default' ? 'active' : '' }}" type="button">Default</button>
                <button wire:click="switchTheme('one-by-one')" class="btn btn-sm btn-light {{ $theme === 'one-by-one' ? 'active' : '' }}" type="button">One-by-One</button>
            </div>
        </div>
    @endif

    <div class="row">
        <!-- Main Exam Content -->
        <div class="mb-4 text-center">
            <h2>Course Title: {{ $exam->course->name }}</h2>
            Date of Exam: {{ $examSession->started_at }}
            <p>Student Name: {{ $student_name }} | Student ID : {{ $student_index }} </p>
            <p>Proctor: AI Sensei </p>

            <div class="p-3 rounded border shadow-lg row bg-light">
                <div class="col-md-12">
                    <div class="card-body d-flex flex-column align-items-center justify-content-center">

                        <div class="p-3 pt-4 card-text w-100" style="font-size:18px;font-weight:600">
                            <div class="d-flex justify-content-center">
                                <h4 class="text-center text-danger"><strong> Instructions</strong></h4>
                            </div>
                            <p>
                                You're being proctored by AI Sensei. <br />Any suspecious activity will result in immediate
                                disqualification. You're required to answer {{ count($questions) }} questions in total.
                                <br>
                                @php
                                    $baseDuration = $examSession->exam->duration ?? 0;
                                    $extraTime = $examSession->extra_time_minutes ?? 0;
                                    $totalAllocated = $baseDuration + $extraTime;
                                    
                                // For restored sessions, calculate actual restoration time (excluding catch-up time)
                                $actualRestorationTime = $extraTime;
                                if (($examSession->is_restored ?? false) && $examSession->restored_at && $examSession->started_at) {
                                    $minutesFromStartToRestore = $examSession->started_at->diffInMinutes($examSession->restored_at);
                                    $catchUpTime = max(0, $minutesFromStartToRestore - $baseDuration);
                                    $actualRestorationTime = ceil(max(0, $extraTime - $catchUpTime));
                                }
                                @endphp
                                @if($examSession->is_restored ?? false)
                                    <span class="badge bg-warning text-dark"><i class="fas fa-rotate-right"></i> Restored Session</span>
                                    This session was restored with <span class="text-danger">{{ $actualRestorationTime }} minutes</span> allocated.@if($actualRestorationTime > 0) <span class="badge bg-info text-white ms-1"><i class="fas fa-clock-rotate-left"></i> {{ $actualRestorationTime }} min restoration time</span>@endif
                                @else
                                    This exam is <span class="text-danger">{{ $totalAllocated }} minutes</span> long.@if($extraTime > 0) <span class="badge bg-warning text-dark ms-1"><i class="fas fa-plus"></i> {{ $extraTime }} min extra time</span>@endif
                                @endif
                            </p>

                            <div class="d-flex justify-content-center w-100">
                                <!-- Timer Component (now enabled in preview mode for testing) -->
                                <x-exam.timer :examSessionId="$examSession->id" 
                                    :startedAt="$examSession->started_at->toIso8601String()" 
                                    :completedAt="$examSession->adjustedCompletionTime->toIso8601String()" 
                                    :hasExtraTime="$hasExtraTime"
                                    :extraTimeMinutes="$extraTimeMinutes" 
                                    :isRestored="$examSession->is_restored ?? false"
                                    :debug="false" 
                                    class="mt-3" />
                            </div>
                        </div>
                    </div>
                </div>
            </div> <!-- row -->
        </div>

        <div class="row h-100">
            <!-- Main Exam Content -->
            <div class="col-md-9 d-flex flex-column">
                <div class="p-4 shadow-lg card question-card position-relative exam-protected">
                    <!-- Watermark -->
                    <div class="watermark">
                        {{ $student_name }}
                    </div>

                    @if ($examExpired && !$canStillSubmit)
                        <div class="alert alert-warning mb-4">
                            <h4 class="alert-heading"><i class="bi bi-exclamation-triangle me-2"></i> Exam Completed
                            </h4>
                            <p>Your exam time has expired, and your answers have been submitted automatically. You can
                                no longer modify your responses.</p>
                        </div>
                    @elseif ($examExpired && $canStillSubmit)
                        <div class="alert alert-info mb-4">
                            <h4 class="alert-heading"><i class="bi bi-clock-history me-2"></i> Extra Time Active</h4>
                            <p>Regular exam time has expired, but you are allowed to continue answering questions using
                                your extra time allocation.</p>
                        </div>
                    @endif

                    <div class="scrollable-questions flex-grow-1 scrollbar-container" id="questionsContainer">
                        <form wire:submit.prevent="{{ $examExpired && !$canStillSubmit ? 'logout' : 'submitExam' }}">
                            <div class="questions-container">
                                @foreach ($questions as $index => $question)
                                    <div class="p-3 mb-4 question rounded-border" id="question-{{ $index + 1 }}">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div style="font-size: 18px; font-weight: 600;">
                                                <strong>Q{{ $index + 1 }}:</strong> {!! $question['question'] !!}
                                            </div>
                                            @if (!($examExpired && !$canStillSubmit))
                                                <div class="d-flex gap-2 ms-3 flex-shrink-0">
                                                    <button type="button" 
                                                        class="btn btn-sm btn-outline-warning flag-button {{ in_array($question['id'], $flaggedQuestions) ? 'active' : '' }}"
                                                        onclick="toggleFlagV1({{ $question['id'] }})"
                                                        data-question-id="{{ $question['id'] }}"
                                                        title="Flag for review">
                                                        <i class="bi {{ in_array($question['id'], $flaggedQuestions) ? 'bi-flag-fill' : 'bi-flag' }}"></i>
                                                    </button>
                                                    <button type="button" 
                                                        class="btn btn-sm btn-outline-danger clear-button"
                                                        onclick="confirmClearResponseV1({{ $question['id'] }})"
                                                        title="Clear response"
                                                        {{ !isset($responses[$question['id']]) ? 'disabled' : '' }}>
                                                        <i class="bi bi-x-circle"></i>
                                                    </button>
                                                </div>
                                            @endif
                                        </div>
                                        <ul class="list-unstyled">
                                            @foreach ($question['options'] as $option)
                                                <li class="mb-3">
                                                    <label class="form-check-label d-flex align-items-center">
                                                        <input type="radio" class="mx-2 form-check-input"
                                                            name="responses[{{ $question['id'] }}]"
                                                            value="{{ $option['id'] }}"
                                                            wire:click="storeResponse({{ $question['id'] }}, {{ $option['id'] }})"
                                                            @if (isset($responses[$question['id']]) && $responses[$question['id']] == $option['id']) checked @endif>
                                                        <span style="font-size: 16px;">{{ $option['option_text'] }}</span>
                                                    </label>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endforeach
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="shadow-lg col-md-3 sidebar d-flex flex-column card question-card" style="height:550px">
                <div class="p-4 text-center">
                    <h5>Questions Overview</h5>
                    <p class="mb-0">
                        Questions Answered: <strong id="answeredCount">{{ count(array_filter($responses)) }}</strong> /
                        {{ count($questions) }}
                    </p>

                    @if ($examExpired && !$canStillSubmit)
                        <div class="alert alert-info mt-3 mb-0 py-2 small">
                            <i class="bi bi-info-circle me-1"></i> Exam has been submitted
                        </div>
                    @elseif ($examExpired && $canStillSubmit)
                        <div class="alert alert-warning mt-3 mb-0 py-2 small">
                            <i class="bi bi-clock-history me-1"></i> Using extra time
                        </div>
                    @endif
                </div>

                <div id="questionsOverview" class="overflow-y-auto p-3 mb-2 flex-grow-1">
                    <div class="flex-wrap gap-3 tracker-container d-flex justify-content-center">
                        @foreach ($questions as $index => $question)
                            @php
                                $isFlagged = in_array($question['id'], $flaggedQuestions);
                                $isAnswered = isset($responses[$question['id']]);
                                $trackerClass = '';
                                if ($isFlagged && $isAnswered) {
                                    $trackerClass = 'flagged-answered';
                                } elseif ($isFlagged) {
                                    $trackerClass = 'flagged-unanswered';
                                } elseif ($isAnswered) {
                                    $trackerClass = 'answered';
                                } else {
                                    $trackerClass = 'unanswered';
                                }
                            @endphp
                            <div class="tracker-item rounded-circle text-center {{ $trackerClass }}"
                                style="width: 50px; height: 50px; line-height: 50px; cursor: pointer;"
                                data-question-id="{{ $index + 1 }}"
                                data-actual-question-id="{{ $question['id'] }}"
                                data-is-flagged="{{ $isFlagged ? 'true' : 'false' }}"
                                onclick="scrollToQuestion({{ $index + 1 }})">
                                {{ $index + 1 }}
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="bg-white card-footer d-flex justify-content-center align-items-center">
                    @if ($examExpired && !$canStillSubmit)
                        <a href="{{ route('take-exam') }}" class="btn btn-secondary w-100">
                            <i class="bi bi-box-arrow-left me-2"></i> Return to Exam Login
                        </a>
                    @else
                        <button class="btn btn-primary w-100" onclick="showV1SubmitConfirmation()" id="submitBtn">Submit Exam</button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Clear Response Confirmation Modal -->
    <div class="modal fade" id="clearResponseModalV1" tabindex="-1" aria-labelledby="clearResponseLabelV1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header bg-warning bg-opacity-10">
                    <h5 class="modal-title" id="clearResponseLabelV1">
                        <i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>
                        Clear Response
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Are you sure you want to clear your response for this question?</p>
                    <p class="text-muted small mt-2 mb-0">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger btn-sm" id="confirmClearBtnV1">
                        <i class="bi bi-trash me-1"></i> Clear Response
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Exam Submission Confirmation Modal (Improved Design) -->
    <div class="modal fade" id="submitConfirmModalV1" tabindex="-1" aria-labelledby="submitConfirmLabelV1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header bg-gradient-to-r from-blue-50 to-indigo-50 border-0">
                    <h5 class="modal-title fw-bold" id="submitConfirmLabelV1">
                        <svg class="me-2" style="width: 24px; height: 24px; display: inline-block; vertical-align: middle;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10" stroke="#f59e0b" fill="#fef3c7"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01" stroke="#f59e0b"/>
                        </svg>
                        Confirm Exam Submission
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <div class="card text-center border-0 bg-light">
                                <div class="card-body py-3">
                                    <div class="text-muted small">Total Questions</div>
                                    <div class="fs-4 fw-bold" id="modalTotalQuestions">{{ count($questions) }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card text-center border-0 bg-success bg-opacity-10">
                                <div class="card-body py-3">
                                    <div class="text-success small">Answered</div>
                                    <div class="fs-4 fw-bold text-success" id="modalAnsweredCount"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card text-center border-0 bg-danger bg-opacity-10">
                                <div class="card-body py-3">
                                    <div class="text-danger small">Unanswered</div>
                                    <div class="fs-4 fw-bold text-danger" id="modalUnansweredCount"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-warning d-flex align-items-center mb-0">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <span><strong>Warning:</strong> This action cannot be undone. Once submitted, you cannot modify your answers.</span>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i>Cancel
                    </button>
                    <button type="button" class="btn btn-success" id="confirmSubmitBtnV1">
                        <i class="bi bi-check2-circle me-2"></i>Submit Exam
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- New timer scripts and styles -->
    <link href="{{ asset('css/exam-timer.css') }}" rel="stylesheet">
    <script src="{{ asset('js/services/ExamTimerService.js') }}"></script>

    @include('components.partials.styles.exam-styles')
    @include('components.partials.styles.scrollbar-styles')
    @include('components.partials.exam-security')

    <style>
        /* Tracker States */
        .tracker-item {
            transition: all 0.3s ease;
            font-weight: 600;
        }

        .tracker-item:hover {
            transform: scale(1.1);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }

        /* Answered - Green */
        .tracker-item.answered {
            background-color: #28a745;
            color: white;
            border: 2px solid #1e7e34;
        }

        /* Unanswered - Light Gray */
        .tracker-item.unanswered {
            background-color: #f8f9fa;
            color: #6c757d;
            border: 2px solid #dee2e6;
        }

        /* Flagged + Answered - Yellow/Gold Gradient */
        .tracker-item.flagged-answered {
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            color: #78350f;
            border: 2px solid #d97706;
            font-weight: 700;
        }

        /* Flagged + Unanswered - Light Yellow */
        .tracker-item.flagged-unanswered {
            background-color: #fef3c7;
            color: #92400e;
            border: 2px solid #fbbf24;
            font-weight: 700;
        }

        /* Flag Button Styles */
        .flag-button {
            transition: all 0.2s ease;
        }

        .flag-button.active {
            background-color: #fbbf24;
            border-color: #f59e0b;
            color: #78350f;
        }

        .flag-button:hover:not(.active) {
            background-color: #fef3c7;
            border-color: #fbbf24;
        }

        .flag-button.active:hover {
            background-color: #f59e0b;
            border-color: #d97706;
        }

        /* Clear Button Styles */
        .clear-button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .clear-button:not(:disabled):hover {
            background-color: #dc3545;
            border-color: #dc3545;
            color: white;
        }

        /* Modal Styles */
        .bg-gradient-to-r {
            background: linear-gradient(to right, #eff6ff, #eef2ff);
        }
    </style>

    @if ($examExpired && !$canStillSubmit)
        <style>
            .form-check-label.disabled {
                color: #6c757d;
                cursor: not-allowed;
            }

            .question {
                opacity: 0.9;
            }

            .tracker-item.answered {
                opacity: 0.8;
            }
        </style>
    @elseif ($examExpired && $canStillSubmit)
        <style>
            .question {
                border-left: 3px solid #ffc107;
            }
        </style>
    @endif

    <script>
        let questionToClearV1 = null;
        
        // Show modern submission confirmation modal
        function showV1SubmitConfirmation() {
            const answeredCount = document.querySelectorAll('input[type="radio"]:checked').length;
            const totalQuestions = {{ count($questions) }};
            const unanswered = totalQuestions - answeredCount;
            
            document.getElementById('modalAnsweredCount').textContent = answeredCount;
            document.getElementById('modalUnansweredCount').textContent = unanswered;
            
            const modal = new bootstrap.Modal(document.getElementById('submitConfirmModalV1'));
            modal.show();
        }
        
        // Toggle flag for a question
        async function toggleFlagV1(questionId) {
            const button = document.querySelector(`.flag-button[data-question-id="${questionId}"]`);
            const icon = button.querySelector('i');
            const isFlagged = button.classList.contains('active');
            
            // Optimistic UI update
            if (isFlagged) {
                button.classList.remove('active');
                icon.classList.remove('bi-flag-fill');
                icon.classList.add('bi-flag');
            } else {
                button.classList.add('active');
                icon.classList.remove('bi-flag');
                icon.classList.add('bi-flag-fill');
            }
            
            // Update tracker
            updateTrackerFlagStateV1(questionId, !isFlagged);
            
            try {
                // Call Livewire method
                await @this.toggleFlag(questionId);
                console.log('Flag toggled successfully for question:', questionId);
            } catch (error) {
                console.error('Error toggling flag:', error);
                // Rollback UI on error
                if (isFlagged) {
                    button.classList.add('active');
                    icon.classList.add('bi-flag-fill');
                    icon.classList.remove('bi-flag');
                } else {
                    button.classList.remove('active');
                    icon.classList.add('bi-flag');
                    icon.classList.remove('bi-flag-fill');
                }
                updateTrackerFlagStateV1(questionId, isFlagged);
            }
        }
        
        // Update tracker item flag state
        function updateTrackerFlagStateV1(questionId, isFlagged) {
            const trackerItem = document.querySelector(`.tracker-item[data-actual-question-id="${questionId}"]`);
            if (!trackerItem) return;
            
            const isAnswered = trackerItem.classList.contains('answered') || 
                             trackerItem.classList.contains('flagged-answered');
            
            // Remove all state classes
            trackerItem.classList.remove('answered', 'unanswered', 'flagged-answered', 'flagged-unanswered');
            
            // Apply new class based on state
            if (isFlagged && isAnswered) {
                trackerItem.classList.add('flagged-answered');
            } else if (isFlagged) {
                trackerItem.classList.add('flagged-unanswered');
            } else if (isAnswered) {
                trackerItem.classList.add('answered');
            } else {
                trackerItem.classList.add('unanswered');
            }
            
            trackerItem.setAttribute('data-is-flagged', isFlagged ? 'true' : 'false');
        }
        
        // Show clear response confirmation modal
        function confirmClearResponseV1(questionId) {
            questionToClearV1 = questionId;
            const modal = new bootstrap.Modal(document.getElementById('clearResponseModalV1'));
            modal.show();
            
            // Setup confirm button click handler
            document.getElementById('confirmClearBtnV1').onclick = () => {
                clearResponseV1(questionId);
                modal.hide();
            };
        }
        
        // Clear response for a question
        async function clearResponseV1(questionId) {
            try {
                const result = await @this.clearResponse(questionId);
                
                if (result.success) {
                    // Uncheck radio button
                    const radioInput = document.querySelector(`input[name="responses[${questionId}]"]:checked`);
                    if (radioInput) {
                        radioInput.checked = false;
                    }
                    
                    // Disable clear button
                    const clearButton = document.querySelector(`.clear-button[onclick*="${questionId}"]`);
                    if (clearButton) {
                        clearButton.disabled = true;
                    }
                    
                    // Update tracker
                    const trackerItem = document.querySelector(`.tracker-item[data-actual-question-id="${questionId}"]`);
                    if (trackerItem) {
                        const isFlagged = trackerItem.getAttribute('data-is-flagged') === 'true';
                        trackerItem.classList.remove('answered', 'flagged-answered');
                        trackerItem.classList.add(isFlagged ? 'flagged-unanswered' : 'unanswered');
                    }
                    
                    // Update answer count
                    updateQuestionOverview();
                    
                    console.log('Response cleared successfully for question:', questionId);
                } else {
                    alert('Failed to clear response: ' + (result.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error clearing response:', error);
                alert('An error occurred while clearing the response');
            }
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize question navigation
            initializeQuestionNavigation();

            // Set up Livewire event listeners for Laravel 12
            Livewire.on('responseUpdated', () => {
                console.log('Response updated event received');
                updateQuestionOverview();
            });
            
            Livewire.on('responseCleared', (data) => {
                console.log('Response cleared event received', data);
                updateQuestionOverview();
            });
            
            // Setup submission modal confirm button
            document.getElementById('confirmSubmitBtnV1').addEventListener('click', function() {
                // Hide modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('submitConfirmModalV1'));
                if (modal) {
                    modal.hide();
                }
                
                // Disable button to prevent double submission
                this.disabled = true;
                this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Submitting...';
                
                // Call Livewire submit method
                @this.submitExam();
            });
        });

        function scrollToQuestion(questionNumber) {
            const questionElement = document.getElementById('question-' + questionNumber);
            if (questionElement) {
                // Scroll to the question with smooth behavior
                document.getElementById('questionsContainer').scrollTo({
                    top: questionElement.offsetTop - 20,
                    behavior: 'smooth'
                });

                // Briefly highlight the question
                questionElement.classList.add('highlight-question');
                setTimeout(() => {
                    questionElement.classList.remove('highlight-question');
                }, 2000);
            }
        }

        function initializeQuestionNavigation() {
            // Add click handlers for question overview items
            document.querySelectorAll('.tracker-item').forEach(item => {
                item.addEventListener('click', function() {
                    const questionId = this.getAttribute('data-question-id');
                    scrollToQuestion(questionId);
                });
            });
        }

        function updateQuestionOverview() {
            // Force re-calculation of answered questions
            const trackerItems = document.querySelectorAll('.tracker-item');
            const answeredCount = document.querySelectorAll('input[type="radio"]:checked').length;
            const totalQuestions = trackerItems.length;

            // Update the counter display
            document.getElementById('answeredCount').textContent = answeredCount;

            // Update each tracker item and clear button based on whether its question has an answer
            document.querySelectorAll('.tracker-item').forEach(item => {
                const actualQuestionId = item.getAttribute('data-actual-question-id');
                const isFlagged = item.getAttribute('data-is-flagged') === 'true';
                const radioInput = document.querySelector(`input[name="responses[${actualQuestionId}]"]:checked`);
                const isAnswered = !!radioInput;
                
                // Update tracker classes
                item.classList.remove('answered', 'unanswered', 'flagged-answered', 'flagged-unanswered');
                if (isFlagged && isAnswered) {
                    item.classList.add('flagged-answered');
                } else if (isFlagged) {
                    item.classList.add('flagged-unanswered');
                } else if (isAnswered) {
                    item.classList.add('answered');
                } else {
                    item.classList.add('unanswered');
                }
                
                // Enable/disable clear button based on answered state
                const clearButton = document.querySelector(`.clear-button[onclick*="${actualQuestionId}"]`);
                if (clearButton) {
                    clearButton.disabled = !isAnswered;
                }
            });

            console.log('Question overview updated. Answered:', answeredCount);
        }

        // Initialize device heartbeat system (disabled in preview mode)
        @if(!isset($isPreview) || !$isPreview)
        document.addEventListener('livewire:initialized', function() {
            // Send heartbeats to keep the device session active
            const heartbeatInterval = setInterval(function() {
                // Only send heartbeat if the page is visible
                if (document.visibilityState === 'visible') {
                    @this.heartbeat();
                }
            }, 30000); // Send heartbeat every 30 seconds

            // Handle page visibility changes
            document.addEventListener('visibilitychange', function() {
                if (document.visibilityState === 'visible') {
                    // Immediately send a heartbeat when page becomes visible
                    @this.heartbeat();
                }
            });

            // Clear interval on page unload
            window.addEventListener('beforeunload', function() {
                clearInterval(heartbeatInterval);
            });

            // Track device switching attempts
            function checkDeviceConsistency() {
                @this.validateDeviceAccess().then(function(result) {
                    if (result.deviceConflict) {
                        // Redirect to conflict page or reload to show the conflict view
                        window.location.reload();
                    }
                });
            }

            // Check device consistency every 60 seconds
            setInterval(checkDeviceConsistency, 60000);
        });
        @endif
    </script>

    <style>
        .question{
            min-width:720px !important;
            width:100% !important;
        }
        .highlight-question {
            box-shadow: 0 0 10px rgba(0, 123, 255, 0.5);
            border: 2px solid #007bff;
            animation: pulse 1.5s;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.02);
            }

            100% {
                transform: scale(1);
            }
        }

        .tracker-item {
            transition: all 0.3s ease;
        }

        .tracker-item:hover {
            transform: scale(1.1);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .tracker-item.answered {
            background-color: #28a745;
            color: white;
        }

        .tracker-item.unanswered {
            background-color: #f8f9fa;
        }
    </style>
</div> <!-- Root Container -->
