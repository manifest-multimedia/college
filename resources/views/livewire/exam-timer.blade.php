<div>
    <div id="countdown" class="text-xl font-bold badge bg-danger pulse"></div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Constants for localStorage keys
            const STORAGE_START_AT = 'startAt';
            const STORAGE_COMPLETED_AT = 'completedAt';
            const STORAGE_TIME_LEFT = 'exam_time_left';

            // Parse dates and store them in localStorage
            const startAt = new Date(@js($started_at)).getTime();
            const completedAt = new Date(@js($completed_at)).getTime();
            localStorage.setItem(STORAGE_START_AT, startAt);
            localStorage.setItem(STORAGE_COMPLETED_AT, completedAt);

            // Retrieve dates and saved time left
            const savedStartAt = parseInt(localStorage.getItem(STORAGE_START_AT), 10);
            const savedCompletedAt = parseInt(localStorage.getItem(STORAGE_COMPLETED_AT), 10);
            let timeLeft = parseInt(localStorage.getItem(STORAGE_TIME_LEFT), 10) || (savedCompletedAt - new Date().getTime());

            // Ensure timeLeft is not negative
            timeLeft = Math.max(timeLeft, 0);

            function updateCountdown() {
                if (timeLeft <= 0) {
                    document.getElementById('countdown').innerText = "Time's up!";
                    return;
                }

                // Calculate remaining minutes and seconds
                const hours = Math.floor(timeLeft / 1000 / 60 / 60);
                const minutes = Math.floor(timeLeft / 1000 / 60);
                const seconds = Math.floor((timeLeft / 1000) % 60);

                // Format countdown timer as hh:mm:ss

                const formattedTime = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
                document.getElementById('countdown').innerText ='Time Left ' + formattedTime;

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
        });
    </script>
</div>
