{{-- Loading Overlay Component --}}
<div id="loading-overlay" class="loading-overlay" style="display: none;">
    <div class="loading-content">
        <div class="loading-spinner">
            <div class="spinner-ring"></div>
            <div class="spinner-ring"></div>
            <div class="spinner-ring"></div>
            <div class="spinner-ring"></div>
        </div>
        <div class="loading-text">Memproses...</div>
        <div class="loading-subtext">Mohon tunggu sebentar</div>
    </div>
</div>

<style>
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(15, 41, 66, 0.9);
    backdrop-filter: blur(5px);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.loading-overlay.show {
    opacity: 1;
}

.loading-content {
    text-align: center;
    color: white;
}

.loading-spinner {
    display: inline-block;
    position: relative;
    width: 64px;
    height: 64px;
    margin-bottom: 1.5rem;
}

.spinner-ring {
    box-sizing: border-box;
    display: block;
    position: absolute;
    width: 51px;
    height: 51px;
    margin: 6px;
    border: 6px solid transparent;
    border-top-color: #ffffff;
    border-radius: 50%;
    animation: loading-spin 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
}

.spinner-ring:nth-child(1) { animation-delay: -0.45s; }
.spinner-ring:nth-child(2) { animation-delay: -0.3s; }
.spinner-ring:nth-child(3) { animation-delay: -0.15s; }

@keyframes loading-spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.loading-text {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.loading-subtext {
    font-size: 0.875rem;
    opacity: 0.8;
}

/* Button loading states */
.btn-loading {
    position: relative;
    pointer-events: none;
}

.btn-loading .btn-text {
    opacity: 0;
}

.btn-loading::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 1rem;
    height: 1rem;
    border: 2px solid transparent;
    border-top-color: currentColor;
    border-radius: 50%;
    animation: loading-spin 0.8s linear infinite;
}

/* Card loading overlay */
.card-loading {
    position: relative;
    pointer-events: none;
}

.card-loading::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.9);
    z-index: 10;
    border-radius: inherit;
}

.card-loading::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 2rem;
    height: 2rem;
    border: 3px solid var(--gray-300);
    border-top-color: var(--accent-color);
    border-radius: 50%;
    animation: loading-spin 1s linear infinite;
    z-index: 11;
}
</style>

<script>
// Loading overlay management
class LoadingManager {
    constructor() {
        this.overlay = document.getElementById('loading-overlay');
        this.isVisible = false;
    }

    show(text = 'Memproses...', subtext = 'Mohon tunggu sebentar') {
        if (!this.overlay) return;
        
        const textEl = this.overlay.querySelector('.loading-text');
        const subtextEl = this.overlay.querySelector('.loading-subtext');
        
        if (textEl) textEl.textContent = text;
        if (subtextEl) subtextEl.textContent = subtext;
        
        this.overlay.style.display = 'flex';
        
        // Trigger animation
        requestAnimationFrame(() => {
            this.overlay.classList.add('show');
        });
        
        this.isVisible = true;
    }

    hide() {
        if (!this.overlay || !this.isVisible) return;
        
        this.overlay.classList.remove('show');
        
        setTimeout(() => {
            this.overlay.style.display = 'none';
        }, 300);
        
        this.isVisible = false;
    }

    setButtonLoading(button, loading = true) {
        if (loading) {
            button.disabled = true;
            button.classList.add('btn-loading');
            if (!button.dataset.originalText) {
                button.dataset.originalText = button.innerHTML;
            }
        } else {
            button.disabled = false;
            button.classList.remove('btn-loading');
            if (button.dataset.originalText) {
                button.innerHTML = button.dataset.originalText;
            }
        }
    }

    setCardLoading(card, loading = true) {
        if (loading) {
            card.classList.add('card-loading');
        } else {
            card.classList.remove('card-loading');
        }
    }
}

// Initialize loading manager
window.loadingManager = new LoadingManager();

// Convenient global functions
window.showLoading = (text, subtext) => window.loadingManager.show(text, subtext);
window.hideLoading = () => window.loadingManager.hide();
window.setButtonLoading = (button, loading) => window.loadingManager.setButtonLoading(button, loading);
window.setCardLoading = (card, loading) => window.loadingManager.setCardLoading(card, loading);

// Auto-hide loading on page unload
window.addEventListener('beforeunload', () => {
    window.loadingManager.hide();
});

// Handle form submissions with loading states
document.addEventListener('submit', function(e) {
    const form = e.target;
    const submitBtn = form.querySelector('button[type="submit"]');
    
    if (submitBtn && !form.classList.contains('no-auto-loading')) {
        window.loadingManager.setButtonLoading(submitBtn, true);
        
        // Auto-remove loading state after timeout
        setTimeout(() => {
            window.loadingManager.setButtonLoading(submitBtn, false);
        }, 30000); // 30 seconds timeout
    }
});
</script>