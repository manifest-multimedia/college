/**
 * ExamTimerService.js
 * Responsible for managing exam timer functionality including:
 * - Countdown display
 * - Server synchronization
 * - Extra time handling
 * - Auto-submission when time expires
 * - Works in slow network environments
 * 
 * CREATED: May 12, 2025
 */

class ExamTimerService {
    constructor(config) {
        // Core timer properties
        this.examSessionId = config.examSessionId;
        this.timerElement = document.getElementById(config.timerElementId || 'exam-countdown-timer');
        this.startTimeIso = config.startTimeIso;
        this.endTimeIso = config.endTimeIso;
        this.hasExtraTime = config.hasExtraTime || false;
        this.extraTimeMinutes = config.extraTimeMinutes || 0;
        this.autoSubmit = config.autoSubmit !== false;
        this.submitCallback = config.submitCallback || null;
        this.extraTimeCallback = config.extraTimeCallback || null;
        this.syncInterval = config.syncInterval || 15000; // 15 seconds by default

        // localStorage prefix for exam session
        this.storagePrefix = `exam_${this.examSessionId}_`;

        // Timer state
        this.timeLeft = 0;
        this.timerInterval = null;
        this.syncIntervalId = null;
        this.isTimerRunning = false;
        this.hasSubmitted = false;

        // Debug properties (optional)
        this.debug = config.debug || false;
        this.debugElement = document.getElementById(config.debugElementId || 'timer-debug');

        // Server endpoints
        this.endpoints = {
            checkStatus: config.endpoints?.checkStatus || '/api/exam-timer/status',
            checkExtraTime: config.endpoints?.checkExtraTime || '/api/exam-timer/extra-time',
            submitExam: config.endpoints?.submitExam || '/api/exam/submit',
        };

        // Initialize storage keys
        this.storageKeys = {
            startAt: `${this.storagePrefix}startAt`,
            endAt: `${this.storagePrefix}endAt`,
            lastSync: `${this.storagePrefix}lastSync`,
            hasExtraTime: `${this.storagePrefix}hasExtraTime`,
            timeExpired: `${this.storagePrefix}timeExpired`
        };

        // Bind methods
        this.init = this.init.bind(this);
        this.start = this.start.bind(this);
        this.stop = this.stop.bind(this);
        this.updateCountdown = this.updateCountdown.bind(this);
        this.syncWithServer = this.syncWithServer.bind(this);
        this.checkExtraTime = this.checkExtraTime.bind(this);
        this.checkTimerStatus = this.checkTimerStatus.bind(this);
        this.autoSubmitExam = this.autoSubmitExam.bind(this);
        this.formatTime = this.formatTime.bind(this);
        this.showNotification = this.showNotification.bind(this);
        this.log = this.log.bind(this);

        // Initialize timer
        if (this.timerElement) {
            this.init();
        } else {
            console.error('Timer element not found. Timer cannot be initialized.');
        }
    }

    /**
     * Initialize the timer
     */
    init() {
        // Parse the ISO strings into timestamps
        const startTimestamp = new Date(this.startTimeIso).getTime();
        const endTimestamp = new Date(this.endTimeIso).getTime();

        // Store in localStorage for persistence across page reloads
        localStorage.setItem(this.storageKeys.startAt, startTimestamp);
        localStorage.setItem(this.storageKeys.endAt, endTimestamp);
        localStorage.setItem(this.storageKeys.hasExtraTime, this.hasExtraTime);

        // Show loading state
        if (this.timerElement) {
            this.timerElement.innerHTML = '<span class="placeholder-wave">Syncing timer...</span>';
        }

        // Get initial time from server to avoid device time issues
        this.checkTimerStatus().then(status => {
            if (status && status.remainingSeconds !== undefined) {
                // Use server-calculated remaining time (convert to milliseconds)
                this.timeLeft = Math.max(0, status.remainingSeconds * 1000);

                this.log('Timer initialized with server time', {
                    serverRemainingSeconds: status.remainingSeconds,
                    timeLeft: this.formatTime(this.timeLeft),
                    hasExtraTime: this.hasExtraTime
                });
            } else {
                // Fallback to calculated time if server unavailable
                const now = new Date().getTime();
                this.timeLeft = Math.max(0, endTimestamp - now);

                this.log('Timer initialized with device time (fallback)', {
                    startTime: new Date(startTimestamp).toLocaleString(),
                    endTime: new Date(endTimestamp).toLocaleString(),
                    timeLeft: this.formatTime(this.timeLeft),
                    hasExtraTime: this.hasExtraTime
                });
            }

            // Update the timer element with initial time
            this.updateCountdown();

            // Start the timer
            this.start();

            // Start background sync
            this.startBackgroundSync();

            // Set extra time info if available
            if (this.hasExtraTime && this.extraTimeMinutes > 0) {
                this.showNotification(`You have ${this.extraTimeMinutes} minutes of extra time for this exam.`);
            }
        }).catch(error => {
            // If server check fails, use device time as fallback
            console.error('Failed to get initial server time, using device time:', error);
            const now = new Date().getTime();
            this.timeLeft = Math.max(0, endTimestamp - now);

            this.updateCountdown();
            this.start();
            this.startBackgroundSync();

            this.log('Timer initialized with device time (error fallback)', {
                error: error.message,
                timeLeft: this.formatTime(this.timeLeft)
            });
        });
    }

