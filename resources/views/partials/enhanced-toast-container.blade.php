{{-- Enhanced Toast Container --}}
<div id="toast-container" class="position-fixed top-0 end-0 p-3" style="z-index: 9999;">
    {{-- Toasts will be dynamically inserted here --}}
</div>

<style>
.toast {
    border: none;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-lg);
    backdrop-filter: blur(10px);
    max-width: 350px;
    opacity: 0;
    transform: translateX(100%);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.toast.show {
    opacity: 1;
    transform: translateX(0);
}

.toast.fade-out {
    opacity: 0;
    transform: translateX(100%);
}

.toast-body {
    display: flex;
    align-items: center;
    padding: 1rem 1.5rem;
    font-weight: 500;
}

.toast-body i {
    font-size: 1.25rem;
    margin-right: 0.75rem;
}

.toast.bg-success {
    background: linear-gradient(135deg, var(--success-color) 0%, var(--success-dark) 100%);
    color: white;
}

.toast.bg-danger {
    background: linear-gradient(135deg, var(--danger-color) 0%, var(--danger-dark) 100%);
    color: white;
}

.toast.bg-warning {
    background: linear-gradient(135deg, var(--warning-color) 0%, var(--warning-dark) 100%);
    color: white;
}

.toast.bg-info {
    background: linear-gradient(135deg, var(--accent-color) 0%, var(--accent-dark) 100%);
    color: white;
}

.btn-close-white {
    filter: brightness(0) invert(1);
    opacity: 0.8;
}

.btn-close-white:hover {
    opacity: 1;
}

@media (max-width: 576px) {
    #toast-container {
        left: 1rem;
        right: 1rem;
        top: 1rem;
    }
    
    .toast {
        max-width: 100%;
        margin-bottom: 0.5rem;
    }
}
</style>

<script>
// Enhanced toast system with better animations and stacking
function createToastSystem() {
    const container = document.getElementById('toast-container');
    let toastId = 0;

    function createToast(type, message, duration = 4000, options = {}) {
        const id = ++toastId;
        
        const typeConfig = {
            success: { bgClass: 'bg-success', icon: 'ti-check-circle' },
            error: { bgClass: 'bg-danger', icon: 'ti-alert-circle' },
            warning: { bgClass: 'bg-warning', icon: 'ti-alert-triangle' },
            info: { bgClass: 'bg-info', icon: 'ti-info-circle' }
        };

        const config = typeConfig[type] || typeConfig.info;
        
        const toast = document.createElement('div');
        toast.id = `toast-${id}`;
        toast.className = `toast align-items-center text-white ${config.bgClass} border-0 mb-2`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');
        
        toast.innerHTML = `
            <div class="d-flex align-items-center w-100">
                <div class="toast-body flex-grow-1">
                    <i class="ti ${config.icon} me-2"></i>
                    <span>${message}</span>
                </div>
                ${options.closable !== false ? `
                <button type="button" class="btn-close btn-close-white me-2" onclick="removeToast('${id}')"></button>
                ` : ''}
            </div>
            ${options.showProgress !== false ? `
            <div class="toast-progress" style="height: 3px; background: rgba(255,255,255,0.3);">
                <div class="toast-progress-bar" style="height: 100%; background: rgba(255,255,255,0.8); width: 100%; transition: width ${duration}ms linear;"></div>
            </div>
            ` : ''}
        `;
        
        container.appendChild(toast);
        
        // Trigger animation
        requestAnimationFrame(() => {
            toast.classList.add('show');
            
            // Start progress bar animation
            if (options.showProgress !== false) {
                const progressBar = toast.querySelector('.toast-progress-bar');
                if (progressBar) {
                    setTimeout(() => {
                        progressBar.style.width = '0%';
                    }, 100);
                }
            }
        });
        
        // Auto remove
        if (duration > 0) {
            setTimeout(() => removeToast(id), duration);
        }
        
        return id;
    }

    function removeToast(id) {
        const toast = document.getElementById(`toast-${id}`);
        if (toast) {
            toast.classList.add('fade-out');
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 300);
        }
    }

    // Make functions globally available
    window.showToast = createToast;
    window.removeToast = removeToast;
    
    // Clear all toasts function
    window.clearAllToasts = function() {
        const toasts = container.querySelectorAll('.toast');
        toasts.forEach(toast => {
            const id = toast.id.replace('toast-', '');
            removeToast(id);
        });
    };

    return { createToast, removeToast };
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', createToastSystem);
} else {
    createToastSystem();
}
</script>