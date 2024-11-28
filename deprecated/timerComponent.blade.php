public function getRemainingTime()
{
    // Parse start and end times
    $startedAt = Carbon::parse($this->examSession->started_at);
    $completedAt = Carbon::parse($this->examSession->completed_at);

    // Calculate the total duration in seconds
    $totalDurationSeconds = $startedAt->diffInSeconds($completedAt);

    // Calculate the elapsed time since the exam started
    $now = Carbon::now();
    $elapsedSeconds = $startedAt->diffInSeconds($now, false);

    // Calculate the remaining time in seconds
    $remainingSeconds = $totalDurationSeconds - $elapsedSeconds;

    if ($remainingSeconds <= 0) {
        // If time has elapsed, set "Time is up!"
        $this->remainingTime = 'Time is up!';
    } else {
        // Convert seconds into hours, minutes, and seconds
        $hours = floor($remainingSeconds / 3600);
        $minutes = floor(($remainingSeconds % 3600) / 60);
        $seconds = $remainingSeconds % 60;

        $this->hours = $hours;
        $this->minutes = $minutes;
        $this->seconds = $seconds;

        // Format the time as HH:MM:SS
        $this->remainingTime = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }

    return $this->remainingTime;
}