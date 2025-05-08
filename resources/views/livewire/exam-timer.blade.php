<div>
    @include('components.partials.styles.timer-styles')
    <div class="d-flex justify-content-between align-items-center mb-2">
        <div id="countdown" class="text-xl font-bold timer-text badge bg-danger pulse"></div>
        <button id="toggle-debug" class="btn btn-sm btn-outline-secondary" type="button">
            <i class="fas fa-cog me-1"></i> Debug
        </button>
    </div>
    
    <!-- Debug information (hidden by default) -->
    <div class="mt-3 p-3 bg-light rounded border small d-none" id="timer-debug">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="fw-bold mb-0">Timer Debug Info:</h6>
            <button id="close-debug" class="btn btn-sm btn-link text-secondary" type="button">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="d-flex flex-column">
            <div><strong>Start Time:</strong> <span id="debug-start-time">Loading...</span></div>
            <div><strong>End Time:</strong> <span id="debug-end-time">Loading...</span></div>
            <div><strong>Current Time:</strong> <span id="debug-current-time">Loading...</span></div>
            <div><strong>Time Left (s):</strong> <span id="debug-time-left-sec">Loading...</span></div>
            <div><strong>Time Left (formatted):</strong> <span id="debug-time-left-format">Loading...</span></div>
            <div><strong>Extra Time Applied:</strong> <span id="debug-extra-time">Checking...</span></div>
            <div><strong>Last Server Sync:</strong> <span id="debug-last-sync">Never</span></div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Toggle debug panel
            document.getElementById('toggle-debug').addEventListener('click', () => {
                document.getElementById('timer-debug').classList.toggle('d-none');
            });
            
            // Close debug panel
            document.getElementById('close-debug').addEventListener('click', () => {
                document.getElementById('timer-debug').classList.add('d-none');
            });
        
            // Constants for localStorage keys
            const STORAGE_KEY_PREFIX = 'exam_' + @js($exam_session_id) + '_';
            const STORAGE_START_AT = STORAGE_KEY_PREFIX + 'startAt';
            const STORAGE_COMPLETED_AT = STORAGE_KEY_PREFIX + 'completedAt';
            const STORAGE_TIME_LEFT = STORAGE_KEY_PREFIX + 'timeLeft';
            const STORAGE_LAST_SYNC = STORAGE_KEY_PREFIX + 'lastSync';

            // Parse fixed dates from server - DO NOT UPDATE THESE DURING PAGE REFRESHES
            const fixedStartAt = new Date(@js($started_at)).getTime();
            const fixedEndAt = new Date(@js($completed_at)).getTime();

            // Use the fixed times from the server
            localStorage.setItem(STORAGE_START_AT, fixedStartAt);
            localStorage.setItem(STORAGE_COMPLETED_AT, fixedEndAt);

            // Get stored values
            const savedStartAt = parseInt(localStorage.getItem(STORAGE_START_AT), 10);
            const savedCompletedAt = parseInt(localStorage.getItem(STORAGE_COMPLETED_AT), 10);
            
            // Calculate time left based on fixed end time, not browser refresh time
            let timeLeft = savedCompletedAt - new Date().getTime();
            
            // Update debug information
            function updateDebugInfo() {
                document.getElementById('debug-start-time').innerText = new Date(savedStartAt).toLocaleString();
                document.getElementById('debug-end-time').innerText = new Date(savedCompletedAt).toLocaleString();
                document.getElementById('debug-current-time').innerText = new Date().toLocaleString();
                document.getElementById('debug-time-left-sec').innerText = Math.floor(timeLeft / 1000);
                
                // Format time left for display
                const hours = Math.floor(timeLeft / 1000 / 60 / 60);
                const minutes = Math.floor(timeLeft / 1000 / 60) % 60;
                const seconds = Math.floor((timeLeft / 1000) % 60);
                const formattedTime = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
                document.getElementById('debug-time-left-format').innerText = formattedTime;
                
                // Display last sync time if available
                const lastSync = localStorage.getItem(STORAGE_LAST_SYNC);
                if (lastSync) {
                    document.getElementById('debug-last-sync').innerText = new Date(parseInt(lastSync, 10)).toLocaleString();
                }
            }
            
            // Initialize timer
            initializeTimer();
            
            // Initial debug info update
            updateDebugInfo();
            
            // Update debug info every second
            setInterval(updateDebugInfo, 1000);

            function initializeTimer() {
                // If completedAt is in the past, show "Time's up!" and stop the timer
                if (new Date().getTime() >= savedCompletedAt) {
                    document.getElementById('countdown').innerText = "Time's up!";
                    document.getElementById('debug-extra-time').innerHTML = '<span class="text-danger">Timer expired</span>';
                    
                    // Auto-submit when time is up
                    autoSubmitExam();
                    return;
                }

                // Ensure timeLeft is not negative
                timeLeft = Math.max(timeLeft, 0);

                function updateCountdown() {
                    if (timeLeft <= 0) {
                        document.getElementById('countdown').innerText = "Time's up!";
                        
                        // Auto-submit when time is up
                        autoSubmitExam();
                        return;
                    }

                    // Calculate remaining hours, minutes, and seconds
                    const hours = Math.floor(timeLeft / 1000 / 60 / 60);
                    const minutes = Math.floor(timeLeft / 1000 / 60) % 60;
                    const seconds = Math.floor((timeLeft / 1000) % 60);

                    // Format countdown timer as hh:mm:ss
                    const formattedTime = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
                    document.getElementById('countdown').innerText = 'Time Left ' + formattedTime;

                    // Decrease timeLeft by 1 second
                    timeLeft -= 1000;
                }

                // Initialize the countdown
                updateCountdown();

                // Update countdown every second
                const intervalId = setInterval(() => {
                    if (timeLeft <= 0) {
                        clearInterval(intervalId);
                        document.getElementById('countdown').innerText = "Time's up!";
                        
                        // Auto-submit when time is up
                        autoSubmitExam();
                    } else {
                        updateCountdown();
                    }
                }, 1000);
            }
            
            // Function to auto-submit the exam when time elapses
            function autoSubmitExam() {
                // Check if the exam has already been submitted
                const alreadySubmitted = localStorage.getItem(STORAGE_KEY_PREFIX + 'timeExpired') === 'true';
                
                // Get the parent container to check if we're in expired view
                const examExpired = document.body.classList.contains('exam-expired');
                
                // Only proceed if this is a new expiration and we're not already in expired view
                if (alreadySubmitted || examExpired) {
                    console.log('Exam already submitted or in expired view. Not auto-submitting again.');
                    return;
                }
                
                // Set a flag in localStorage to indicate time has expired
                localStorage.setItem(STORAGE_KEY_PREFIX + 'timeExpired', 'true');
                
                // Get the submit button element
                const submitBtn = document.getElementById('submitBtn');
                
                if (submitBtn) {
                    console.log('Time expired, auto-submitting exam...');
                    
                    // Show a brief message to the user
                    const notification = document.createElement('div');
                    notification.className = 'alert alert-warning text-center mt-2';
                    notification.innerHTML = '<strong>Time\'s up!</strong> Your exam is being submitted...';
                    document.getElementById('countdown').parentNode.appendChild(notification);
                    
                    // Wait briefly then submit the form
                    setTimeout(() => {
                        Livewire.dispatch('examTimeExpired');
                        submitBtn.click();
                    }, 2000);
                }
            }

            // Check for extra time updates from the server every 30 seconds
            setInterval(() => {
                syncWithServer();
            }, 30000);

            // Function to sync timer with server
            function syncWithServer() {
                // Call the Livewire method to get updated timer info for extra time only
                @this.call('checkForExtraTime').then(extraTimeInfo => {
                    if (extraTimeInfo && extraTimeInfo.hasExtraTime) {
                        // Get the new end time with extra time
                        const newCompletedAt = new Date(extraTimeInfo.newEndTime).getTime();
                        
                        if (newCompletedAt > savedCompletedAt) {
                            // Calculate the extra time added in minutes
                            const extraTimeMinutes = Math.floor((newCompletedAt - savedCompletedAt) / (1000 * 60));
                            
                            document.getElementById('debug-extra-time').innerHTML = 
                                `<span class="text-success">${extraTimeMinutes} minutes added</span>`;
                            
                            // Update the end time in localStorage
                            localStorage.setItem(STORAGE_COMPLETED_AT, newCompletedAt);
                            
                            // Recalculate time left
                            timeLeft = newCompletedAt - new Date().getTime();
                            localStorage.setItem(STORAGE_LAST_SYNC, new Date().getTime());
                            
                            // Update debug info
                            updateDebugInfo();
                            
                            // Reload the page for a complete timer reset
                            location.reload();
                        }
                    } else {
                        document.getElementById('debug-extra-time').innerText = 'No extra time detected';
                    }
                });
            }
            
            // Check for extra time immediately on page load
            syncWithServer();
        });
    </script>
</div>
