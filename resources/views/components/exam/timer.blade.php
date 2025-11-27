@props([
    'examSessionId',
    'startedAt',
    'completedAt',
    'hasExtraTime' => false,
    'extraTimeMinutes' => 0,
    'isRestored' => false,
    'debug' => false
])

<div {{ $attributes->merge(['class' => 'exam-timer']) }}>
    <div class="exam-timer-content">
        <div class="exam-timer-header d-flex justify-content-center align-items-center">
            <div class="exam-timer-title d-flex align-items-center">
                <div class="d-flex align-items-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-alarm me-2" viewBox="0 0 16 16">
                    <path d="M8.5 5.5a.5.5 0 0 0-1 0v3.362l-1.429 2.38a.5.5 0 1 0 .858.515l1.5-2.5A.5.5 0 0 0 8.5 9z"/>
                    <path d="M6.5 0a.5.5 0 0 0 0 1H7v1.07a7.001 7.001 0 0 0-3.273 12.474l-.602.602a.5.5 0 0 0 .707.708l.746-.746A6.97 6.97 0 0 0 8 16a6.97 6.97 0 0 0 3.422-.892l.746.746a.5.5 0 0 0 .707-.708l-.601-.602A7.001 7.001 0 0 0 9 2.07V1h.5a.5.5 0 0 0 0-1zm1.038 3.018a6 6 0 0 1 .924 0 6 6 0 1 1-.924 0M0 3.5c0 .753.333 1.429.86 1.887A8.04 8.04 0 0 1 4.387 1.86 2.5 2.5 0 0 0 0 3.5M13.5 1c-.753 0-1.429.333-1.887.86a8.04 8.04 0 0 1 3.527 3.527A2.5 2.5 0 0 0 13.5 1"/>
                  </svg>
                <span>Exam Timer</span>
                </div>
                @if($isRestored)
                <div class="extra-time-badge ms-2 d-flex align-items-center">
                    <i class="bi bi-arrow-clockwise me-1 text-warning"></i>
                    <span class="text-warning fw-semibold">Restored Session</span>
                </div>
                @elseif($hasExtraTime)
                <div class="extra-time-badge ms-2 d-flex align-items-center">
                    <i class="bi bi-plus-circle me-1"></i>
                    <span>+{{ $extraTimeMinutes }} min extra time</span>
                </div>
                @endif
            </div>
            @if($debug)
            <div class="exam-timer-controls ms-3">
                <button id="toggle-timer-debug" class="btn btn-sm btn-outline-secondary d-flex justify-content-center align-items-center" type="button">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-gear-fill" viewBox="0 0 16 16">
                        <path d="M9.405 1.05c-.413-1.4-2.397-1.4-2.81 0l-.1.34a1.464 1.464 0 0 1-2.105.872l-.31-.17c-1.283-.698-2.686.705-1.987 1.987l.169.311c.446.82.023 1.841-.872 2.105l-.34.1c-1.4.413-1.4 2.397 0 2.81l.34.1a1.464 1.464 0 0 1 .872 2.105l-.17.31c-.698 1.283.705 2.686 1.987 1.987l.311-.169a1.464 1.464 0 0 1 2.105.872l.1.34c.413 1.4 2.397 1.4 2.81 0l.1-.34a1.464 1.464 0 0 1 2.105-.872l.31.17c1.283.698 2.686-.705 1.987-1.987l-.169-.311a1.464 1.464 0 0 1 .872-2.105l.34-.1c1.4-.413 1.4-2.397 0-2.81l-.34-.1a1.464 1.464 0 0 1-.872-2.105l.17-.31c.698-1.283-.705-2.686-1.987-1.987l-.311.169a1.464 1.464 0 0 1-2.105-.872zM8 10.93a2.929 2.929 0 1 1 0-5.86 2.929 2.929 0 0 1 0 5.858z"/>
                    </svg>
                </button>
            </div>
            @endif
        </div>
        
        <div id="exam-countdown-timer" class="exam-countdown text-center">
            <!-- Timer will be displayed here -->
            <span class="placeholder-wave">
                @php
                    // Fallback calculation in PHP
                    $end = \Carbon\Carbon::parse($completedAt);
                    $now = now();
                    $diff = $end->diffInSeconds($now, false); // false = allow negative
                    
                    if ($diff < 0) {
                        // Time remaining
                        $diff = abs($diff);
                        $hours = floor($diff / 3600);
                        $minutes = floor(($diff % 3600) / 60);
                        $seconds = $diff % 60;
                        $formatted = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
                        echo '<span class="time-left font-monospace fs-4 badge bg-danger text-white p-2 px-3 rounded shadow-sm">' . $formatted . '</span>';
                    } else {
                        echo '<span class="text-danger fw-bold">Time\'s up!</span>';
                    }
                @endphp
            </span>
        </div>
        
        <div class="exam-timer-info d-flex justify-content-center">
            <div class="timer-info-item me-3">
                <span class="timer-info-label">Started</span>
                <span class="timer-info-value">{{ Carbon\Carbon::parse($startedAt)->format('h:i A') }}</span>
            </div>
            <div class="timer-info-item">
                <span class="timer-info-label">Ends</span>
                <span class="timer-info-value">{{ Carbon\Carbon::parse($completedAt)->format('h:i A') }}</span>
            </div>
            <div class="timer-info-item ms-3 border-start ps-3">
                <span class="timer-info-label">Server Time</span>
                <span id="exam-server-clock-display" class="timer-info-value fw-bold text-primary">
                    {{ now()->format('h:i:s A') }}
                </span>
            </div>
            <div class="timer-info-item ms-3 border-start ps-3">
                <span class="timer-info-label">Device Time</span>
                <span id="exam-device-clock-display" class="timer-info-value fw-bold text-success">
                    --:--:-- --
                </span>
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

<script src="{{ asset('js/services/ExamClock.js') }}"></script>
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
        examSessionId: '{{ $examSessionId }}',
        timerElementId: 'exam-countdown-timer',
        // Pass server time for ExamClock sync
        serverTimeIso: '{{ now()->toIso8601String() }}',
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
    
    // Update server time and device time clocks
    function updateClocks() {
        // Update server time (synced with ExamClock if available)
        let serverTime;
        if (window.examTimer && window.examTimer.clock) {
            serverTime = new Date(window.examTimer.clock.now());
        } else {
            // Fallback to device time
            serverTime = new Date();
        }
        
        // Update device time (always from device)
        const deviceTime = new Date();
        
        // Format times for display
        const serverTimeFormatted = serverTime.toLocaleTimeString('en-US', {
            hour: 'numeric',
            minute: '2-digit',
            second: '2-digit',
            hour12: true
        });
        
        const deviceTimeFormatted = deviceTime.toLocaleTimeString('en-US', {
            hour: 'numeric',
            minute: '2-digit',
            second: '2-digit',
            hour12: true
        });
        
        // Update display elements
        const serverClockElement = document.getElementById('exam-server-clock-display');
        const deviceClockElement = document.getElementById('exam-device-clock-display');
        
        if (serverClockElement) {
            serverClockElement.textContent = serverTimeFormatted;
        }
        
        if (deviceClockElement) {
            deviceClockElement.textContent = deviceTimeFormatted;
        }
    }
    
    // Update clocks immediately and then every second
    updateClocks();
    setInterval(updateClocks, 1000);
});
</script>