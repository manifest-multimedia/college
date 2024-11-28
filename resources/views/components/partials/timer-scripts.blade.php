
<script>
    document.addEventListener('DOMContentLoaded', () => {
        let remainingSeconds = Math.floor(@json($remainingTime)); // Ensure whole seconds
        const countdownElement = document.getElementById('remaining-time');

        // Function to format time as HH:MM:SS
        function formatTime(seconds) {
            const hours = Math.floor(seconds / 3600);
            const minutes = Math.floor((seconds % 3600) / 60);
            const secs = seconds % 60;
            return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
        }

        // Update the countdown every second
        const interval = setInterval(() => {
            if (remainingSeconds <= 0) {
                countdownElement.textContent = 'Time is up!';
                clearInterval(interval);
                return;
            }

            // Decrease remaining time and update display
            remainingSeconds -= 1;
            countdownElement.textContent = formatTime(remainingSeconds);
        }, 1000);

        // Periodically fetch updated remaining time from the server
        setInterval(() => {
            @this.call('getRemainingTime').then(serverTime => {
                const serverSeconds = Math.floor(serverTime); // Ensure whole seconds
                // Synchronize only if there's significant drift
                if (Math.abs(serverSeconds - remainingSeconds) > 2) {
                    remainingSeconds = serverSeconds;
                }
            });
        }, 10000); // Fetch every 10 seconds
    });
</script>