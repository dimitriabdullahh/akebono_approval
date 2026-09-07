<!-- Countdown Timer Component -->
<script>
/**
 * Initialize countdown timer for a deadline
 * @param {string} elementId - ID of the element to update
 * @param {string} deadlineIso - ISO format deadline (e.g., "2026-08-21T23:59:00")
 * @param {function} onExpired - Callback when timer expires
 */
window.initCountdown = function(elementId, deadlineIso, onExpired = null) {
    const element = document.getElementById(elementId);
    if (!element) return;

    function updateTimer() {
        const deadline = new Date(deadlineIso).getTime();
        const now = new Date().getTime();
        const diff = deadline - now;

        if (diff <= 0) {
            element.innerHTML = '<span class="badge bg-danger"><i class="ti ti-alert-triangle me-1"></i>EXPIRED</span>';
            if (onExpired) onExpired();
            return;
        }

        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
        const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((diff % (1000 * 60)) / 1000);

        // Determine badge color based on time remaining
        let badgeClass = 'bg-success';
        if (days === 0 && hours <= 24) {
            badgeClass = 'bg-danger';
        } else if (days <= 1) {
            badgeClass = 'bg-warning';
        }

        let timeString = '';
        if (days > 0) {
            timeString = `${days}h ${hours}m`;
        } else if (hours > 0) {
            timeString = `${hours}h ${minutes}m`;
        } else {
            timeString = `${minutes}m ${seconds}s`;
        }

        element.innerHTML = `<span class="badge ${badgeClass}"><i class="ti ti-clock me-1"></i>${timeString}</span>`;
    }

    updateTimer();
    setInterval(updateTimer, 1000);
};

/**
 * Format deadline for display
 */
window.formatDeadline = function(isoDate) {
    const date = new Date(isoDate);
    return date.toLocaleDateString('id-ID', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<style>
.countdown-timer {
    font-variant-numeric: tabular-nums;
}

.countdown-timer.urgent {
    animation: pulse 1s infinite;
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.7;
    }
}
</style>
