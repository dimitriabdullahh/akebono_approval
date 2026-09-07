

<?php $__env->startSection('title', 'Dashboard - Monitoring Approval'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4 animate-on-scroll">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 mb-2">
                        <i class="ti ti-dashboard me-2 text-accent-color"></i>Dashboard Monitoring Approval
                    </h1>
                    <p class="text-muted">Pantau progres approval dokumen supplier secara real-time</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Daftar Permintaan Dokumen Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div class="d-flex align-items-center">
                            <i class="ti ti-list-details me-2 text-primary"></i>
                            <h5 class="mb-0 font-semibold">Daftar Permintaan Dokumen</h5>
                        </div>
                        <?php if(auth()->user()->canCreateRequest()): ?>
                        <a href="<?php echo e(route('requests.create')); ?>" class="btn btn-primary">
                            <i class="ti ti-plus me-2"></i>Buat Permintaan Baru
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Search & Filter Section -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <input type="text" id="searchInput" class="form-control" placeholder="Cari berdasarkan Model Part, Supplier...">
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="statusFilter">
                                <option value="">Semua Status</option>
                                <option value="pending">Menunggu Upload</option>
                                <option value="in_progress">Sedang Berjalan</option>
                                <option value="completed">Selesai</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-outline-secondary w-100" onclick="resetFilters()">
                                <i class="ti ti-rotate-clockwise me-2"></i>Reset
                            </button>
                        </div>
                    </div>

                    <!-- Requests Table -->
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead style="background-color: #F8FAFC; border-bottom: 2px solid #e2e8f0;">
                                <tr>
                                    <th class="ps-4">Model Part</th>
                                    <th>Supplier</th>
                                    <th>Tanggal Due</th>
                                    <th>Status Dokumen</th>
                                    <th>Progres</th>
                                    <th>Dibuat</th>
                                    <th class="text-end pe-4">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $requests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $request): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <?php
                                        $totalDocs = $request->requestDocuments->count();
                                        $approvedDocs = $request->requestDocuments->where('status', 'APPROVED')->count();
                                        $submitedDocs = $request->requestDocuments->where('status', '!=', 'PENDING')->count();
                                        $progressPercent = $totalDocs > 0 ? round(($approvedDocs / $totalDocs) * 100) : 0;
                                        $statusLabel = $progressPercent === 100 ? 'Selesai' : ($submitedDocs === 0 ? 'Menunggu Upload' : 'Sedang Berjalan');
                                        $statusColor = $progressPercent === 100 ? 'success' : ($submitedDocs === 0 ? 'secondary' : 'info');
                                        $statusSlug = $progressPercent === 100 ? 'completed' : ($submitedDocs === 0 ? 'pending' : 'in_progress');
                                    ?>
                                    <?php
                                        // Collect all part names and numbers for search
                                        $allPartNames = $request->requestParts->pluck('part_name')->implode(' ');
                                        $allPartNumbers = $request->requestParts->pluck('part_number')->implode(' ');
                                    ?>
                                    <tr style="border-bottom: 1px solid #e2e8f0;" 
                                        data-search-model="<?php echo e(strtolower($request->model)); ?>"
                                        data-search-partnumber="<?php echo e(strtolower($allPartNumbers)); ?>"
                                        data-search-partname="<?php echo e(strtolower($allPartNames)); ?>"
                                        data-search-supplier="<?php echo e(strtolower($request->supplier->name)); ?>"
                                        data-status="<?php echo e($statusSlug); ?>">
                                        <td class="ps-4">
                                            <div class="fw-600" style="color: var(--primary-color);"><?php echo e($request->model); ?></div>
                                            <?php if($request->requestParts && $request->requestParts->count() > 0): ?>
                                                <div class="mt-1">
                                                    <?php $__currentLoopData = $request->requestParts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $part): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <small class="d-block text-muted">
                                                            <i class="ti ti-box" style="font-size: 0.75rem;"></i>
                                                            <?php echo e($part->part_name); ?> (<?php echo e($part->part_number); ?>)
                                                        </small>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </div>
                                            <?php else: ?>
                                                <small class="text-muted"><?php echo e($request->part_number); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="fw-500"><?php echo e($request->supplier->name); ?></div>
                                            <small class="text-muted"><?php echo e($request->supplier->pic_email); ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo e($request->due_date < now() ? 'danger' : 'light'); ?> text-<?php echo e($request->due_date < now() ? 'white' : 'dark'); ?>">
                                                <?php echo e($request->due_date->format('d M Y')); ?>

                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo e($statusColor); ?>">
                                                <?php echo e($statusLabel); ?>

                                            </span>
                                        </td>
                                        <td>
                                            <div class="progress" style="height: 20px; width: 120px;">
                                                <div class="progress-bar bg-<?php echo e($progressPercent >= 100 ? 'success' : 'info'); ?>" 
                                                     role="progressbar" 
                                                     style="width: <?php echo e($progressPercent); ?>%"
                                                     aria-valuenow="<?php echo e($progressPercent); ?>" 
                                                     aria-valuemin="0" 
                                                     aria-valuemax="100">
                                                    <small class="fw-600"><?php echo e($approvedDocs); ?>/<?php echo e($totalDocs); ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <small class="text-muted"><?php echo e($request->created_at->format('d M Y')); ?></small>
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="btn-group" role="group">
                                                <a href="<?php echo e(route('requests.show', $request)); ?>" class="btn btn-sm btn-outline-primary" title="Lihat Detail">
                                                    <i class="ti ti-eye"></i>
                                                </a>
                                                
                                                <?php if(auth()->user()->isAdminQA() && $submitedDocs > 0): ?>
                                                    <?php
                                                        $qaReviewableDocs = $request->requestDocuments
                                                            ->where('status', 'SUBMITTED')
                                                            ->filter(function($doc) {
                                                                $scopes = $doc->documentType->scope ?? [];
                                                                return in_array('QA', $scopes);
                                                            });
                                                    ?>
                                                    
                                                    <?php if($qaReviewableDocs->count() > 0): ?>
                                                        <a href="<?php echo e(route('requests.review', $request)); ?>" 
                                                           class="btn btn-sm btn-success" 
                                                           title="Review & Approve <?php echo e($qaReviewableDocs->count()); ?> dokumen QA yang menunggu"
                                                           data-bs-toggle="tooltip" 
                                                           data-bs-placement="top">
                                                            <i class="ti ti-circle-check"></i>
                                                            <span class="badge bg-light text-dark ms-1"><?php echo e($qaReviewableDocs->count()); ?></span>
                                                        </a>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                                
                                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="resendEmail(<?php echo e($request->id); ?>)" title="Kirim Ulang Email">
                                                    <i class="ti ti-mail"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <div style="color: #cbd5e1;">
                                                <i class="ti ti-inbox" style="font-size: 3rem; opacity: 0.5;"></i>
                                                <p class="mt-3 mb-0">Belum ada permintaan dokumen</p>
                                                <small>Buat permintaan baru dengan mengklik tombol di atas</small>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if($requests->hasPages()): ?>
                        <div class="mt-3">
                            <?php echo e($requests->links()); ?>

                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Resend Email Modal -->
    <div class="modal fade" id="resendEmailModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Konfirmasi Pengiriman Ulang Email</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin mengirim ulang email surat request ke supplier?</p>
                    <p class="text-muted small mb-0">Email akan dikirim ke alamat email PIC supplier yang terdaftar.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="confirmResendBtn" onclick="confirmResendEmail()">
                        <i class="ti ti-mail me-2"></i>Kirim Ulang
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.hover-lift {
    transition: all 0.3s ease;
}

