/**
 * ExamClock.js
 * Responsible for synchronizing client time with server time.
 * Provides an accurate 'now()' timestamp independent of device clock drift.
 */
class ExamClock {
    constructor(serverTimeIso) {
        this.offset = 0;
        this.serverTimeIso = serverTimeIso;

        if (serverTimeIso) {
            this.sync(serverTimeIso);
        }
    }

    /**
     * Synchronize with a new server timestamp
     * @param {string} serverTimeIso - ISO 8601 timestamp from server
     */
    sync(serverTimeIso) {
        if (!serverTimeIso) return;

        try {
            const serverTime = new Date(serverTimeIso).getTime();
            const clientTime = new Date().getTime();

            // Calculate the difference: Server - Client
            // If Server is ahead (e.g. 10:05) and Client is behind (10:00), offset is +5min
            // Client(10:00) + Offset(5min) = TrueTime(10:05)
            this.offset = serverTime - clientTime;

            console.log('[ExamClock] Synced with server. Offset:', this.offset, 'ms');
        } catch (e) {
            console.error('[ExamClock] Sync failed:', e);
        }
    }

    /**
     * Get the current server-synced time
     * @returns {number} Timestamp in milliseconds
     */
    now() {
        return new Date().getTime() + this.offset;
    }

    /**
     * Get the current server-synced Date object
     * @returns {Date}
     */
    date() {
        return new Date(this.now());
    }
}

// Make available globally
window.ExamClock = ExamClock;
