@props([
    'examSessionId',
    'startedAt',
    'completedAt',
    'hasExtraTime' => false,
    'extraTimeMinutes' => 0,
    'debug' => false
])

<div {{ $attributes->merge(['class' => 'exam-timer']) }}>
    <div class="exam-timer-content">
        <div class="exam-timer-header">
            <div class="exam-timer-title">
                <i class="bi bi-alarm"></i>
                <span>Exam Timer</span>
                @if($hasExtraTime)
                <div class="extra-time-badge">
                    <i class="bi bi-plus-circle"></i>
                    <span>+{{ $extraTimeMinutes }} min extra time</span>
                </div>
                @endif
            </div>
            <div class="exam-timer-controls">
                @if($debug)
                <button id="toggle-timer-debug" class="btn btn-sm btn-outline-secondary" type="button">
                    <i class="bi bi-gear-fill"></i>
                </button>
                @endif
            </div>
        </div>
        
        <div id="exam-countdown-timer" class="exam-countdown">
            <!-- Timer will be displayed here -->
            <span class="placeholder-wave">Loading timer...</span>
        </div>
        
        <div class="exam-timer-info">
            <div class="timer-info-item">
                <span class="timer-info-label">Started</span>
                <span class="timer-info-value">{{ Carbon\Carbon::parse($startedAt)->format('h:i A') }}</span>
            </div>
            <div class="timer-info-item">
                <span class="timer-info-label">Ends</span>
                <span class="timer-info-value">{{ Carbon\Carbon::parse($completedAt)->format('h:i A') }}</span>
            </div>
        </div>
        
        @if($debug)
        <div id="timer-debug" class="timer-debug d-none">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="fw-bold mb-0">Timer Debug Info:</h6>
                <button id="close-timer-debug" class="btn btn-sm btn-link text-secondary p-0" type="button">
                    <i class="bi bi-x"></i>
                </button>
            </div>
            <!-- Debug logs will appear here -->
        </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Only initialize if we have the ExamTimerService available
    if (typeof ExamTimerService === 'undefined') {
        console.error('ExamTimerService not found. Please include the ExamTimerService.js script.');
        return;
    }

    // Debug panel toggle
    const toggleDebugBtn = document.getElementById('toggle-timer-debug');
    const closeDebugBtn = document.getElementById('close-timer-debug');
    const debugPanel = document.getElementById('timer-debug');
    
    if (toggleDebugBtn && debugPanel) {
        toggleDebugBtn.addEventListener('click', function() {
            debugPanel.classList.toggle('d-none');
        });
    }
    
    if (closeDebugBtn && debugPanel) {
        closeDebugBtn.addEventListener('click', function() {
            debugPanel.classList.add('d-none');
        });
    }
    
    // Initialize the timer service
    const timer = new ExamTimerService({
        examSessionId: {{ $examSessionId }},
        timerElementId: 'exam-countdown-timer',
        startTimeIso: '{{ $startedAt }}',
        endTimeIso: '{{ $completedAt }}',
        hasExtraTime: {{ $hasExtraTime ? 'true' : 'false' }},
        extraTimeMinutes: {{ $extraTimeMinutes }},
        debug: {{ $debug ? 'true' : 'false' }},
        debugElementId: 'timer-debug',
        submitCallback: function() {
            // This will be called when time expires
            @this.call('examTimeExpired').then(() => {
                console.log('Exam time expired event dispatched.');
                
                // Submit the exam form if exists
                const submitBtn = document.getElementById('submitBtn');
                if (submitBtn) {
                    console.log('Automatically submitting exam...');
                    submitBtn.click();
                }
            });
        },
        extraTimeCallback: function(extraTime) {
            // This will be called when extra time is detected
            console.log('Extra time detected:', extraTime);
            
            // Refresh the page to ensure UI consistency
            if (extraTime.recentlyAdded) {
                setTimeout(() => {
                    location.reload();
                }, 3000); // Give the user a chance to see the notification
            }
        }
    });

    // Make timer available globally for debugging
    window.examTimer = timer;
});
</script>