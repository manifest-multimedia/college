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
                                You have <span class="text-danger">{{ $exam->duration }} minutes</span> to complete this exam.
                            </p>

                            <div class="d-flex justify-content-center w-100">
                                @if(isset($isPreview) && $isPreview)
                                    {{-- Timer disabled in preview mode --}}
                                    <div class="alert alert-info mt-3 text-dark">
                                        <i class="bi bi-clock me-2"></i> <strong>Timer disabled in preview mode</strong>
                                    </div>
                                @else
                                    <!-- New Timer Component -->
                                    <x-exam.timer :examSessionId="$examSession->id" 
                                        :startedAt="$examSession->started_at->toIso8601String()" 
                                        :completedAt="$examSession->adjustedCompletionTime->toIso8601String()" 
                                        :hasExtraTime="$hasExtraTime"
                                        :extraTimeMinutes="$extraTimeMinutes" 
                                        :debug="false" 
                                        class="mt-3" />
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div> <!-- row -->
        </div>

        <div class="row h-100">
            <!-- Main Exam Content -->
            <div class="col-md-9 d-flex flex-column">
                <div class="p-4 shadow-lg card question-card position-relative">
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
                                        <div class="mb-3" style="font-size: 18px; font-weight: 600;">
                                            <strong>Q{{ $index + 1 }}:</strong> {!! $question['question'] !!}
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
                            <div class="tracker-item rounded-circle text-center 
                                   @if (isset($responses[$question['id']])) answered @else unanswered @endif"
                                style="width: 50px; height: 50px; line-height: 50px; cursor: pointer;"
                                data-question-id="{{ $index + 1 }}"
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
                        <button class="btn btn-primary w-100" wire:click="submitExam" id="submitBtn">Submit
                            Exam</button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- New timer scripts and styles -->
    <link href="{{ asset('css/exam-timer.css') }}" rel="stylesheet">
    <script src="{{ asset('js/services/ExamTimerService.js') }}"></script>

    @include('components.partials.styles.exam-styles')
    @include('components.partials.styles.scrollbar-styles')

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
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize question navigation
            initializeQuestionNavigation();

            // Set up Livewire event listeners for Laravel 12
            Livewire.on('responseUpdated', () => {
                console.log('Response updated event received');
                updateQuestionOverview();
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
            document.getElementById('answeredCount').textContent = answeredCount + ' / ' + totalQuestions;

            // Update each tracker item based on whether its question has an answer
            document.querySelectorAll('input[type="radio"]:checked').forEach(radio => {
                const questionId = radio.name.match(/\[(\d+)\]/)[1];
                document.querySelectorAll('.tracker-item').forEach(item => {
                    const index = parseInt(item.textContent.trim()) - 1;
                    if (questionId === document.querySelectorAll('.question')[index].id.replace('question-',
                            '')) {
                        item.classList.add('answered');
                        item.classList.remove('unanswered');
                    }
                });
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
