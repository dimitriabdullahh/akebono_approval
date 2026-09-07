

<?php $__env->startSection('title', 'Review Dokumen - ' . $request->model); ?>

<?php $__env->startPush('styles'); ?>
<style>
/* SweetAlert2 Custom Styling for Perfect Centering */
.swal2-container {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    padding: 1rem;
    background: rgba(0, 0, 0, 0.5) !important;
    backdrop-filter: blur(3px);
}

.swal2-popup {
    border-radius: 1.5rem !important;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25) !important;
    border: none !important;
    font-family: 'Inter', 'Plus Jakarta Sans', 'Segoe UI', 'Roboto', sans-serif !important;
    max-width: 28rem !important;
    margin: 0 !important;
    position: relative !important;
    transform: none !important;
    animation: swalSlideIn 0.3s ease-out !important;
}

@keyframes swalSlideIn {
    from {
        opacity: 0;
        transform: translateY(-1rem) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.swal2-title {
    font-size: 1.25rem !important;
    font-weight: 600 !important;
    color: #374151 !important;
    margin-bottom: 1rem !important;
}

.swal2-html-container {
    font-size: 0.875rem !important;
    line-height: 1.5 !important;
    color: #6b7280 !important;
    margin-bottom: 1.5rem !important;
}

.swal2-actions {
    gap: 0.75rem !important;
    margin-top: 1.5rem !important;
}

.swal2-confirm {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
    border: none !important;
    border-radius: 0.5rem !important;
    font-weight: 500 !important;
    padding: 0.75rem 1.5rem !important;
    font-size: 0.875rem !important;
    transition: all 0.2s ease !important;
}

.swal2-confirm:hover {
    background: linear-gradient(135deg, #059669 0%, #047857 100%) !important;
    transform: translateY(-1px) !important;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1) !important;
}

.swal2-cancel {
    background: #f3f4f6 !important;
    color: #374151 !important;
    border: 1px solid #d1d5db !important;
    border-radius: 0.5rem !important;
    font-weight: 500 !important;
    padding: 0.75rem 1.5rem !important;
    font-size: 0.875rem !important;
    transition: all 0.2s ease !important;
}

.swal2-cancel:hover {
    background: #e5e7eb !important;
    border-color: #9ca3af !important;
    transform: translateY(-1px) !important;
}

.swal2-icon {
    margin-bottom: 1rem !important;
}

.swal2-icon.swal2-question {
    border-color: #2563eb !important;
    color: #2563eb !important;
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="<?php echo e(route('requests.index')); ?>" class="text-decoration-none">Daftar Request</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="<?php echo e(route('requests.show', $request)); ?>" class="text-decoration-none"><?php echo e($request->model); ?></a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Review Dokumen</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0" style="color: var(--primary-color); font-weight: 600;">
                <i class="ti ti-clipboard-check me-2"></i>Review & Approve Dokumen
                <span class="badge bg-warning text-dark ms-2"><?php echo e($submittedDocuments->count()); ?> dokumen menunggu</span>
            </h1>
            <p class="text-muted small mt-1">Review dokumen yang telah dikirim supplier untuk persetujuan</p>
        </div>
        <a href="<?php echo e(route('requests.show', $request)); ?>" class="btn btn-outline-secondary">
            <i class="ti ti-arrow-left me-2"></i>Kembali ke Detail
        </a>
    </div>

    <!-- Request Info Card -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5 style="color: var(--primary-color);">Informasi Request</h5>
                    <table class="table table-borderless">
                        <tr>
                            <td width="120"><strong>Model:</strong></td>
                            <td><?php echo e($request->model); ?></td>
                        </tr>
                        <?php if($request->requestParts && $request->requestParts->count() > 0): ?>
                        <tr>
                            <td valign="top"><strong>Parts:</strong></td>
                            <td>
                                <?php $__currentLoopData = $request->requestParts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $part): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="mb-1">
                                        <span class="badge bg-light text-dark">
                                            <?php echo e($part->part_name); ?> (<?php echo e($part->part_number); ?>)
                                        </span>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </td>
                        </tr>
                        <?php else: ?>
                        <tr>
                            <td><strong>Part Name:</strong></td>
                            <td><?php echo e($request->part_name); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Part Number:</strong></td>
                            <td><?php echo e($request->part_number); ?></td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </div>
                <div class="col-md-6">
                    <h5 style="color: var(--primary-color);">Supplier & Timeline</h5>
                    <table class="table table-borderless">
                        <tr>
                            <td width="120"><strong>Supplier:</strong></td>
                            <td><?php echo e($request->supplier->name); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Due Date:</strong></td>
                            <td>
                                <span class="badge bg-<?php echo e($request->due_date < now() ? 'danger' : 'light'); ?> text-<?php echo e($request->due_date < now() ? 'white' : 'dark'); ?>">
                                    <?php echo e($request->due_date->format('d M Y')); ?>

                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Status:</strong></td>
                            <td>
                                <?php
                                    $totalDocs = $request->requestDocuments->count();
                                    $approvedDocs = $request->requestDocuments->where('status', 'APPROVED')->count();
                                    $progressPercent = $totalDocs > 0 ? round(($approvedDocs / $totalDocs) * 100) : 0;
                                ?>
                                <span class="badge bg-info"><?php echo e($approvedDocs); ?>/<?php echo e($totalDocs); ?> Dokumen Disetujui</span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Documents to Review -->
    <div class="row">
        <?php $__currentLoopData = $submittedDocuments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $document): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="col-lg-6 mb-4">
                <div class="card h-100 border-left-4" style="border-left: 4px solid var(--accent-color);">
                    <div class="card-header bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0" style="color: var(--primary-color);">
                                <?php echo e($document->documentType->name); ?>

                                <?php if($document->documentType->is_mandatory): ?>
                                    <span style="color: #DC2626; font-size: 1.1em; font-weight: bold;">*</span>
                                <?php endif; ?>
                            </h5>
                            <span class="badge bg-warning text-dark">MENUNGGU REVIEW</span>
                        </div>
                        <?php if($document->documentType->is_mandatory): ?>
                            <small class="text-danger">
                                <i class="ti ti-alert-triangle me-1"></i>Dokumen Wajib
                            </small>
                        <?php endif; ?>
                    </div>
                    
                    <div class="card-body">
                        <!-- File Info -->
                        <div class="mb-3 p-3 rounded" style="background-color: #f8f9fa;">
                            <div class="d-flex align-items-center">
                                <i class="ti ti-file-description text-primary me-2" style="font-size: 1.5rem;"></i>
                                <div>
                                    <div class="fw-600"><?php echo e($document->original_filename ?? 'Document.pdf'); ?></div>
                                    <small class="text-muted">
                                        Diunggah: <?php echo e($document->uploaded_at?->format('d M Y, H:i')); ?>

                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Document Type Info -->
                        <?php if($document->documentType->description): ?>
                            <div class="mb-3">
                                <small class="text-muted">
                                    <strong>Deskripsi:</strong> <?php echo e($document->documentType->description); ?>

                                </small>
                            </div>
                        <?php endif; ?>

                        <!-- Scope Info for QA -->
                        <div class="mb-3">
                            <small class="text-muted">
                                <strong>Scope Review:</strong> 
                                <?php
                                    $scopes = $document->documentType->scope ?? [];
                                ?>
                                <?php if(in_array('QA', $scopes)): ?>
                                    <span class="badge bg-success text-white">QA</span>
                                <?php endif; ?>
                                <?php if(in_array('PURCHASING', $scopes)): ?>
                                    <span class="badge bg-info text-white">PURCHASING</span>
                                <?php endif; ?>
                                <?php if(in_array('PPC', $scopes)): ?>
                                    <span class="badge bg-warning text-dark">PPC</span>
                                <?php endif; ?>
                            </small>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-grid gap-2">
                            <div class="row g-2">
                                <div class="col-6">
                                    <a href="<?php echo e(route('review.show', $document)); ?>" 
                                       class="btn btn-primary w-100">
                                        <i class="ti ti-eye me-2"></i>Review Detail
                                    </a>
                                </div>
                                <div class="col-6">
                                    <button type="button" 
                                            class="btn btn-success w-100" 
                                            onclick="showApproveConfirmation(<?php echo e($document->id); ?>, '<?php echo e($document->documentType->name); ?>')"
                                            data-document-id="<?php echo e($document->id); ?>">
                                        <i class="ti ti-check me-2"></i>Approve
                                    </button>
                                </div>
                            </div>
                            
                            <button type="button" 
                                    class="btn btn-outline-warning w-100" 
                                    onclick="openRejectModal(<?php echo e($document->id); ?>, '<?php echo e($document->documentType->name); ?>')">
                                <i class="ti ti-x me-2"></i>Request Revisi
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <?php if($submittedDocuments->count() === 0): ?>
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="ti ti-circle-check" style="font-size: 3rem; opacity: 0.5; color: var(--success-color);"></i>
                <h4 class="mt-3 mb-0">Tidak ada dokumen menunggu review QA</h4>
                <p class="text-muted">Semua dokumen sudah direview atau belum ada yang disubmit untuk scope QA</p>
                <a href="<?php echo e(route('requests.show', $request)); ?>" class="btn btn-primary mt-2">
                    <i class="ti ti-arrow-left me-2"></i>Kembali ke Detail Request
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Request Revisi Dokumen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="rejectForm" method="POST">
                <?php echo csrf_field(); ?>
                <div class="modal-body">
                    <p>Anda akan meminta revisi untuk dokumen: <strong id="documentName"></strong></p>
                    
                    <div class="mb-3">
                        <label for="revision_note" class="form-label">Catatan Revisi <span class="text-danger">*</span></label>
                        <textarea class="form-control" 
                                  id="revision_note" 
                                  name="revision_note" 
                                  rows="4" 
                                  placeholder="Jelaskan alasan revisi dan perbaikan yang dibutuhkan..." 
                                  required></textarea>
                        <small class="text-muted">Catatan ini akan dikirim ke supplier untuk panduan revisi</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="ti ti-send me-2"></i>Kirim Permintaan Revisi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Approve Confirmation Modal -->
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 1.5rem; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); border: none;">
            <div class="modal-header border-0 pb-0">
                <div class="text-center w-100">
                    <div class="mb-3">
                        <i class="ti ti-help-circle text-primary" style="font-size: 3rem;"></i>
                    </div>
                    <h5 class="modal-title fw-600" style="color: #374151;">Konfirmasi Persetujuan</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="approveForm" method="POST">
                <?php echo csrf_field(); ?>
                <div class="modal-body text-center pt-0">
                    <p class="mb-1">Apakah Anda yakin ingin menyetujui dokumen</p>
                    <p class="fw-600 mb-3" id="approveDocumentName" style="color: #374151;"></p>
                    <small class="text-muted">Setelah disetujui, dokumen tidak dapat diubah lagi.</small>
                </div>
                <div class="modal-footer border-0 pt-0 justify-content-center gap-3">
                    <button type="button" class="btn px-4" data-bs-dismiss="modal" 
                            style="background: #f3f4f6; color: #374151; border: 1px solid #d1d5db; border-radius: 0.5rem; font-weight: 500;">
                        Batal
                    </button>
                    <button type="submit" class="btn px-4 text-white" 
                            style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border: none; border-radius: 0.5rem; font-weight: 500;">
                        <i class="ti ti-check me-2"></i>Ya, Setujui
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const rejectModal = new bootstrap.Modal(document.getElementById('rejectModal'));
const approveModal = new bootstrap.Modal(document.getElementById('approveModal'));

