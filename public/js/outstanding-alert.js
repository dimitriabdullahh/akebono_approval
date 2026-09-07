/**
 * Outstanding Documents Alert Box
 * Handles AJAX calls, sorting, and interactions for outstanding PPAP documents
 */

class OutstandingAlert {
    constructor(token) {
        this.token = token;
        this.currentSort = 'due_date';
        this.currentDirection = 'asc';
        this.data = [];
        this.statistics = {};
        
        this.init();
    }

    init() {
        this.bindEvents();
        this.loadOutstandingDocuments();
    }

    bindEvents() {
        // Sort buttons
        document.querySelectorAll('[data-sort]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                this.handleSortChange(e.target.closest('[data-sort]').dataset.sort);
            });
        });

        // Toggle sort direction
        const toggleBtn = document.getElementById('toggle-sort-direction');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                this.toggleSortDirection();
            });
        }

        // Collapse/Expand alert body
        const collapseBtn = document.getElementById('collapse-alert-btn');
        if (collapseBtn) {
            collapseBtn.addEventListener('click', () => {
                this.toggleCollapse();
            });
        }
    }

    toggleCollapse() {
        const body = document.getElementById('alert-body-collapsible');
        const icon = document.getElementById('collapse-icon');
        
        if (body.style.display === 'none') {
            body.style.display = 'block';
            icon.className = 'ti ti-chevron-up';
        } else {
            body.style.display = 'none';
            icon.className = 'ti ti-chevron-down';
        }
    }

    async loadOutstandingDocuments() {
        try {
            const url = `/supplier/${this.token}/api/outstanding-documents?sort_by=${this.currentSort}&sort_direction=${this.currentDirection}`;
            
            console.log('Loading outstanding documents from:', url);
            
            const response = await fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });
            
            console.log('Response status:', response.status);
            
            if (!response.ok) {
                const errorText = await response.text();
                console.error('Error response:', errorText);
                throw new Error(`HTTP ${response.status}: ${errorText}`);
            }

            const result = await response.json();
            
            console.log('Outstanding documents loaded:', result);
            
            if (result.success) {
                this.data = result.data || [];
                this.statistics = result.statistics || {};
                
                // Update supplier info
                if (result.supplier) {
                    const supplierNameEl = document.getElementById('supplier-name');
                    if (supplierNameEl) {
                        supplierNameEl.textContent = result.supplier.name;
                    }
                }

                // Show or hide alert container
                const container = document.getElementById('outstanding-alert-container');
                if (this.data.length > 0) {
                    container.style.display = 'block';
                    this.renderTable();
                    this.updateStatistics();
                } else {
                    container.style.display = 'none';
                }
            } else {
                throw new Error(result.message || 'Failed to load outstanding documents');
            }
        } catch (error) {
            console.error('Error loading outstanding documents:', error);
            this.showError('Gagal memuat data outstanding documents: ' + error.message);
            
            // Hide container on error
            const container = document.getElementById('outstanding-alert-container');
            if (container) {
                container.style.display = 'none';
            }
        }
    }

    renderTable() {
        const tbody = document.getElementById('outstanding-table-body');
        
        if (this.data.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center py-4 text-muted">
                        <i class="ti ti-check-circle" style="font-size: 2rem;"></i>
                        <p class="mb-0 mt-2">Tidak ada outstanding documents</p>
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = this.data.map((item, index) => this.renderRow(item, index + 1)).join('');
    }

    renderRow(item, index) {
        const isOverdue = item.is_overdue;
        const daysRemaining = item.days_remaining;
        
        let dueDateInfo = '';
        if (isOverdue) {
            dueDateInfo = '<small class="badge bg-danger">Overdue</small>';
        } else if (daysRemaining > 0) {
            dueDateInfo = `<small class="text-success">${daysRemaining} hari lagi</small>`;
        } else if (daysRemaining === 0) {
            dueDateInfo = '<small class="text-warning">Jatuh tempo hari ini</small>';
        } else {
            dueDateInfo = '<small class="text-muted">Sudah terlewat</small>';
        }

        let statusInfo = '';
        if (item.revision_documents > 0) {
            statusInfo = `<br><small class="text-muted">${item.revision_documents} doc perlu revisi</small>`;
        } else if (item.submitted_documents < item.total_documents) {
            statusInfo = `<br><small class="text-muted">${item.submitted_documents}/${item.total_documents} submitted</small>`;
        }

        return `
            <tr class="outstanding-row ${isOverdue ? 'row-overdue' : ''}" data-request-id="${item.id}">
                <td class="text-center">${index}</td>
                <td>
                    <div class="d-flex flex-column">
                        <span class="fw-medium">${item.request_date_formatted}</span>
                        <small class="text-muted">${item.model}</small>
                    </div>
                </td>
                <td>
                    <span class="fw-medium">${item.model}</span>
                    <br>
                    <small class="text-muted">${item.part_number}</small>
                </td>
                <td>
                    <div class="d-flex flex-column">
                        <span class="fw-medium ${isOverdue ? 'text-danger' : ''}">${item.due_date_formatted}</span>
                        ${dueDateInfo}
                    </div>
                </td>
                <td class="text-center">
                    ${item.status === 'open' 
                        ? '<span class="badge badge-status bg-warning text-dark">Open</span>'
                        : '<span class="badge badge-status bg-danger">Reject</span>'
                    }
                    ${statusInfo}
                </td>
                <td class="text-center">
                    <a href="${item.portal_url}" class="btn btn-primary btn-sm" title="Upload Dokumen">
                        <i class="ti ti-upload"></i> Upload Dokumen
                    </a>
                </td>
            </tr>
        `;
    }

    updateStatistics() {
        document.getElementById('outstanding-count').textContent = this.statistics.total || 0;
        document.getElementById('stat-total').textContent = this.statistics.total || 0;
        document.getElementById('stat-open').textContent = this.statistics.open || 0;
        document.getElementById('stat-reject').textContent = this.statistics.reject || 0;
        document.getElementById('stat-overdue').textContent = this.statistics.overdue || 0;
    }

    handleSortChange(sortBy) {
        // Update active button
        document.querySelectorAll('[data-sort]').forEach(btn => {
            btn.classList.remove('active');
        });
        document.querySelector(`[data-sort="${sortBy}"]`).classList.add('active');

        // Update current sort and reload
        this.currentSort = sortBy;
        this.loadOutstandingDocuments();
    }

    toggleSortDirection() {
        this.currentDirection = this.currentDirection === 'asc' ? 'desc' : 'asc';
        
        // Update UI
        const icon = document.getElementById('sort-icon');
        const text = document.getElementById('sort-direction-text');
        
        if (this.currentDirection === 'asc') {
            icon.className = 'ti ti-sort-ascending';
            text.textContent = 'Ascending';
        } else {
            icon.className = 'ti ti-sort-descending';
            text.textContent = 'Descending';
        }

        this.loadOutstandingDocuments();
    }

    getCsrfToken() {
        const token = document.querySelector('meta[name="csrf-token"]');
        return token ? token.getAttribute('content') : '';
    }

    showSuccess(message) {
        this.showToast(message, 'success');
    }

    showError(message) {
        this.showToast(message, 'danger');
    }

    showToast(message, type = 'info') {
        // Create toast notification
        const toast = document.createElement('div');
        toast.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        toast.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(toast);

        // Auto remove after 5 seconds
        setTimeout(() => {
            toast.remove();
        }, 5000);
    }
}

// Initialize when document is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing outstanding alert...');
    console.log('Current URL:', window.location.pathname);
    
    // Get token from URL or data attribute
    const tokenMatch = window.location.pathname.match(/supplier\/([^\/]+)/);
    
    if (tokenMatch && tokenMatch[1]) {
        const token = tokenMatch[1];
        console.log('Token found:', token);
        
        window.outstandingAlert = new OutstandingAlert(token);
    } else {
        console.error('No token found in URL:', window.location.pathname);
    }
});
