<div>
    @include('components.partials.styles.timer-styles')
    <div id="countdown" class="text-xl font-bold timer-text badge bg-danger pulse"></div>
    
    <!-- Debug information -->
    <div class="mt-3 p-3 bg-light rounded border small" id="timer-debug">
        <h6 class="fw-bold mb-2">Timer Debug Info:</h6>
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
            // Constants for localStorage keys
            const STORAGE_START_AT = 'startAt';
            const STORAGE_COMPLETED_AT = 'completedAt';
            const STORAGE_TIME_LEFT = 'exam_time_left';
            const STORAGE_LAST_SYNC = 'exam_last_sync';

            // Parse dates and store them in localStorage
            const startAt = new Date(@js($started_at)).getTime();
            const completedAt = new Date(@js($completed_at)).getTime();
            localStorage.setItem(STORAGE_START_AT, startAt);
            localStorage.setItem(STORAGE_COMPLETED_AT, completedAt);

            // Retrieve dates and saved time left
            const savedStartAt = parseInt(localStorage.getItem(STORAGE_START_AT), 10);
            const savedCompletedAt = parseInt(localStorage.getItem(STORAGE_COMPLETED_AT), 10);
            let timeLeft = parseInt(localStorage.getItem(STORAGE_TIME_LEFT), 10) || (savedCompletedAt - new Date().getTime());
            
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
                    localStorage.removeItem(STORAGE_TIME_LEFT);
                    return;
                }

                // Ensure timeLeft is not negative
                timeLeft = Math.max(timeLeft, 0);

                function updateCountdown() {
                    if (timeLeft <= 0) {
                        document.getElementById('countdown').innerText = "Time's up!";
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
                    } else {
                        updateCountdown();
                    }
                }, 1000);

                // Save the remaining time in localStorage before page unload
                window.addEventListener('beforeunload', () => {
                    localStorage.setItem(STORAGE_TIME_LEFT, timeLeft);
                });

                // Clear saved time left when the countdown is complete
                if (timeLeft <= 0) {
                    localStorage.removeItem(STORAGE_TIME_LEFT);
                }
            }

            // Check for timer updates from the server every 30 seconds
            // This will detect if extra time has been added
            setInterval(() => {
                syncWithServer();
            }, 30000);

            // Function to sync timer with server
            function syncWithServer() {
                // Call the Livewire method to get updated timer info
                @this.call('getRemainingTime').then(serverInfo => {
                    if (serverInfo) {
                        // Update the completedAt time from server (which includes any extra time)
                        const newCompletedAt = new Date(serverInfo).getTime();
                        
                        // Only update if the completion time has changed (extra time was added)
                        if (newCompletedAt > savedCompletedAt) {
                            console.log('Timer updated: Extra time detected');
                            
                            // Calculate the difference (extra time added in ms)
                            const extraTimeMs = newCompletedAt - savedCompletedAt;
                            const extraMinutes = Math.floor(extraTimeMs / (1000 * 60));
                            
                            document.getElementById('debug-extra-time').innerHTML = 
                                `<span class="text-success">${extraMinutes} minutes added</span>`;
                            
                            localStorage.setItem(STORAGE_COMPLETED_AT, newCompletedAt);
                            
                            // Recalculate time left
                            timeLeft = newCompletedAt - new Date().getTime();
                            localStorage.setItem(STORAGE_TIME_LEFT, timeLeft);
                            localStorage.setItem(STORAGE_LAST_SYNC, new Date().getTime());
                            
                            // Update debug info
                            updateDebugInfo();
                            
                            // Reload the page to reset the timer completely
                            location.reload();
                        } else {
                            document.getElementById('debug-extra-time').innerText = 'No extra time detected';
                        }
                    }
                });
            }
            
            // Check for extra time immediately on page load
            syncWithServer();
        });
    </script>
</div>