function openRejectModal(documentId, documentName) {
    document.getElementById('documentName').textContent = documentName;
    document.getElementById('rejectForm').action = `/admin/review/${documentId}/reject`;
    document.getElementById('revision_note').value = '';
    rejectModal.show();
}

function showApproveConfirmation(documentId, documentName) {
    document.getElementById('approveDocumentName').textContent = `"${documentName}"`;
    document.getElementById('approveForm').action = `/admin/review/${documentId}/approve`;
    approveModal.show();
}

async function confirmApprove(documentName) {
    const result = await Swal.fire({
        title: 'Konfirmasi Persetujuan',
        html: `Apakah Anda yakin ingin menyetujui dokumen <strong>"${documentName}"</strong>?<br><br><small class="text-muted">Setelah disetujui, dokumen tidak dapat diubah lagi.</small>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Ya, Setujui',
        cancelButtonText: 'Batal',
        customClass: {
            container: 'sweet-alert-center'
        },
        backdrop: true,
        allowOutsideClick: false
    });
    
    return result.isConfirmed;
}

// Toast notification for success/error messages
<?php if(session('success')): ?>
    showToast('success', '<?php echo e(session('success')); ?>');
<?php endif; ?>

<?php if(session('error')): ?>
    showToast('error', '<?php echo e(session('error')); ?>');
<?php endif; ?>

function showToast(type, message) {
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

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\KERJAAN\akebono-approval\resources\views/admin/requests/review.blade.php ENDPATH**/ ?>