.hover-lift:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

.min-width-0 {
    min-width: 0;
}

.flex-shrink-0 {
    flex-shrink: 0;
}

.text-gray-800 {
    color: #1f2937 !important;
}

.text-gray-600 {
    color: #4b5563 !important;
}

.text-gray-700 {
    color: #374151 !important;
}
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
// Dashboard System
document.addEventListener('DOMContentLoaded', function() {
    // Add smooth scrolling to anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });

    // Auto-refresh data every 5 minutes
    setInterval(() => {
        if (document.hidden) return; // Don't refresh if tab is not active
        
        const refreshBtn = document.querySelector('button[onclick="location.reload()"]');
        if (refreshBtn) {
            refreshBtn.innerHTML = '<i class="ti ti-refresh"></i> <span class="d-none d-sm-inline ms-2">Refreshing...</span>';
            setTimeout(() => {
                location.reload();
            }, 1000);
        }
    }, 5 * 60 * 1000); // 5 minutes

    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Setup search & filter event listeners
    document.getElementById('searchInput')?.addEventListener('input', function() {
        filterTable();
    });

    document.getElementById('statusFilter')?.addEventListener('change', function() {
        filterTable();
    });
    
    // Initialize filter on page load
    filterTable();
});

// Resend Email Functionality
let currentRequestId = null;
const resendModal = new bootstrap.Modal(document.getElementById('resendEmailModal'));

