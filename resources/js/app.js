require('./bootstrap');

// ========================================
// Akebono Approval System - Main JS
// ========================================

class AkebonoApp {
    constructor() {
        this.init();
    }

    init() {
        this.setupToastSystem();
        this.setupLoadingStates();
        this.setupFormValidation();
        this.setupFileUploads();
        this.setupSearchAndFilters();
        this.setupAnimations();
        this.setupKeyboardNavigation();
    }

    // Toast Notification System
    setupToastSystem() {
        window.showToast = (type, message, duration = 4000) => {
            const toastContainer = this.getToastContainer();
            const toast = this.createToast(type, message);
            
            toastContainer.appendChild(toast);
            
            // Trigger animation
            setTimeout(() => toast.classList.add('show'), 100);
            
            // Auto remove
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300);
            }, duration);
        };
    }

    getToastContainer() {
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'position-fixed top-0 end-0 p-3';
            container.style.zIndex = '9999';
            document.body.appendChild(container);
        }
        return container;
    }

    createToast(type, message) {
        const typeConfig = {
            success: { bgClass: 'bg-success', icon: 'ti-check-circle' },
            error: { bgClass: 'bg-danger', icon: 'ti-alert-circle' },
            warning: { bgClass: 'bg-warning', icon: 'ti-alert-triangle' },
            info: { bgClass: 'bg-info', icon: 'ti-info-circle' }
        };

        const config = typeConfig[type] || typeConfig.info;
        
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white ${config.bgClass} border-0 fade-in`;
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    <i class="ti ${config.icon} me-2"></i>${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" onclick="this.parentElement.parentElement.remove()"></button>
            </div>
        `;
        
        return toast;
    }

    // Loading States
    setupLoadingStates() {
        // Add loading state to buttons
        document.addEventListener('click', (e) => {
            const btn = e.target.closest('button[type="submit"], .btn-loading');
            if (btn && btn.form) {
                this.setButtonLoading(btn, true);
                
                // Remove loading state if form submission fails
                setTimeout(() => {
                    this.setButtonLoading(btn, false);
                }, 10000);
            }
        });

        // Add loading state to forms
        document.addEventListener('submit', (e) => {
            const form = e.target;
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                this.setButtonLoading(submitBtn, true);
            }
        });
    }

    setButtonLoading(btn, loading) {
        if (loading) {
            btn.disabled = true;
            btn.dataset.originalHtml = btn.innerHTML;
            btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status"></span>Memproses...`;
            btn.classList.add('loading');
        } else {
            btn.disabled = false;
            btn.innerHTML = btn.dataset.originalHtml || btn.innerHTML;
            btn.classList.remove('loading');
        }
    }

    // Form Validation Enhancement
    setupFormValidation() {
        document.addEventListener('input', (e) => {
            if (e.target.matches('.form-control, .form-select')) {
                this.validateField(e.target);
            }
        });

        document.addEventListener('blur', (e) => {
            if (e.target.matches('.form-control, .form-select')) {
                this.validateField(e.target);
            }
        });
    }

    validateField(field) {
        const isValid = field.checkValidity();
        const feedbackElement = field.parentElement.querySelector('.invalid-feedback');
        
        field.classList.toggle('is-valid', isValid);
        field.classList.toggle('is-invalid', !isValid);
        
        if (!isValid && feedbackElement) {
            feedbackElement.textContent = field.validationMessage;
        }
    }

    // Enhanced File Uploads
    setupFileUploads() {
        document.querySelectorAll('.upload-area').forEach(area => {
            this.setupDragAndDrop(area);
        });

        // File input change handler
        document.addEventListener('change', (e) => {
            if (e.target.type === 'file') {
                this.handleFileSelect(e.target);
            }
        });
    }

    setupDragAndDrop(area) {
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            area.addEventListener(eventName, this.preventDefaults, false);
        });

        ['dragenter', 'dragover'].forEach(eventName => {
            area.addEventListener(eventName, () => area.classList.add('dragover'), false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            area.addEventListener(eventName, () => area.classList.remove('dragover'), false);
        });

        area.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            const files = dt.files;
            const input = area.querySelector('input[type="file"]');
            if (input) {
                input.files = files;
                this.handleFileSelect(input);
            }
        });
    }

    preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    handleFileSelect(input) {
        const file = input.files[0];
        if (!file) return;

        const uploadArea = input.closest('.upload-area');
        if (uploadArea) {
            const fileName = uploadArea.querySelector('.file-name') || this.createFileNameDisplay(uploadArea);
            fileName.textContent = file.name;
            
            // Show file size
            const fileSize = this.formatFileSize(file.size);
            const sizeDisplay = uploadArea.querySelector('.file-size') || this.createFileSizeDisplay(uploadArea);
            sizeDisplay.textContent = fileSize;
            
            uploadArea.classList.add('file-selected');
        }
    }

    createFileNameDisplay(container) {
        const nameEl = document.createElement('div');
        nameEl.className = 'file-name text-sm font-medium mt-2';
        container.appendChild(nameEl);
        return nameEl;
    }

    createFileSizeDisplay(container) {
        const sizeEl = document.createElement('div');
        sizeEl.className = 'file-size text-xs text-muted';
        container.appendChild(sizeEl);
        return sizeEl;
    }

    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    // Search and Filter Enhancement
    setupSearchAndFilters() {
        // Real-time search with debounce
        const searchInputs = document.querySelectorAll('[data-search-target]');
        searchInputs.forEach(input => {
            let debounceTimer;
            input.addEventListener('input', (e) => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    this.performSearch(e.target);
                }, 300);
            });
        });

        // Filter dropdowns
        const filterSelects = document.querySelectorAll('[data-filter-target]');
        filterSelects.forEach(select => {
            select.addEventListener('change', (e) => {
                this.applyFilter(e.target);
            });
        });
    }

    performSearch(input) {
        const target = input.dataset.searchTarget;
        const searchTerm = input.value.toLowerCase().trim();
        const targetElements = document.querySelectorAll(target);
        
        let visibleCount = 0;
        
        targetElements.forEach(element => {
            // Skip if already hidden by other filters
            const wasHiddenByFilter = element.dataset.hiddenByFilter === 'true';
            
            // Get searchable data attributes or text content
            const searchableFields = [
                element.dataset.searchSupplier,
                element.dataset.searchPart,
                element.dataset.searchModel,
                element.dataset.searchPartnumber,
                element.textContent
            ].filter(field => field).map(field => field.toLowerCase());
            
            // Check if any field matches search term
            const matches = searchTerm === '' || searchableFields.some(field => field.includes(searchTerm));
            
            // Handle visibility
            if (!matches) {
                element.style.display = 'none';
                element.dataset.hiddenBySearch = 'true';
            } else if (!wasHiddenByFilter) {
                element.style.display = '';
                element.dataset.hiddenBySearch = 'false';
                visibleCount++;
            }
        });
        
        // Update empty state if needed
        this.updateEmptyState(targetElements[0]?.closest('tbody'), visibleCount);
    }

    applyFilter(select) {
        const target = select.dataset.filterTarget;
        const filterValue = select.value;
        const targetElements = document.querySelectorAll(target);
        
        let visibleCount = 0;
        
        targetElements.forEach(element => {
            // Skip if already hidden by search
            const wasHiddenBySearch = element.dataset.hiddenBySearch === 'true';
            
            if (!filterValue) {
                // No filter - show if not hidden by search
                if (!wasHiddenBySearch) {
                    element.style.display = '';
                    visibleCount++;
                }
                element.dataset.hiddenByFilter = 'false';
                return;
            }
            
            // Get filter value from data attribute
            const elementValue = element.dataset.filterValue || element.dataset.status;
            const matches = elementValue === filterValue;
            
            if (!matches) {
                element.style.display = 'none';
                element.dataset.hiddenByFilter = 'true';
            } else if (!wasHiddenBySearch) {
                element.style.display = '';
                element.dataset.hiddenByFilter = 'false';
                visibleCount++;
            }
        });
        
        // Update empty state if needed
        this.updateEmptyState(targetElements[0]?.closest('tbody'), visibleCount);
    }

    updateEmptyState(tbody, visibleCount) {
        if (!tbody) return;
        
        let emptyRow = tbody.querySelector('.filter-empty-row');
        
        if (visibleCount === 0) {
            if (!emptyRow) {
                emptyRow = document.createElement('tr');
                emptyRow.className = 'filter-empty-row';
                emptyRow.innerHTML = `
                    <td colspan="100%" class="text-center py-5">
                        <div style="color: #94a3b8;">
                            <i class="ti ti-search-off" style="font-size: 3rem; opacity: 0.4;"></i>
                            <p class="mt-3 mb-0 fw-medium">Tidak ada data yang sesuai dengan filter</p>
                            <small class="text-muted">Coba ubah kata kunci pencarian atau reset filter</small>
                        </div>
                    </td>
                `;
                tbody.appendChild(emptyRow);
            }
            emptyRow.style.display = '';
        } else {
            if (emptyRow) {
                emptyRow.style.display = 'none';
            }
        }
    }

    // Animation Setup
    setupAnimations() {
        // Intersection Observer for scroll animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('fade-in');
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        // Observe elements with animation class
        document.querySelectorAll('.animate-on-scroll').forEach(el => {
            observer.observe(el);
        });
    }

    // Keyboard Navigation
    setupKeyboardNavigation() {
        // Escape key to close modals
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                const openModal = document.querySelector('.modal.show');
                if (openModal) {
                    const modal = bootstrap.Modal.getInstance(openModal);
                    if (modal) modal.hide();
                }
            }
        });

        // Enter key on search inputs
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && e.target.matches('[data-search-target]')) {
                e.preventDefault();
                this.performSearch(e.target);
            }
        });
    }

    // Utility Methods
    static formatNumber(num) {
        return new Intl.NumberFormat('id-ID').format(num);
    }

    static formatDate(date, options = {}) {
        return new Intl.DateTimeFormat('id-ID', {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
            ...options
        }).format(new Date(date));
    }

    static formatCurrency(amount, currency = 'IDR') {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: currency
        }).format(amount);
    }
}

// Initialize the app when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.akebonoApp = new AkebonoApp();
    
    // Make utilities available globally
    window.formatNumber = AkebonoApp.formatNumber;
    window.formatDate = AkebonoApp.formatDate;
    window.formatCurrency = AkebonoApp.formatCurrency;
});

// Global error handler
window.addEventListener('error', (e) => {
    console.error('Application error:', e.error);
    
    // Skip error toast for specific benign cases
    if (e.message && (
        e.message.includes('ResizeObserver') || 
        e.message.includes('Bootstrap') ||
        e.message.includes('Dropdown') ||
        e.filename && e.filename.includes('bootstrap')
    )) {
        return; // Ignore benign errors
    }
    
    // Only show toast for critical errors with stack trace
    if (window.showToast && e.error && e.error.stack) {
        window.showToast('error', 'Terjadi kesalahan. Silakan refresh halaman.');
    }
});

// Service Worker registration for PWA features (optional)
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        // navigator.serviceWorker.register('/sw.js')
        //     .then(registration => console.log('SW registered'))
        //     .catch(error => console.log('SW registration failed'));
    });
}
