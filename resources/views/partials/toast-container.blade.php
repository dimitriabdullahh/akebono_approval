<!-- Toast Container -->
<div class="toast-container position-fixed top-0 end-0 p-3" id="toastContainer"></div>

<script>
/**
 * Show a toast notification
 * @param {string} type - 'success', 'error', 'warning', 'info'
 * @param {string} message - The message to display
 * @param {number} duration - Duration in milliseconds (default: 3000)
 */
window.showToast = function(type, message, duration = 3000) {
    const typeMap = {
        'success': { bgClass: 'bg-success', icon: 'ti-check-circle' },
        'error': { bgClass: 'bg-danger', icon: 'ti-alert-circle' },
        'warning': { bgClass: 'bg-warning', icon: 'ti-alert-triangle' },
        'info': { bgClass: 'bg-info', icon: 'ti-info-circle' }
    };

    const config = typeMap[type] || typeMap['info'];

    const toastHTML = `
        <div class="toast align-items-center text-white ${config.bgClass} border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="ti ${config.icon} me-2"></i>${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;

    const toastContainer = document.getElementById('toastContainer');
    const toastElement = document.createElement('div');
    toastElement.innerHTML = toastHTML;
    toastContainer.appendChild(toastElement.firstElementChild);

    const toastBs = new bootstrap.Toast(toastContainer.lastElementChild);
    toastBs.show();

    // Remove element after toast hides
    setTimeout(() => {
        toastContainer.lastElementChild?.remove();
    }, duration + 500);
};

/**
 * Show skeleton loading for a container
 */
window.showSkeleton = function(containerId, lines = 3) {
    const container = document.getElementById(containerId);
    if (!container) return;

    let skeletonHTML = '';
    for (let i = 0; i < lines; i++) {
        skeletonHTML += `
            <div class="mb-3">
                <div class="placeholder-glow">
                    <span class="placeholder col-${Math.random() > 0.5 ? '12' : '8'} rounded"></span>
                </div>
            </div>
        `;
    }

    container.innerHTML = skeletonHTML;
};

/**
 * Hide skeleton loading
 */
window.hideSkeleton = function(containerId) {
    const container = document.getElementById(containerId);
    if (container) {
        container.innerHTML = '';
    }
};
</script>

<style>
.toast-container {
    z-index: 9999;
}

.placeholder {
    height: 1rem;
    background-color: #e9ecef;
}

.placeholder-glow .placeholder {
    animation: placeholder-shimmer 2s linear infinite;
}

@keyframes placeholder-shimmer {
    0% {
        background-color: #e9ecef;
    }
    50% {
        background-color: #f5f6f7;
    }
    100% {
        background-color: #e9ecef;
    }
}
</style>