function resendEmail(requestId) {
    currentRequestId = requestId;
    resendModal.show();
}

function confirmResendEmail() {
    if (!currentRequestId) return;

    fetch(`/admin/requests/${currentRequestId}/resend-email`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        resendModal.hide();
        
        // Show success toast
        showToastNotification('success', 'Email berhasil dikirim ulang ke supplier!');
        
        // Optional: Reload table or update specific row
        setTimeout(() => location.reload(), 1500);
    })
    .catch(error => {
        resendModal.hide();
        showToastNotification('error', 'Gagal mengirim email. Silakan coba lagi.');
        console.error('Error:', error);
    });
}

// Helper function for word-based search matching - STRICT MODE
function matchesSearchTerm(searchValue, text) {
    if (!searchValue) return true;
    if (!text) return false;
    
    // Split search into words
    const searchTerms = searchValue.split(/\s+/).filter(t => t.length > 0);
    
    // STRICT: For each search term, MUST match as a whole word (word boundary)
    return searchTerms.every(term => {
        // Use word boundary - must be a complete word
        const wordRegex = new RegExp(`\\b${term}\\b`, 'i');
        return wordRegex.test(text);
    });
}

function filterTable() {
    const searchValue = document.getElementById('searchInput')?.value.toLowerCase().trim() || '';
    const statusValue = document.getElementById('statusFilter')?.value || ''; // Don't lowercase - options are already lowercase
    const rows = document.querySelectorAll('tbody tr');
    
    let visibleCount = 0;

    rows.forEach(row => {
        // Skip empty state row
        if (row.textContent.includes('Belum ada permintaan dokumen')) {
            row.style.display = 'none';
            return;
        }

        // Get data attributes for accurate filtering
        const searchModel = row.dataset.searchModel || '';
        const searchPartNumber = row.dataset.searchPartnumber || '';
        const searchPartName = row.dataset.searchPartname || '';
        const searchSupplier = row.dataset.searchSupplier || '';
        const rowStatus = row.dataset.status || '';

        // Search matching - check all searchable fields with word-based matching
        const matchesSearch = searchValue === '' || 
            matchesSearchTerm(searchValue, searchModel) || 
            matchesSearchTerm(searchValue, searchPartNumber) || 
            matchesSearchTerm(searchValue, searchPartName) || 
            matchesSearchTerm(searchValue, searchSupplier);

        // Status matching - strict equality check
        const matchesStatus = statusValue === '' || rowStatus === statusValue;

        // Show/hide row - BOTH conditions must match
        const shouldShow = matchesSearch && matchesStatus;
        row.style.display = shouldShow ? '' : 'none';
        
        if (shouldShow) visibleCount++;
    });

    // Show/hide empty state
    updateEmptyState(visibleCount);
}

function updateEmptyState(visibleCount) {
    const tbody = document.querySelector('tbody');
    let emptyRow = tbody.querySelector('.empty-state-row');
    
    if (visibleCount === 0) {
        if (!emptyRow) {
            emptyRow = document.createElement('tr');
            emptyRow.className = 'empty-state-row';
            emptyRow.innerHTML = `
                <td colspan="7" class="text-center py-5">
                    <div style="color: #cbd5e1;">
                        <i class="ti ti-search-off" style="font-size: 3rem; opacity: 0.5;"></i>
                        <p class="mt-3 mb-0">Tidak ada data yang sesuai dengan filter</p>
                        <small>Coba ubah kata kunci pencarian atau reset filter</small>
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

function resetFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('statusFilter').value = '';
    filterTable();
}

// Toast Notification
function showToastNotification(type, message) {
    const toastHtml = `
        <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="ti ti-${type === 'success' ? 'check' : 'alert-circle'} me-2"></i>${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;
    
    const toastContainer = document.querySelector('.toast-container') || (() => {
        const container = document.createElement('div');
        container.className = 'toast-container position-fixed top-0 end-0 p-3';
        document.body.appendChild(container);
        return container;
    })();

    const toastElement = document.createElement('div');
    toastElement.innerHTML = toastHtml;
    toastContainer.appendChild(toastElement.firstElementChild);

    const toast = new bootstrap.Toast(toastContainer.querySelector('.toast'));
    toast.show();
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\KERJAAN\akebono-approval\resources\views/admin/dashboard/index.blade.php ENDPATH**/ ?>