    /**
     * Start the timer countdown
     */
    start() {
        if (this.isTimerRunning) return;

        // Create timer interval
        this.timerInterval = setInterval(() => {
            // Get current time left from our storage
            const endTimestamp = parseInt(localStorage.getItem(this.storageKeys.endAt), 10);
            const now = new Date().getTime();
            this.timeLeft = Math.max(0, endTimestamp - now);

            // Update the countdown display
            this.updateCountdown();

            // Check if time has expired
            if (this.timeLeft <= 0) {
                // For extra time students, double-check with server before submitting
                if (this.hasExtraTime) {
                    this.checkTimerStatus().then(status => {
                        if (!status.isActive) {
                            this.stop();
                            if (this.autoSubmit) {
                                this.autoSubmitExam();
                            }
                        } else {
                            // Extra time was added server-side
                            const newEndTime = new Date(status.endTimeIso).getTime();
                            localStorage.setItem(this.storageKeys.endAt, newEndTime);
                            this.timeLeft = Math.max(0, newEndTime - new Date().getTime());
                            this.updateCountdown();
                        }
                    });
                } else {
                    // Stop the timer
                    this.stop();

                    // Auto-submit if enabled
                    if (this.autoSubmit) {
                        this.autoSubmitExam();
                    }
                }
            }
        }, 1000);

        this.isTimerRunning = true;
    }

    /**
     * Stop the timer countdown
     */
    stop() {
        if (this.timerInterval) {
            clearInterval(this.timerInterval);
            this.timerInterval = null;
        }

        this.isTimerRunning = false;
    }

    /**
     * Update the countdown display
     */
    updateCountdown() {
        if (!this.timerElement) return;

        if (this.timeLeft <= 0) {
            this.timerElement.innerHTML = `<span class="text-danger">Time's up!</span>`;
            return;
        }

        // Calculate hours, minutes, and seconds
        const formattedTime = this.formatTime(this.timeLeft);

        // Update the timer element
        this.timerElement.innerHTML = `<span class="time-left">Time Left: ${formattedTime}</span>`;

        // If less than 5 minutes, make it flash red
        if (this.timeLeft < 5 * 60 * 1000) {
            this.timerElement.classList.add('timer-warning');
        } else {
            this.timerElement.classList.remove('timer-warning');
        }
    }

    /**
     * Start background synchronization with server
     */
    startBackgroundSync() {
        // Check immediately on initialization
        this.syncWithServer();

        // Set interval for periodic syncing
        this.syncIntervalId = setInterval(this.syncWithServer, this.syncInterval);
    }

    /**
     * Stop background synchronization
     */
    stopBackgroundSync() {
        if (this.syncIntervalId) {
            clearInterval(this.syncIntervalId);
            this.syncIntervalId = null;
        }
    }

