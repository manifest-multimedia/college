<div class="container my-5" x-data="examV2Manager()" x-init="initExam()">
    <div class="row">
        <!-- Main Exam Content -->
        <div class="mb-4 text-center">
            <h2>Course Title: {{ $exam->course->name }}</h2>
            Date of Exam: {{ $examSession->started_at }}
            <p>Student Name: {{ $student->full_name ?? $user->name }} | Student ID : {{ $student->student_id }} </p>
            <p>Proctor: AI Sensei </p>

            <div class="p-3 rounded border shadow-lg row bg-light">
                <div class="col-md-12">
                    <div class="card-body d-flex flex-column align-items-center justify-content-center">
                        <div class="p-3 pt-4 card-text w-100" style="font-size:18px;font-weight:600">
                            <div class="d-flex justify-content-center">
                                <h4 class="text-center text-danger"><strong> Instructions</strong></h4>
                            </div>
                            <p>
                                You're being proctored by AI Sensei. <br />Any suspicious activity will result in immediate
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
                                @if (!$readOnlyMode)
                                    <!-- Timer Component -->
                                    <x-exam.timer :examSessionId="$examSession->id" 
                                        :startedAt="$examSession->started_at->toIso8601String()" 
                                        :completedAt="$adjustedCompletionTime->toIso8601String()" 
                                        :hasExtraTime="$examSession->extra_time_minutes > 0"
                                        :extraTimeMinutes="$examSession->extra_time_minutes ?? 0" 
                                        :isRestored="$examSession->is_restored ?? false"
                                        :debug="false" 
                                        class="mt-3" />
                                @elseif ($readOnlyMode && $readOnlyReason === 'completed')
                                    <div class="alert alert-info mt-3 mb-0">
                                        <i class="bi bi-info-circle me-2"></i>
                                        <strong>Exam Completed</strong>
                                    </div>
                                @elseif ($readOnlyMode && $readOnlyReason === 'device_mismatch' && !$examSession->device_mismatch_bypassed)
                                    <div class="alert alert-danger mt-3 mb-0">
                                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                        <strong>Device Mismatch Detected</strong>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row" style="min-height: calc(100vh - 400px);">
            <!-- Main Exam Content -->
            <div class="col-md-9 d-flex flex-column" style="display: flex; flex-direction: column;">
                <div class="p-4 shadow-lg card question-card position-relative exam-protected d-flex flex-column" style="flex: 1; display: flex; flex-direction: column;">
                    <!-- Watermark -->
                    <div class="watermark">
                        {{ $student->full_name ?? $user->name }}
                    </div>

                    @if ($readOnlyMode)
                        @if ($readOnlyReason === 'device_mismatch' && !$examSession->device_mismatch_bypassed)
                            <div class="alert alert-danger mb-4">
                                <h4 class="alert-heading"><i class="bi bi-exclamation-triangle-fill me-2"></i> Device Mismatch Detected</h4>
                                <p class="mb-0">{{ $validationMessage }}</p>
                            </div>
                        @elseif ($readOnlyReason === 'completed')
                            <div class="alert alert-info mb-4">
                                <h4 class="alert-heading"><i class="bi bi-info-circle me-2"></i> Exam Completed</h4>
                                <p class="mb-0">{{ $validationMessage }}</p>
                            </div>
                        @endif
                    @endif

                    <div class="scrollable-questions flex-grow-1 scrollbar-container" id="questionsContainer" style="flex: 1; display: flex; flex-direction: column; overflow-y: auto;">
                        <div class="questions-container">
                            @foreach ($questions as $index => $question)
                                <div class="p-3 mb-4 question rounded-border" id="question-{{ $index + 1 }}">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div style="font-size: 18px; font-weight: 600;">
                                            <strong>Q{{ $index + 1 }}:</strong> {!! $question['question_text'] !!}
                                        </div>
                                        @if (!$readOnlyMode)
                                            <div class="d-flex gap-2 ms-3 flex-shrink-0">
                                                <button type="button" 
                                                    class="btn btn-outline-warning flag-button"
                                                    style="padding: 0.25rem 0.5rem; font-size: 0.75rem;"
                                                    :class="{ 'active': flaggedQuestions.includes({{ $question['id'] }}) }"
                                                    @click="toggleFlag({{ $question['id'] }})"
                                                    title="Flag for review">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-flag" viewBox="0 0 16 16">
                                                        <path d="M14.778.085A.5.5 0 0 1 15 .5V8a.5.5 0 0 1-.314.464L14.5 8l.186.464-.003.001-.006.003-.023.009a12 12 0 0 1-.397.15c-.264.095-.631.223-1.047.35-.816.252-1.879.523-2.71.523-.847 0-1.548-.28-2.158-.525l-.028-.01C7.68 8.71 7.14 8.5 6.5 8.5c-.7 0-1.638.23-2.437.477A20 20 0 0 0 3 9.342V15.5a.5.5 0 0 1-1 0V.5a.5.5 0 0 1 1 0v.282c.226-.079.496-.17.79-.26C4.606.272 5.67 0 6.5 0c.84 0 1.524.277 2.121.519l.043.018C9.286.788 9.828 1 10.5 1c.7 0 1.638-.23 2.437-.477a20 20 0 0 0 1.349-.476l.019-.007.004-.002h.001M14 1.221c-.22.078-.48.167-.766.255-.81.252-1.872.523-2.734.523-.886 0-1.592-.286-2.203-.534l-.008-.003C7.662 1.21 7.139 1 6.5 1c-.669 0-1.606.229-2.415.478A21 21 0 0 0 3 1.845v6.433c.22-.078.48-.167.766-.255C4.576 7.77 5.638 7.5 6.5 7.5c.847 0 1.548.28 2.158.525l.028.01C9.32 8.29 9.86 8.5 10.5 8.5c.668 0 1.606-.229 2.415-.478A21 21 0 0 0 14 7.655V1.222z"/>
                                                    </svg>
                                                    <span class="ms-1">Flag</span>
                                                </button>
                                                <button type="button" 
                                                    class="btn btn-outline-danger clear-button"
                                                    style="padding: 0.25rem 0.5rem; font-size: 0.75rem;"
                                                    @click="confirmClearResponse({{ $question['id'] }})"
                                                    title="Clear response"
                                                    :disabled="!responses[{{ $question['id'] }}]">
                                                    <i class="bi bi-x-circle" style="font-size: 14px;"></i>
                                                    <span class="ms-1">Clear</span>
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                    <ul class="list-unstyled">
                                        @foreach ($question['options'] as $option)
                                            <li class="mb-3">
                                                <label class="form-check-label d-flex align-items-center">
                                                    <input type="radio" 
                                                        class="mx-2 form-check-input"
                                                        name="responses[{{ $question['id'] }}]"
                                                        value="{{ $option['id'] }}"
                                                        @change="handleAnswerChange({{ $question['id'] }}, {{ $option['id'] }})"
                                                        @if ($question['selected_answer'] == $option['id']) checked @endif
                                                        @if ($readOnlyMode) disabled @endif>
                                                    <span style="font-size: 16px;">{{ $option['option_text'] }}</span>
                                                </label>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="mt-4 d-flex justify-content-center">
                    @if ($readOnlyMode)
                        <a href="{{ route('take-exam') }}" class="btn btn-secondary w-100">
                            <i class="bi bi-box-arrow-left me-2"></i> Return to Exam Login
                        </a>
                    @else
                        <button class="btn w-100 text-white fw-bold" 
                                style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); padding: 0.75rem 1.5rem; font-size: 1rem;"
                                onclick="showV2SubmitConfirmation()" 
                                id="submitBtn">
                            <i class="bi bi-check-circle me-2"></i> Submit Exam
                        </button>
                    @endif
                </div>
            </div>

            <!-- Sidebar - Question Navigator (Offline UI Style) -->
            <div class="shadow-lg col-md-3 sidebar d-flex flex-column" style="padding: 0; border-radius: 0.5rem; overflow: hidden; display: flex; flex-direction: column; height: auto;">
                <!-- Header Section -->
                <div class="p-3 text-white" style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);">
                    <h5 class="mb-0 fw-bold">Questions Overview</h5>
                </div>

                <!-- Exam Progress Section -->
                <div class="p-3 border-bottom" style="background-color: #f8f9fa;">
                    <h6 class="mb-2 fw-bold" style="color: #1d4ed8;">Exam Progress</h6>
                    <div class="row g-2 mb-2">
                        <div class="col-4 text-center">
                            <div class="p-2 rounded" style="background-color: #22c55e; color: white;">
                                <div class="fw-bold fs-5" x-text="answeredCount">0</div>
                                <div class="small">Answered</div>
                            </div>
                        </div>
                        <div class="col-4 text-center">
                            <div class="p-2 rounded" style="background-color: #ef4444; color: white;">
                                <div class="fw-bold fs-5" x-text="{{ count($questions) }} - answeredCount">{{ count($questions) }}</div>
                                <div class="small">Left</div>
                            </div>
                        </div>
                        <div class="col-4 text-center">
                            <div class="p-2 rounded" style="background-color: #f59e0b; color: white;">
                                <div class="fw-bold fs-5" x-text="flaggedQuestions.length">0</div>
                                <div class="small">Flagged</div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small text-muted">Progress</span>
                            <span class="small fw-bold" x-text="Math.round((answeredCount / {{ count($questions) }}) * 100) + '%'">0%</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar" 
                                 :style="`width: ${(answeredCount / {{ count($questions) }}) * 100}%`"
                                 style="background: linear-gradient(90deg, #22c55e 0%, #16a34a 100%);">
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="small fw-bold">Total: {{ count($questions) }}</span>
                        <span class="small text-muted">Flagged: <span x-text="flaggedQuestions.length">0</span></span>
                    </div>
                </div>

                <!-- Question Grid with Scrollbar -->
                <div id="questionsOverview" class="p-3 mb-0 flex-grow-1 overflow-y-auto" style="flex: 1; min-height: 200px; overflow-y: auto;">
                    <div class="row g-2">
                        @foreach ($questions as $index => $question)
                            @php
                                $isFlagged = $question['is_flagged'] ?? false;
                                $isAnswered = $question['selected_answer'] !== null;
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
                            <div class="col-2-4">
                                <div class="tracker-item rounded-circle text-center {{ $trackerClass }}"
                                    style="width: 45px; height: 45px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 13px;"
                                    data-question-id="{{ $index + 1 }}"
                                    data-actual-question-id="{{ $question['id'] }}"
                                    data-is-flagged="{{ $isFlagged ? 'true' : 'false' }}"
                                    onclick="scrollToQuestion({{ $index + 1 }})">
                                    {{ $index + 1 }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Legend Section -->
                <div class="p-3 border-top" style="background-color: #ffffff;">
                    <div class="row g-2 small">
                        <div class="col-6 d-flex align-items-center">
                            <div class="me-2 rounded-circle" style="width: 20px; height: 20px; background-color: #22c55e; border: 2px solid #16a34a;"></div>
                            <span>Answered</span>
                        </div>
                        <div class="col-6 d-flex align-items-center">
                            <div class="me-2 rounded-circle" style="width: 20px; height: 20px; background-color: #fbbf24; border: 2px solid #f59e0b;"></div>
                            <span>Flagged</span>
                        </div>
                        <div class="col-6 d-flex align-items-center">
                            <div class="me-2 rounded-circle" style="width: 20px; height: 20px; background-color: #f8f9fa; border: 2px solid #dee2e6;"></div>
                            <span>Not Answered</span>
                        </div>
                        <div class="col-6 d-flex align-items-center">
                            <div class="me-2 rounded-circle" style="width: 20px; height: 20px; background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); border: 2px solid #2563eb;"></div>
                            <span>Current</span>
                        </div>
                    </div>
                </div>


            </div>
        </div>
    </div>

    <!-- Clear Response Confirmation Modal -->
    <div class="modal fade" id="clearResponseModal" tabindex="-1" aria-labelledby="clearResponseLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header bg-warning bg-opacity-10">
                    <h5 class="modal-title" id="clearResponseLabel">
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
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" id="confirmClearBtn">
                        <i class="bi bi-trash me-1"></i> Clear Response
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Exam Submission Confirmation Modal (Offline App Design) -->
    <div class="modal fade" id="v2SubmitConfirmModal" tabindex="-1" aria-labelledby="submitConfirmLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header bg-gradient-to-r from-blue-50 to-indigo-50 border-0">
                    <h5 class="modal-title fw-bold" id="submitConfirmLabel">
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
                                    <div class="fs-4 fw-bold">{{ count($questions) }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card text-center border-0 bg-success bg-opacity-10">
                                <div class="card-body py-3">
                                    <div class="text-success small">Answered</div>
                                    <div class="fs-4 fw-bold text-success" x-text="answeredCount"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card text-center border-0 bg-danger bg-opacity-10">
                                <div class="card-body py-3">
                                    <div class="text-danger small">Unanswered</div>
                                    <div class="fs-4 fw-bold text-danger" x-text="{{ count($questions) }} - answeredCount"></div>
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
                    <button type="button" class="btn btn-success" onclick="confirmV2Submit()" id="confirmSubmitBtn">
                        <i class="bi bi-check2-circle me-2"></i>Submit Exam
                    </button>
                </div>
            </div>
        </div>
    </div>

    @include('components.partials.styles.exam-styles')
    @include('components.partials.styles.scrollbar-styles')
    @include('components.partials.exam-security')

    <link href="{{ asset('css/exam-timer.css') }}" rel="stylesheet">
    <script src="{{ asset('js/services/ExamTimerService.js') }}"></script>

    <style>
        .question {
            min-width: 720px !important;
            width: 100% !important;
        }

        /* 5-column grid for question tracker */
        .col-2-4 {
            flex: 0 0 20%;
            max-width: 20%;
            padding: 0.25rem;
        }

        .tracker-item {
            transition: all 0.3s ease;
            font-weight: 600;
            border: 2px solid;
        }

        .tracker-item:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        /* Answered - Green (Offline style) */
        .tracker-item.answered {
            background-color: #22c55e;
            color: white;
            border-color: #16a34a;
        }

        /* Unanswered - Light Gray (Offline style) */
        .tracker-item.unanswered {
            background-color: #ffffff;
            color: #6b7280;
            border-color: #d1d5db;
        }

        /* Flagged + Answered - Amber/Gold (Offline style) */
        .tracker-item.flagged-answered {
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            color: #78350f;
            border-color: #d97706;
            font-weight: 700;
        }

        /* Flagged + Unanswered - Light Amber (Offline style) */
        .tracker-item.flagged-unanswered {
            background-color: #fef3c7;
            color: #92400e;
            border-color: #fbbf24;
            font-weight: 700;
        }

        /* Current Question Indicator */
        .tracker-item.current {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
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

        /* Modal Animations */
        .modal.fade .modal-dialog {
            transition: transform 0.3s ease-out;
        }

        .modal-content {
            border-radius: 0.5rem;
        }

        /* Background gradient classes */
        .bg-gradient-to-r {
            background: linear-gradient(to right, var(--bs-light), var(--bs-info));
        }

        .from-blue-50 {
            --bs-light: #eff6ff;
        }

        .to-indigo-50 {
            --bs-info: #eef2ff;
        }

        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 4rem;
            color: rgba(0, 0, 0, 0.05);
            pointer-events: none;
            z-index: 10;
            white-space: nowrap;
        }

        /* Progress bar animation */
        .progress-bar {
            transition: width 0.6s ease;
        }
    </style>

    <script>
        function examV2Manager() {
            return {
                // State
                responses: @js(collect($questions)->mapWithKeys(fn($q) => [$q['id'] => $q['selected_answer']])->toArray()),
                pendingSync: {},
                flaggedQuestions: @js($flaggedQuestions ?? []),
                pendingFlags: {}, // {questionId: 'flag' or 'unflag'}
                pendingFlagsCount: 0,
                questionToClear: null, // Stores question ID for clear confirmation
                syncInterval: null,
                syncTimeout: null,
                isOnline: navigator.onLine,
                lastSyncTime: null,
                pendingSyncCount: 0,
                syncStatus: 'synced',
                answeredCount: {{ $answeredCount }},

                // Constants
                SYNC_INTERVAL_MS: 30000, // 30 seconds
                SYNC_THRESHOLD: 5, // Sync after 5 pending answers
                FLAG_SYNC_THRESHOLD: 3, // Sync flags after 3 pending operations
                STORAGE_KEY: 'exam_v2_responses_{{ $examSession->id }}',

                // Computed
                get syncStatusClass() {
                    const classes = {
                        'synced': 'bg-success',
                        'syncing': 'bg-warning',
                        'offline': 'bg-danger',
                        'error': 'bg-danger'
                    };
                    return classes[this.syncStatus] || 'bg-secondary';
                },

                get syncStatusText() {
                    const texts = {
                        'synced': '✓ Synced',
                        'syncing': '⟳ Syncing...',
                        'offline': '⚠ Offline',
                        'error': '✗ Error'
                    };
                    return texts[this.syncStatus] || 'Unknown';
                },

                // Initialization
                initExam() {
                    this.loadFromLocalStorage();
                    this.updateAnsweredCount();
                    // Wait for DOM to be ready before updating tracker UI
                    this.$nextTick(() => {
                        this.initializeTrackerUI();
                    });
                    this.setupAutoSync();
                    this.setupOnlineDetection();
                    
                    console.log('V2 Exam Manager initialized', {
                        responses: this.responses,
                        answeredCount: this.answeredCount,
                        pendingSync: this.pendingSync,
                        responseValues: Object.values(this.responses),
                        nonNullResponses: Object.values(this.responses).filter(v => v !== null && v !== undefined),
                        detailedResponses: Object.entries(this.responses).map(([k, v]) => ({
                            questionId: k,
                            answer: v,
                            type: typeof v,
                            isNull: v === null,
                            isUndefined: v === undefined
                        }))
                    });
                },

                // Initialize tracker UI based on current responses and flags
                initializeTrackerUI() {
                    console.log('Initializing tracker UI with responses:', this.responses);
                    console.log('Flagged questions:', this.flaggedQuestions);
                    
                    // Get all tracker items
                    const allTrackerItems = document.querySelectorAll('.tracker-item');
                    
                    allTrackerItems.forEach(trackerItem => {
                        const questionId = parseInt(trackerItem.getAttribute('data-actual-question-id'));
                        if (questionId) {
                            this.updateTrackerUI(questionId);
                        }
                    });
                    
                    console.log('Tracker UI initialization complete');
                },

                // Local Storage Management
                loadFromLocalStorage() {
                    try {
                        const stored = localStorage.getItem(this.STORAGE_KEY);
                        if (stored) {
                            const data = JSON.parse(stored);
                            // Only load pending sync items, don't override backend responses
                            this.pendingSync = data.pendingSync || {};
                            this.pendingSyncCount = Object.keys(this.pendingSync).length;
                            console.log('Loaded pendingSync from localStorage:', this.pendingSync);
                        }
                    } catch (error) {
                        console.error('Error loading from localStorage:', error);
                    }
                },

                saveToLocalStorage() {
                    try {
                        const data = {
                            responses: this.responses,
                            pendingSync: this.pendingSync,
                            timestamp: new Date().toISOString()
                        };
                        localStorage.setItem(this.STORAGE_KEY, JSON.stringify(data));
                    } catch (error) {
                        console.error('Error saving to localStorage:', error);
                    }
                },

                clearLocalStorage() {
                    localStorage.removeItem(this.STORAGE_KEY);
                },

                // Answer Management
                handleAnswerChange(questionId, answerId) {
                    // Immediately update local state
                    this.responses[questionId] = answerId;
                    this.pendingSync[questionId] = answerId;
                    this.pendingSyncCount = Object.keys(this.pendingSync).length;
                    
                    // Save to localStorage immediately
                    this.saveToLocalStorage();
                    
                    // Update UI
                    this.updateAnsweredCount();
                    this.updateTrackerUI(questionId);
                    
                    console.log('Answer changed:', { questionId, answerId, pendingCount: this.pendingSyncCount });
                    
                    // Check if we should sync based on threshold
                    if (this.pendingSyncCount >= this.SYNC_THRESHOLD) {
                        console.log('Threshold reached, syncing now');
                        this.syncNow();
                    }
                },

                updateAnsweredCount() {
                    const validAnswers = Object.values(this.responses).filter(v => v !== null && v !== undefined);
                    this.answeredCount = validAnswers.length;
                    console.log('updateAnsweredCount called:', {
                        totalResponses: Object.keys(this.responses).length,
                        answeredCount: this.answeredCount,
                        validAnswers: validAnswers
                    });
                },

                updateTrackerUI(questionId) {
                    // Find tracker item by actual question ID
                    const trackerItem = document.querySelector(`.tracker-item[data-actual-question-id="${questionId}"]`);
                    
                    console.log('updateTrackerUI called:', {
                        questionId: questionId,
                        trackerItemFound: !!trackerItem,
                        responseValue: this.responses[questionId],
                        hasResponse: this.responses[questionId] !== null && this.responses[questionId] !== undefined,
                        isFlagged: this.flaggedQuestions.includes(questionId)
                    });
                    
                    if (trackerItem) {
                        const isAnswered = this.responses[questionId] !== null && this.responses[questionId] !== undefined;
                        const isFlagged = this.flaggedQuestions.includes(questionId);
                        
                        // Remove all state classes
                        trackerItem.classList.remove('answered', 'unanswered', 'flagged-answered', 'flagged-unanswered');
                        
                        // Apply appropriate class based on state
                        if (isFlagged && isAnswered) {
                            trackerItem.classList.add('flagged-answered');
                        } else if (isFlagged) {
                            trackerItem.classList.add('flagged-unanswered');
                        } else if (isAnswered) {
                            trackerItem.classList.add('answered');
                        } else {
                            trackerItem.classList.add('unanswered');
                        }
                        
                        // Update data attribute
                        trackerItem.setAttribute('data-is-flagged', isFlagged ? 'true' : 'false');
                        
                        console.log(`Tracker item ${questionId} updated - answered: ${isAnswered}, flagged: ${isFlagged}`);
                    } else {
                        console.warn(`Tracker item not found for question ${questionId}`);
                    }
                },

                // Flag Management
                async toggleFlag(questionId) {
                    // Optimistic UI update
                    const isFlagged = this.flaggedQuestions.includes(questionId);
                    
                    if (isFlagged) {
                        // Unflag
                        this.flaggedQuestions = this.flaggedQuestions.filter(id => id !== questionId);
                        this.pendingFlags[questionId] = 'unflag';
                    } else {
                        // Flag
                        this.flaggedQuestions.push(questionId);
                        this.pendingFlags[questionId] = 'flag';
                    }
                    
                    this.pendingFlagsCount = Object.keys(this.pendingFlags).length;
                    this.updateTrackerUI(questionId);
                    
                    console.log('Flag toggled:', { questionId, isFlagged: !isFlagged, pendingFlagsCount: this.pendingFlagsCount });
                    
                    // CRITICAL FIX: Always sync flags immediately to prevent state loss on refresh
                    await this.syncFlags();
                },

                async syncFlags() {
                    if (this.pendingFlagsCount === 0) {
                        console.log('No pending flags to sync');
                        return;
                    }

                    const flagsToSync = { ...this.pendingFlags };
                    console.log('Syncing flags:', flagsToSync);
                    
                    try {
                        const result = await @this.syncFlagsBatch(flagsToSync);
                        
                        if (result.success) {
                            console.log('Flags synced successfully', result);
                            
                            // Clear synced flags from pending
                            Object.keys(flagsToSync).forEach(questionId => {
                                delete this.pendingFlags[questionId];
                            });
                            
                            // CRITICAL FIX: Update flagged questions from server response (source of truth)
                            // Convert to integers to ensure proper comparison
                            this.flaggedQuestions = (result.flagged_questions || []).map(id => parseInt(id));
                            this.pendingFlagsCount = Object.keys(this.pendingFlags).length;
                            
                            console.log('Updated flaggedQuestions from server:', this.flaggedQuestions);
                            
                            // Update all tracker items to reflect server state
                            document.querySelectorAll('.tracker-item').forEach(trackerItem => {
                                const qId = parseInt(trackerItem.getAttribute('data-actual-question-id'));
                                if (qId) {
                                    this.updateTrackerUI(qId);
                                }
                            });
                        } else {
                            console.error('Flag sync failed:', result.error);
                            // Rollback optimistic updates on error
                            await this.reloadFlagsFromServer();
                        }
                    } catch (error) {
                        console.error('Flag sync error:', error);
                        // Rollback optimistic updates on error
                        await this.reloadFlagsFromServer();
                    }
                },

                async reloadFlagsFromServer() {
                    console.log('Reloading flags from server due to sync error');
                    try {
                        // Re-fetch flagged questions from Livewire component
                        await @this.$refresh();
                    } catch (error) {
                        console.error('Error reloading flags:', error);
                    }
                },

                // Clear Response Management
                confirmClearResponse(questionId) {
                    this.questionToClear = questionId;
                    const modal = new bootstrap.Modal(document.getElementById('clearResponseModal'));
                    modal.show();
                    
                    // Setup confirm button click handler
                    document.getElementById('confirmClearBtn').onclick = () => {
                        this.clearResponse(questionId);
                        modal.hide();
                    };
                },

                async clearResponse(questionId) {
                    console.log('Clearing response for question:', questionId);
                    
                    try {
                        const result = await @this.clearResponse(questionId);
                        
                        if (result.success) {
                            // Update local state
                            this.responses[questionId] = null;
                            delete this.pendingSync[questionId];
                            this.pendingSyncCount = Object.keys(this.pendingSync).length;
                            
                            // Save to localStorage
                            this.saveToLocalStorage();
                            
                            // Update UI
                            this.updateAnsweredCount();
                            this.updateTrackerUI(questionId);
                            
                            // Uncheck radio button
                            const radioInput = document.querySelector(`input[name="responses[${questionId}]"]:checked`);
                            if (radioInput) {
                                radioInput.checked = false;
                            }
                            
                            console.log('Response cleared successfully', {
                                was_synced: result.was_synced,
                                pending_count: this.pendingSyncCount
                            });
                        } else {
                            console.error('Failed to clear response:', result.message);
                            alert('Failed to clear response: ' + (result.message || 'Unknown error'));
                        }
                    } catch (error) {
                        console.error('Error clearing response:', error);
                        alert('An error occurred while clearing the response');
                    }
                },

                // Sync Management
                setupAutoSync() {
                    // Sync every 30 seconds
                    this.syncInterval = setInterval(() => {
                        if (this.isOnline) {
                            if (this.pendingSyncCount > 0) {
                                console.log('Auto-sync triggered for responses');
                                this.syncNow();
                            }
                            if (this.pendingFlagsCount > 0) {
                                console.log('Auto-sync triggered for flags');
                                this.syncFlags();
                            }
                        }
                    }, this.SYNC_INTERVAL_MS);
                },

                async syncNow() {
                    if (this.pendingSyncCount === 0) {
                        console.log('No pending changes to sync');
                        return;
                    }

                    if (!this.isOnline) {
                        console.log('Offline, cannot sync');
                        this.syncStatus = 'offline';
                        return;
                    }

                    this.syncStatus = 'syncing';
                    
                    // CRITICAL: Capture the questions we're about to sync BEFORE the async call
                    // This prevents race conditions where user answers new questions during sync
                    const questionsToSync = { ...this.pendingSync };
                    const questionIdsToSync = Object.keys(questionsToSync);
                    
                    console.log('Starting sync for questions:', questionIdsToSync);
                    
                    try {
                        const result = await @this.syncResponsesBatch(questionsToSync);
                        
                        if (result.success) {
                            console.log('Sync successful:', result);
                            
                            // Merge synced responses into main responses object
                            // This ensures the UI keeps showing which questions are answered
                            questionIdsToSync.forEach(questionId => {
                                this.responses[questionId] = questionsToSync[questionId];
                            });
                            
                            // Only remove the questions that were actually synced
                            // This preserves any new questions answered during the sync
                            questionIdsToSync.forEach(questionId => {
                                delete this.pendingSync[questionId];
                            });
                            
                            // Recalculate pending sync count
                            this.pendingSyncCount = Object.keys(this.pendingSync).length;
                            this.syncStatus = 'synced';
                            this.lastSyncTime = new Date().toLocaleTimeString();
                            
                            console.log('After sync - remaining pending:', this.pendingSyncCount);
                            
                            // Update answered count
                            this.updateAnsweredCount();
                            
                            // CRITICAL: Refresh tracker UI for ALL answered questions
                            // Not just the ones synced in this batch
                            Object.keys(this.responses).forEach(questionId => {
                                if (this.responses[questionId] !== null && this.responses[questionId] !== undefined) {
                                    this.updateTrackerUI(parseInt(questionId));
                                }
                            });
                            
                            // Save updated state to localStorage
                            this.saveToLocalStorage();
                        } else {
                            throw new Error(result.error || 'Sync failed');
                        }
                    } catch (error) {
                        console.error('Sync error:', error);
                        this.syncStatus = 'error';
                        
                        // Retry after 5 seconds
                        setTimeout(() => this.syncNow(), 5000);
                    }
                },

                forceSyncNow() {
                    console.log('Manual sync triggered');
                    this.syncNow();
                },

                // Online/Offline Detection
                setupOnlineDetection() {
                    window.addEventListener('online', () => {
                        console.log('Back online');
                        this.isOnline = true;
                        this.syncStatus = 'synced';
                        if (this.pendingSyncCount > 0) {
                            this.syncNow();
                        }
                    });

                    window.addEventListener('offline', () => {
                        console.log('Gone offline');
                        this.isOnline = false;
                        this.syncStatus = 'offline';
                    });
                },

            };
        }

        // V2 Show Bootstrap modal for submission confirmation
        function showV2SubmitConfirmation() {
            const modal = new bootstrap.Modal(document.getElementById('v2SubmitConfirmModal'));
            modal.show();
        }

        // Confirm and submit the exam via Livewire
        function confirmV2Submit() {
            // Close the modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('v2SubmitConfirmModal'));
            modal.hide();
            
            // Disable submit button to prevent double submission
            const submitBtn = document.getElementById('submitBtn');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Submitting...';
            }
            
            // Submit via Livewire
            @this.call('submitExam');
        }

        function scrollToQuestion(questionNumber) {
            const questionElement = document.getElementById('question-' + questionNumber);
            if (questionElement) {
                document.getElementById('questionsContainer').scrollTo({
                    top: questionElement.offsetTop - 20,
                    behavior: 'smooth'
                });
            }
        }
    </script>
</div>
