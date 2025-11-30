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
                                <h4 class="text-center text-danger"><strong> Instructions @if(isset($isPreview) && $isPreview)(One-by-One Mode)@endif</strong></h4>
                            </div>
                            <p>
                                You're being proctored by AI Sensei. <br />Any suspecious activity will result in immediate
                                disqualification. Answer one question at a time using the navigation below.
                                Total questions: {{ count($questions) }}.
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
                <div class="p-4 shadow-lg card question-card position-relative exam-protected" style="min-height: 550px; width: 100%;">
                    <div class="watermark">{{ $student_name }}</div>

                    @if ($examExpired && !$canStillSubmit)
                        <div class="alert alert-warning mb-4">
                            <h4 class="alert-heading"><i class="bi bi-exclamation-triangle me-2"></i> Exam Completed</h4>
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

                    <!-- One Question At A Time -->
                    @php
                        $qCount = count($questions);
                        $qIndex = $currentIndex ?? 0;
                        $q = $qCount > 0 ? $questions[$qIndex] : null;
                    @endphp

                    @if($q)
                        <div class="p-3 mb-2 rounded-border">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="badge bg-primary fs-6">Question {{ $qIndex + 1 }} of {{ $qCount }}</span>
                                <span class="text-muted">Marks: {{ $q['marks'] }}</span>
                            </div>
                            <div class="mb-4" style="font-size: 18px; font-weight: 600;">
                                <strong>Q{{ $qIndex + 1 }}:</strong> {!! $q['question'] !!}
                            </div>
                            <ul class="list-unstyled">
                                @foreach ($q['options'] as $option)
                                    <li class="mb-3">
                                        <label class="form-check-label d-flex align-items-center">
                                            <input type="radio" class="mx-2 form-check-input"
                                                name="responses_one[{{ $q['id'] }}]"
                                                value="{{ $option['id'] }}"
                                                wire:click="storeResponse({{ $q['id'] }}, {{ $option['id'] }})"
                                                @if (isset($responses[$q['id']]) && $responses[$q['id']] == $option['id']) checked @endif>
                                            <span style="font-size: 16px;">{{ str_replace(['→', '&rarr;', '->'], '', $option['option_text']) }}</span>
                                        </label>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                            <button class="btn btn-light" wire:click="prevQuestion" @disabled($currentIndex === 0)>
                                <i class="bi bi-arrow-left me-2"></i> Previous
                            </button>
                            @if ($examExpired && !$canStillSubmit)
                                <a href="{{ route('take-exam') }}" class="btn btn-secondary">
                                    <i class="bi bi-box-arrow-left me-2"></i> Return to Exam Login
                                </a>
                            @else
                                @if($currentIndex < $qCount - 1)
                                    <button class="btn btn-primary" wire:click="nextQuestion">
                                        Next <i class="bi bi-arrow-right ms-2"></i>
                                    </button>
                                @else
                                    <button class="btn btn-success" wire:click="submitExam">
                                        Submit Exam <i class="bi bi-check2-circle ms-2"></i>
                                    </button>
                                @endif
                            @endif
                        </div>
                    @else
                        <div class="alert alert-info">No questions available for this exam.</div>
                    @endif
                </div>
            </div>

            <!-- Sidebar Overview -->
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
                                   @if (isset($responses[$question['id']])) answered @else unanswered @endif
                                   @if ($index === $currentIndex) current-question @endif"
                                 style="width: 50px; height: 50px; line-height: 50px; cursor: pointer;"
                                 wire:click="goToQuestion({{ $index }})">
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
                        <button class="btn btn-primary w-100" wire:click="submitExam" id="submitBtn">Submit Exam</button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @include('components.partials.styles.exam-styles')
    @include('components.partials.styles.scrollbar-styles')
    @include('components.partials.exam-security')

    <!-- Timer scripts and styles -->
    <link href="{{ asset('css/exam-timer.css') }}" rel="stylesheet">
    <script src="{{ asset('js/services/ExamTimerService.js') }}"></script>

    <style>
        .question{ min-width:720px !important; width:100% !important; }
        .tracker-item { transition: all 0.3s ease; }
        .tracker-item:hover { transform: scale(1.1); box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .tracker-item.answered { background-color: #28a745; color: #fff; }
        .tracker-item.unanswered { background-color: #f8f9fa; }
        .tracker-item.current-question { 
            border: 3px solid #007bff; 
            box-shadow: 0 0 10px rgba(0, 123, 255, 0.5);
        }
    </style>

    <script>
        document.addEventListener('livewire:initialized', function() {
            Livewire.on('responseUpdated', () => {
                updateQuestionOverview();
            });
        });

        function updateQuestionOverview() {
            const answeredCount = document.querySelectorAll('input[type="radio"]:checked').length;
            const totalQuestions = {{ count($questions) }};
            const answeredCountEl = document.getElementById('answeredCount');
            if (answeredCountEl) {
                answeredCountEl.textContent = answeredCount;
            }
        }
    </script>
</div>