    /**
     * Sync timer with server
     */
    syncWithServer() {
        Promise.all([
            this.checkTimerStatus(),
            this.checkExtraTime()
        ]).then(([status, extraTime]) => {
            const now = new Date().getTime();

            // Update last sync time
            localStorage.setItem(this.storageKeys.lastSync, now);

            // PRIORITY 1: Use server's remaining seconds as source of truth
            // This prevents drift from incorrect device time
            if (status && status.remainingSeconds !== undefined) {
                const serverTimeLeft = status.remainingSeconds * 1000; // Convert to milliseconds

                // Update our time left to match server
                this.timeLeft = Math.max(0, serverTimeLeft);

                this.log('Timer synchronized with server', {
                    serverRemainingSeconds: status.remainingSeconds,
                    localTimeLeft: this.formatTime(this.timeLeft),
                    isActive: status.isActive
                });
            }

            // PRIORITY 2: Process any extra time updates
            if (extraTime && extraTime.hasExtraTime) {
                // Update extra time info
                this.hasExtraTime = true;
                this.extraTimeMinutes = extraTime.extraMinutes || this.extraTimeMinutes;

                // Get the new end time
                const newEndTime = new Date(extraTime.newEndTime).getTime();
                const currentEndTime = parseInt(localStorage.getItem(this.storageKeys.endAt), 10);

                // Only update if the new end time is actually later
                if (newEndTime > currentEndTime) {
                    localStorage.setItem(this.storageKeys.endAt, newEndTime);
                    localStorage.setItem(this.storageKeys.hasExtraTime, true);

                    // Use server's remaining seconds if available, otherwise calculate
                    if (status && status.remainingSeconds !== undefined) {
                        this.timeLeft = Math.max(0, status.remainingSeconds * 1000);
                    } else {
                        this.timeLeft = Math.max(0, newEndTime - now);
                    }

                    // Show notification if recently added
                    if (extraTime.recentlyAdded) {
                        this.showNotification(`${this.extraTimeMinutes} minutes of extra time have been added to your exam.`);

                        // Execute callback if provided
                        if (this.extraTimeCallback && typeof this.extraTimeCallback === 'function') {
                            this.extraTimeCallback(extraTime);
                        }
                    }

                    this.log('Extra time applied', {
                        extraMinutes: this.extraTimeMinutes,
                        newEndTime: new Date(newEndTime).toLocaleString(),
                        timeLeft: this.formatTime(this.timeLeft)
                    });
                }
            }

            // PRIORITY 3: Handle timer state corrections
            if (status) {
                // If local timer is expired but server says it's active, reset the timer
                if (this.timeLeft <= 0 && status.isActive) {
                    const newEndTime = new Date(status.endTimeIso).getTime();
                    localStorage.setItem(this.storageKeys.endAt, newEndTime);

                    // Use server's remaining seconds
                    if (status.remainingSeconds !== undefined) {
                        this.timeLeft = Math.max(0, status.remainingSeconds * 1000);
                    } else {
                        this.timeLeft = Math.max(0, newEndTime - now);
                    }

                    // Restart timer if it was stopped
                    if (!this.isTimerRunning) {
                        this.start();
                    }

                    this.log('Timer reset from server', {
                        newEndTime: new Date(newEndTime).toLocaleString(),
                        timeLeft: this.formatTime(this.timeLeft)
                    });
                }
            }
        }).catch(error => {
            console.error('Error syncing with server:', error);
            this.log('Sync error - continuing with local timer', {
                error: error.message
            }, 'warn');
        });
    }

