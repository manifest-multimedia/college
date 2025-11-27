/**
 * ExamTimerService.js
 * Responsible for managing exam timer functionality including:
 * - Countdown display using Server-Synced ExamClock
 * - Server synchronization
 * - Extra time handling
 * - Auto-submission when time expires
 * 
 * REWRITTEN: May 2025 (ExamClock Architecture)
 */

class ExamTimerService {
    constructor(config) {
        // Core configuration
        this.examSessionId = config.examSessionId;
        this.timerElement = document.getElementById(config.timerElementId || 'exam-countdown-timer');

        // Time configuration
        this.serverTimeIso = config.serverTimeIso; // Current server time
        this.endTimeIso = config.endTimeIso;       // Exam strict end time

        // Features
        this.hasExtraTime = config.hasExtraTime || false;
        this.extraTimeMinutes = config.extraTimeMinutes || 0;
        this.autoSubmit = config.autoSubmit !== false;

        // Callbacks
        this.submitCallback = config.submitCallback || null;
        this.extraTimeCallback = config.extraTimeCallback || null;

        // Settings
        this.syncInterval = config.syncInterval || 30000; // 30 seconds
        this.debug = config.debug || false;
        this.debugElement = document.getElementById(config.debugElementId || 'timer-debug');

        // Initialize ExamClock
        if (typeof window.ExamClock === 'undefined') {
            console.error('ExamClock not found! Falling back to device time.');
            // Simple fallback mock
            this.clock = {
                now: () => new Date().getTime(),
                sync: () => { }
            };
        } else {
            this.clock = new window.ExamClock(this.serverTimeIso);
        }

        // State
        this.timerInterval = null;
        this.syncIntervalId = null;
        this.isTimerRunning = false;
        this.hasSubmitted = false;
        this.timeLeft = 0;

        // Endpoints
        this.endpoints = {
            checkStatus: config.endpoints?.checkStatus || '/api/exam-timer/status',
            checkExtraTime: config.endpoints?.checkExtraTime || '/api/exam-timer/extra-time',
        };

        // Bind methods
        this.update = this.update.bind(this);
        this.sync = this.sync.bind(this);

        // Start
        this.init();
    }

    init() {
        if (!this.timerElement) {
            console.error('Timer element not found');
            return;
        }

        this.log('Initializing ExamTimerService', {
            serverTime: this.serverTimeIso,
            endTime: this.endTimeIso,
            hasExtraTime: this.hasExtraTime
        });

        // Start the loop
        this.start();

        // Start background sync
        this.startSync();
    }

    start() {
        if (this.isTimerRunning) return;

        // Update immediately
        this.update();

        // Run every second
        this.timerInterval = setInterval(this.update, 1000);
        this.isTimerRunning = true;
    }

    stop() {
        if (this.timerInterval) {
            clearInterval(this.timerInterval);
            this.timerInterval = null;
        }
        this.isTimerRunning = false;
    }

    /**
     * Main update loop - Calculates remaining time using ExamClock
     */
    update() {
        const now = this.clock.now();
        const end = new Date(this.endTimeIso).getTime();

        // Calculate remaining milliseconds
        this.timeLeft = Math.max(0, end - now);

        // Update UI
        this.render(this.timeLeft);

        // Check expiration
        if (this.timeLeft <= 0 && !this.hasSubmitted) {
            this.handleExpiration();
        }
    }

    /**
     * Render the timer UI
     */
    render(milliseconds) {
        if (!this.timerElement) return;

        if (milliseconds <= 0) {
            this.timerElement.innerHTML = `<span class="text-danger fw-bold">Time's up!</span>`;
            return;
        }

        const hours = Math.floor(milliseconds / (1000 * 60 * 60));
        const minutes = Math.floor((milliseconds % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((milliseconds % (1000 * 60)) / 1000);

        const formatted = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;

        // Warning classes
        if (milliseconds < 5 * 60 * 1000) { // < 5 mins
            this.timerElement.classList.add('timer-warning');
        } else {
            this.timerElement.classList.remove('timer-warning');
        }

        this.timerElement.innerHTML = `<span class="time-left font-monospace fs-4 badge bg-danger text-white p-2 px-3 rounded shadow-sm">${formatted}</span>`;

        // Update Exam Clock Display if element exists
        const clockElement = document.getElementById('exam-clock-display');
        if (clockElement) {
            const now = this.clock.date();
            clockElement.textContent = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        }
    }

    /**
     * Handle time expiration
     */
    handleExpiration() {
        this.log('Timer expired locally. Checking with server...');

        // Double check with server before submitting
        this.sync().then(status => {
            // Recalculate with fresh sync
            const now = this.clock.now();
            const end = new Date(this.endTimeIso).getTime();

            if (end - now <= 0) {
                this.log('Expiration confirmed by server.');
                this.stop();

                if (this.autoSubmit && !this.hasSubmitted) {
                    this.hasSubmitted = true;
                    this.showNotification('Time expired. Submitting exam...', 'warning');

                    if (this.submitCallback) {
                        this.submitCallback();
                    }
                }
            } else {
                this.log('Server says time remains (Clock drift corrected). Resuming...');
                this.update(); // Update immediately with new time
            }
        });
    }

    /**
     * Start background synchronization
     */
    startSync() {
        // Initial sync
        this.sync();

        // Periodic sync
        this.syncIntervalId = setInterval(this.sync, this.syncInterval);
    }

    /**
     * Sync with server to update ExamClock and check status
     */
    async sync() {
        try {
            const response = await fetch(this.endpoints.checkStatus, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                body: JSON.stringify({ exam_session_id: this.examSessionId })
            });

            if (!response.ok) throw new Error('Sync failed');

            const data = await response.json();

            // 1. Update Clock Offset
            if (data.serverTimeIso) {
                this.clock.sync(data.serverTimeIso);
            }

            // 2. Update End Time (in case extra time was added)
            if (data.endTimeIso) {
                const oldEnd = new Date(this.endTimeIso).getTime();
                const newEnd = new Date(data.endTimeIso).getTime();

                if (newEnd !== oldEnd) {
                    this.endTimeIso = data.endTimeIso;
                    this.log('End time updated', { old: oldEnd, new: newEnd });

                    // Notify about extra time
                    if (newEnd > oldEnd) {
                        const addedMinutes = Math.round((newEnd - oldEnd) / 60000);
                        this.showNotification(`${addedMinutes} minutes of extra time added!`);

                        if (this.extraTimeCallback) {
                            this.extraTimeCallback({ extraMinutes: addedMinutes });
                        }
                    }
                }
            }

            return data;

        } catch (e) {
            console.error('Timer sync failed:', e);
            return null;
        }
    }

    showNotification(message, type = 'info') {
        const div = document.createElement('div');
        div.className = `alert alert-${type} position-fixed top-0 start-50 translate-middle-x mt-3 shadow z-index-toast`;
        div.style.zIndex = '9999';
        div.innerHTML = `
            <i class="bi bi-info-circle me-2"></i> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(div);
        setTimeout(() => div.remove(), 5000);
    }

    log(msg, data) {
        if (this.debug) {
            console.log(`[ExamTimer] ${msg}`, data || '');
        }
    }
}

window.ExamTimerService = ExamTimerService;