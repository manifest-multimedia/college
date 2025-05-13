<x-dashboard.default title="Exam Review - {{ $exam->course->name }}">
    <div class="container mt-4">
        <div class="alert alert-info">
            <div class="d-flex align-items-center">
                <i class="bi bi-info-circle-fill me-3 fs-4"></i>
                <div>
                    <strong>Exam Completed</strong> - You are viewing this exam in read-only mode. The exam was completed on {{ $examSession->completed_at->format('F j, Y \a\t g:i A') }}.
                </div>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h1 class="card-title">
                        <i class="fa fa-file-text"></i> {{ $exam->course->name }} Exam (Results)
                    </h1>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Student:</strong> {{ $student->user->name ?? $student->first_name . ' ' . $student->last_name }}</p>
                        <p><strong>Student ID:</strong> {{ $student_index }}</p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <p><strong>Score:</strong> {{ $examSession->score ?? 'N/A' }}</p>
                        <p><strong>Completion Date:</strong> {{ $examSession->completed_at->format('F j, Y g:i A') }}</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Display questions and answers in read-only format -->
        <div class="row">
            <div class="col-md-9">
                @foreach ($questions as $index => $question)
                    <div class="card mb-4 question-card" id="question-{{ $index + 1 }}">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Question {{ $index + 1 }} of {{ count($questions) }}</h5>
                            <span class="badge bg-secondary">{{ $question['marks'] }} {{ $question['marks'] > 1 ? 'marks' : 'mark' }}</span>
                        </div>
                        <div class="card-body">
                            <div class="question-text mb-3">{!! $question['question'] !!}</div>
                            
                            <div class="options">
                                @php
                                    $selectedOption = $responses[$question['id']] ?? null;
                                    $correctOption = null;
                                    foreach($question['options'] as $opt) {
                                        if($opt['is_correct']) {
                                            $correctOption = $opt['id'];
                                        }
                                    }
                                    $isCorrect = ($selectedOption && $correctOption && $selectedOption == $correctOption);
                                @endphp
                                
                                @foreach ($question['options'] as $option)
                                    <div class="form-check mb-2 option-display 
                                        @if($option['id'] == $correctOption) correct-option @endif
                                        @if($option['id'] == $selectedOption && $option['id'] != $correctOption) wrong-option @endif">
                                        <input class="form-check-input" type="radio" 
                                            {{ ($selectedOption == $option['id']) ? 'checked' : '' }}
                                            disabled>
                                        <label class="form-check-label {{ ($selectedOption == $option['id']) ? 'fw-bold' : '' }}">
                                            {{ $option['option_text'] }}
                                            @if($option['id'] == $correctOption)
                                                <span class="badge bg-success ms-2">Correct Answer</span>
                                            @endif
                                            @if($option['id'] == $selectedOption && $option['id'] != $correctOption)
                                                <span class="badge bg-danger ms-2">Your Answer</span>
                                            @endif
                                            @if($option['id'] == $selectedOption && $option['id'] == $correctOption)
                                                <span class="badge bg-primary ms-2">Your Answer (Correct)</span>
                                            @endif
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
                
                <div class="text-center mt-4 mb-5">
                    <a href="{{ route('take-exam') }}" class="btn btn-primary">
                        <i class="bi bi-arrow-left"></i> Back to Exam Portal
                    </a>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card sticky-top" style="top: 20px">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Questions Overview</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-column align-items-center mb-3">
                            <div class="score-display">
                                <h3 class="mb-0">{{ $examSession->score ?? 'N/A' }}</h3>
                                <small>Score</small>
                            </div>
                            <div class="mt-2">
                                <span class="badge bg-success me-1">{{ count(array_filter($responses)) }}</span> of {{ count($questions) }} questions answered
                            </div>
                        </div>
                        
                        <div class="questions-navigator">
                            <div class="flex-wrap gap-2 tracker-container d-flex justify-content-center">
                                @foreach ($questions as $index => $question)
                                    @php
                                        $selectedOption = $responses[$question['id']] ?? null;
                                        $correctOption = null;
                                        foreach($question['options'] as $opt) {
                                            if($opt['is_correct']) {
                                                $correctOption = $opt['id'];
                                            }
                                        }
                                        $isCorrect = ($selectedOption && $correctOption && $selectedOption == $correctOption);
                                        $isAnswered = $selectedOption !== null;
                                        
                                        if ($isAnswered) {
                                            $statusClass = $isCorrect ? 'correct' : 'incorrect';
                                        } else {
                                            $statusClass = 'unanswered';
                                        }
                                    @endphp
                                    
                                    <div 
                                        class="tracker-item rounded-circle text-center {{ $statusClass }}"
                                        style="width: 40px; height: 40px; line-height: 40px; cursor: pointer; margin-bottom: 8px;"
                                        data-question-id="{{ $index + 1 }}"
                                        onclick="scrollToQuestion({{ $index + 1 }})"
                                    >
                                        {{ $index + 1 }}
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <div class="tracker-legend">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="tracker-item-small correct me-2"></div>
                                    <small>Correct Answer</small>
                                </div>
                                <div class="d-flex align-items-center mb-2">
                                    <div class="tracker-item-small incorrect me-2"></div>
                                    <small>Incorrect Answer</small>
                                </div>
                                <div class="d-flex align-items-center">
                                    <div class="tracker-item-small unanswered me-2"></div>
                                    <small>Not Answered</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <style>
        .option-display {
            padding: 10px;
            border-radius: 4px;
            transition: background-color 0.2s;
        }
        
        .correct-option {
            background-color: rgba(40, 167, 69, 0.1);
            border-left: 3px solid #28a745;
        }
        
        .wrong-option {
            background-color: rgba(220, 53, 69, 0.1);
            border-left: 3px solid #dc3545;
        }
        
        .tracker-item {
            transition: all 0.2s ease;
            margin: 3px;
        }
        
        .tracker-item:hover {
            transform: scale(1.1);
        }
        
        .tracker-item.correct {
            background-color: #28a745;
            color: white;
        }
        
        .tracker-item.incorrect {
            background-color: #dc3545;
            color: white;
        }
        
        .tracker-item.unanswered {
            background-color: #f8f9fa;
            color: #6c757d;
        }
        
        .score-display {
            text-align: center;
        }
        
        .tracker-item-small {
            width: 15px;
            height: 15px;
            border-radius: 50%;
            display: inline-block;
        }
        
        .tracker-item-small.correct {
            background-color: #28a745;
        }
        
        .tracker-item-small.incorrect {
            background-color: #dc3545;
        }
        
        .tracker-item-small.unanswered {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
        }
    </style>
    
    <script>
        function scrollToQuestion(questionNumber) {
            const questionElement = document.getElementById('question-' + questionNumber);
            if (questionElement) {
                questionElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
                
                // Highlight the question briefly
                questionElement.classList.add('highlight-question');
                setTimeout(() => {
                    questionElement.classList.remove('highlight-question');
                }, 2000);
            }
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            // Send heartbeats to keep the session alive for viewing purposes
            const heartbeatInterval = setInterval(function() {
                if (document.visibilityState === 'visible') {
                    @this.heartbeat();
                }
            }, 60000); // Send heartbeat every minute (can be longer for read-only mode)
            
            // Clear interval on page unload
            window.addEventListener('beforeunload', function() {
                clearInterval(heartbeatInterval);
            });
        });
    </script>
    
    <style>
        .highlight-question {
            box-shadow: 0 0 15px rgba(0, 123, 255, 0.5);
            animation: pulse 1.5s;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.02); }
            100% { transform: scale(1); }
        }
    </style>
</x-dashboard.default>