    /**
     * Check timer status with server
     */
    async checkTimerStatus() {
        try {
            // Use fetch with credentials to ensure cookies are sent
            const response = await fetch(this.endpoints.checkStatus, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify({
                    exam_session_id: this.examSessionId
                }),
                credentials: 'same-origin'
            });

            if (!response.ok) {
                throw new Error(`Server returned ${response.status}: ${response.statusText}`);
            }

            return await response.json();
        } catch (error) {
            this.log('Error checking timer status', error, 'error');

            // Return last known state from localStorage as fallback
            const endTime = parseInt(localStorage.getItem(this.storageKeys.endAt), 10);
            const now = new Date().getTime();

            return {
                isActive: endTime > now,
                endTimeIso: new Date(endTime).toISOString()
            };
        }
    }

    /**
     * Check for extra time updates
     */
    async checkExtraTime() {
        try {
            // Use fetch with credentials to ensure cookies are sent
            const response = await fetch(this.endpoints.checkExtraTime, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify({
                    exam_session_id: this.examSessionId
                }),
                credentials: 'same-origin'
            });

            if (!response.ok) {
                throw new Error(`Server returned ${response.status}: ${response.statusText}`);
            }

            return await response.json();
        } catch (error) {
            this.log('Error checking for extra time', error, 'error');

            // Return current state as fallback
            return {
                hasExtraTime: localStorage.getItem(this.storageKeys.hasExtraTime) === 'true',
                extraMinutes: this.extraTimeMinutes,
                newEndTime: new Date(parseInt(localStorage.getItem(this.storageKeys.endAt), 10)).toISOString(),
                recentlyAdded: false
            };
        }
    }

    /**
     * Auto-submit the exam when time expires
     */
    autoSubmitExam() {
        // Prevent multiple submissions
        if (this.hasSubmitted) return;

        // Double-check with server before submitting (important for network delays)
        this.checkTimerStatus().then(status => {
            if (!status.isActive) {
                this.hasSubmitted = true;
                localStorage.setItem(this.storageKeys.timeExpired, 'true');

                // Show notification
                this.showNotification('Time\'s up! Your exam is being submitted...', 'warning');

                // Call submit callback if provided
                if (this.submitCallback && typeof this.submitCallback === 'function') {
                    this.submitCallback();
                }

                this.log('Exam auto-submitted due to time expiration');
            } else {
                // Server says timer is still active, reset local timer
                const newEndTime = new Date(status.endTimeIso).getTime();
                localStorage.setItem(this.storageKeys.endAt, newEndTime);
                this.timeLeft = Math.max(0, newEndTime - new Date().getTime());
                this.start(); // Restart timer
            }
        }).catch(error => {
            console.error('Error during auto-submission:', error);

            // Still try to submit if we can't reach the server after timeout is detected
            if (this.submitCallback && typeof this.submitCallback === 'function') {
                this.submitCallback();
            }
        });
    }

    /**
     * Format milliseconds into hh:mm:ss format
     */
    formatTime(milliseconds) {
        // Calculate hours, minutes, and seconds
        const hours = Math.floor(milliseconds / (1000 * 60 * 60));
        const minutes = Math.floor((milliseconds % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((milliseconds % (1000 * 60)) / 1000);

        // Format as hh:mm:ss
        return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    }

    /**
     * Show notification to user
     */
    showNotification(message, type = 'success') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show`;
        notification.innerHTML = `
            ${type === 'success' ? '<i class="bi bi-check-circle-fill me-2"></i>' :
                type === 'warning' ? '<i class="bi bi-exclamation-triangle-fill me-2"></i>' :
                    '<i class="bi bi-info-circle-fill me-2"></i>'}
            <strong>${type === 'success' ? 'Success!' : type === 'warning' ? 'Warning!' : 'Information'}</strong> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;

        // Find a good place to show the notification
        const container = document.querySelector('.container') || document.body;
        container.insertBefore(notification, container.firstChild);

        // Auto-dismiss after 10 seconds
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 500);
        }, 10000);
    }

    /**
     * Logger function for debugging
     */
    log(message, data = {}, level = 'info') {
        if (!this.debug) return;

        const logFunction = level === 'error' ? console.error :
            level === 'warn' ? console.warn : console.log;

        logFunction(`[ExamTimer] ${message}`, data);

        // Update debug element if available
        if (this.debugElement) {
            const logItem = document.createElement('div');
            logItem.className = `log-item log-${level}`;
            logItem.innerHTML = `
                <span class="log-time">${new Date().toLocaleTimeString()}</span>
                <span class="log-message">${message}</span>
                <span class="log-data">${JSON.stringify(data)}</span>
            `;
            this.debugElement.appendChild(logItem);

            // Scroll to bottom
            this.debugElement.scrollTop = this.debugElement.scrollHeight;
        }
    }

    /**
     * Get timer status
     */
    getStatus() {
        return {
            timeLeft: this.timeLeft,
            formattedTimeLeft: this.formatTime(this.timeLeft),
            isRunning: this.isTimerRunning,
            hasExtraTime: this.hasExtraTime,
            extraTimeMinutes: this.extraTimeMinutes,
            hasSubmitted: this.hasSubmitted
        };
    }
}

// Make the class available globally
window.ExamTimerService = ExamTimerService;