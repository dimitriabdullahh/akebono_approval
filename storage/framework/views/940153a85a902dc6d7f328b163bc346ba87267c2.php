

<?php $__env->startSection('title', 'Review Dokumen'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <!-- Header Navigation -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="h4 mb-1" style="color: var(--primary-color); font-weight: 600;">
                        <i class="ti ti-clipboard-check me-2"></i><?php echo e($requestDocument->documentType->name); ?>

                        <?php if($requestDocument->documentType->is_mandatory): ?>
                            <span style="color: #DC2626; font-size: 1.1em; font-weight: bold;">*</span>
                        <?php endif; ?>
                    </h2>
                    <p class="text-muted small mb-0">
                        <i class="ti ti-building me-1"></i><?php echo e($requestDocument->documentRequest->supplier->name); ?> • 
                        <i class="ti ti-package me-1"></i><?php echo e($requestDocument->documentRequest->model); ?>

                    </p>
                </div>
                <a href="<?php echo e(route('review.index')); ?>" class="btn btn-outline-secondary">
                    <i class="ti ti-arrow-left me-2"></i>Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- Split View: Document Preview (Left) & Actions (Right) -->
    <div class="row g-4" style="min-height: calc(100vh - 200px);">
        <!-- Left: Document Viewer -->
        <div class="col-lg-8">
            <div class="card h-100" style="display: flex; flex-direction: column;">
                <!-- Toolbar -->
                <div class="card-header" style="background-color: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Pratinjau Dokumen</h6>
                        </div>
                        <div class="d-flex gap-2 align-items-center">
                            <?php
                                $isPdf = false;
                                // Check for form-based documents
                                if ($packagingStandard || $partSubmissionWarrant) {
                                    $isPdf = false; // Form-based docs don't use PDF viewer
                                } elseif ($inspectionStandard && $inspectionStandard->pdf_file_path) {
                                    $isPdf = str_ends_with($inspectionStandard->pdf_file_path, '.pdf');
                                } elseif ($requestDocument->file_path && $requestDocument->file_path !== 'form') {
                                    $isPdf = str_ends_with($requestDocument->file_path, '.pdf');
                                }
                            ?>
                            <?php if($isPdf): ?>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="prevPageBtn" title="Halaman Sebelumnya">
                                        <i class="ti ti-chevron-left"></i>
                                    </button>
                                    <span class="btn btn-sm btn-outline-secondary disabled" style="pointer-events: none;">
                                        Hal <span id="pageNum">1</span> / <span id="pageCount">0</span>
                                    </span>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="nextPageBtn" title="Halaman Selanjutnya">
                                        <i class="ti ti-chevron-right"></i>
                                    </button>
                                </div>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="zoomInBtn" title="Perbesar">
                                        <i class="ti ti-zoom-in"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="zoomOutBtn" title="Perkecil">
                                        <i class="ti ti-zoom-out"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="rotateBtn" title="Putar 90°">
                                        <i class="ti ti-rotate-2"></i>
                                    </button>
                                </div>
                            <?php endif; ?>
                            <?php if($inspectionStandard && $inspectionStandard->pdf_file_path): ?>
                                <a href="<?php echo e(route('inspection-standard.download', $inspectionStandard->id)); ?>" 
                                   class="btn btn-sm btn-success" 
                                   title="Download PDF"
                                   download>
                                    <i class="ti ti-download me-1"></i>Download PDF
                                </a>
                            <?php elseif($packagingStandard): ?>
                                <a href="<?php echo e(route('admin.forms.ps.pdf', $packagingStandard->id)); ?>" 
                                   class="btn btn-sm btn-success" 
                                   target="_blank"
                                   title="View PDF">
                                    <i class="ti ti-file-pdf me-1"></i>View PDF
                                </a>
                            <?php elseif($partSubmissionWarrant): ?>
                                <a href="<?php echo e(route('admin.forms.psw.pdf', $partSubmissionWarrant->id)); ?>" 
                                   class="btn btn-sm btn-success" 
                                   target="_blank"
                                   title="View PDF">
                                    <i class="ti ti-file-pdf me-1"></i>View PDF
                                </a>
                            <?php elseif($requestDocument->file_path && $requestDocument->file_path !== 'form'): ?>
                                <a href="<?php echo e(Storage::disk('public')->url($requestDocument->file_path)); ?>" 
                                   class="btn btn-sm btn-success" 
                                   title="Download PDF"
                                   target="_blank" 
                                   download>
                                    <i class="ti ti-download me-1"></i>Download PDF
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Document Viewer Container -->
                <div class="card-body p-0" style="flex: 1; overflow: auto; background-color: #e0e0e0; display: flex; align-items: center; justify-content: center;">
                    <?php if($inspectionStandard && $inspectionStandard->pdf_file_path): ?>
                        <!-- Inspection Standard PDF Preview -->
                        <?php if(str_ends_with($inspectionStandard->pdf_file_path, '.pdf')): ?>
                            <!-- PDF Viewer -->
                            <div id="pdfContainer" style="width: 100%; height: 100%; display: flex; justify-content: center; align-items: center;">
                                <canvas id="pdfCanvas" style="border: 1px solid #ccc; background: white; max-width: 100%; max-height: 100%;"></canvas>
                            </div>
                        <?php else: ?>
                            <div class="text-center text-muted py-5">
                                <i class="ti ti-file-unknown" style="font-size: 3rem; opacity: 0.3;"></i>
                                <p class="mt-3">Tipe file tidak didukung untuk preview</p>
                            </div>
                        <?php endif; ?>
                    <?php elseif($packagingStandard || $partSubmissionWarrant): ?>
                        <!-- Form-Based Document Preview (PS/PSW) -->
                        <div style="width: 100%; height: 100%; overflow-y: auto; background: white;">
                            <?php if($packagingStandard): ?>
                                <?php echo $__env->make('admin.forms.ps-preview-embed', ['ps' => $packagingStandard], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                            <?php elseif($partSubmissionWarrant): ?>
                                <?php echo $__env->make('admin.forms.psw-preview-embed', ['psw' => $partSubmissionWarrant], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                            <?php endif; ?>
                        </div>
                    <?php elseif($requestDocument->file_path && $requestDocument->file_path !== 'form'): ?>
                        <?php if(str_ends_with($requestDocument->file_path, '.pdf')): ?>
                            <!-- PDF Viewer -->
                            <div id="pdfContainer" style="width: 100%; height: 100%; display: flex; justify-content: center; align-items: center;">
                                <canvas id="pdfCanvas" style="border: 1px solid #ccc; background: white; max-width: 100%; max-height: 100%;"></canvas>
                            </div>
                        <?php elseif(in_array(pathinfo($requestDocument->file_path, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'gif'])): ?>
                            <!-- Image Viewer -->
                            <div style="display: flex; align-items: center; justify-content: center; width: 100%; height: 100%;">
                                <img id="imageViewer" src="<?php echo e(Storage::disk('public')->url($requestDocument->file_path)); ?>" 
                                     alt="Document" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                            </div>
                        <?php else: ?>
                            <div class="text-center text-muted py-5">
                                <i class="ti ti-file-unknown" style="font-size: 3rem; opacity: 0.3;"></i>
                                <p class="mt-3">Tipe file tidak didukung untuk preview</p>
                                <a href="<?php echo e(Storage::disk('public')->url($requestDocument->file_path)); ?>" 
                                   class="btn btn-primary btn-sm mt-2"
                                   download>
                                    <i class="ti ti-download me-2"></i>Unduh File
                                </a>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="text-center text-muted py-5">
                            <i class="ti ti-file-alert" style="font-size: 3rem; opacity: 0.3;"></i>
                            <p class="mt-3"><strong>Dokumen belum diunggah</strong></p>
                            <small>Supplier belum mengunggah file untuk dokumen ini</small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Right: Review Panel & Actions -->
        <div class="col-lg-4">
            <!-- Document Metadata Card -->
            <div class="card mb-4">
                <div class="card-header" style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                    <h6 class="mb-0">
                        <i class="ti ti-file-info me-2"></i>Informasi Dokumen
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label small text-muted">Nama File</label>
                        <p class="mb-2 small" style="word-break: break-all;"><?php echo e($requestDocument->original_filename ?? '-'); ?></p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small text-muted">Status Dokumen</label>
                        <div>
                            <?php if($requestDocument->status === 'SUBMITTED'): ?>
                                <span class="badge bg-info">
                                    <i class="ti ti-hourglass-high me-1"></i>Menunggu Review
                                </span>
                            <?php elseif($requestDocument->status === 'APPROVED'): ?>
                                <span class="badge bg-success">
                                    <i class="ti ti-circle-check me-1"></i>APPROVED
                                </span>
                            <?php elseif($requestDocument->status === 'REVISE'): ?>
                                <span class="badge bg-warning">
                                    <i class="ti ti-alert-triangle me-1"></i>Perlu Revisi
                                </span>
                            <?php else: ?>
                                <span class="badge bg-secondary"><?php echo e($requestDocument->status); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small text-muted">Diunggah Pada</label>
                        <p class="mb-0 small"><?php echo e($requestDocument->uploaded_at?->format('d M Y H:i') ?? '-'); ?></p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small text-muted">Tipe Dokumen</label>
                        <p class="mb-0 small">
                            <?php if($requestDocument->documentType->is_mandatory): ?>
                                <span class="badge bg-danger">WAJIB</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">OPTIONAL</span>
                            <?php endif; ?>
                        </p>
                    </div>

                    <div class="mb-0">
                        <label class="form-label small text-muted">Reviewer Scope</label>
                        <p class="mb-0 small">
                            <?php $__currentLoopData = $requestDocument->documentType->scope ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $scope): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <span class="badge bg-light text-dark"><?php echo e($scope); ?></span>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Multi-Level Approval Status Card -->
            <?php if(count($requestDocument->documentType->scope ?? []) > 1): ?>
                <div class="card mb-4" style="border-left: 4px solid var(--primary-color);">
                    <div class="card-header" style="background-color: #f0f9ff; border-bottom: 1px solid #bae6fd;">
                        <h6 class="mb-0" style="color: #075985;">
                            <i class="ti ti-checklist me-2"></i>Status Approval Multi-Level
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="small text-muted mb-3">
                            Dokumen ini memerlukan approval dari <strong><?php echo e(count($requestDocument->documentType->scope)); ?> pihak</strong>
                        </p>
                        
                        <?php
                            $approvedScopes = $requestDocument->getApprovedScopes();
                            $pendingScopes = $requestDocument->getPendingScopes();
                        ?>

                        <?php $__currentLoopData = $requestDocument->documentType->scope; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $scope): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $scopeApproval = $requestDocument->approvals->where('scope', $scope)->where('status', 'APPROVED')->first();
                                $isApproved = in_array($scope, $approvedScopes);
                                $isUserScope = $userScope === $scope;
                            ?>
                            
                            <div class="d-flex align-items-center mb-3 pb-2" style="border-bottom: 1px solid #e2e8f0;">
                                <div class="me-3">
                                    <?php if($isApproved): ?>
                                        <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" 
                                             style="width: 40px; height: 40px;">
                                            <i class="ti ti-check" style="font-size: 1.2rem;"></i>
                                        </div>
                                    <?php else: ?>
                                        <div class="bg-light text-muted rounded-circle d-flex align-items-center justify-content-center" 
                                             style="width: 40px; height: 40px; border: 2px dashed #cbd5e1;">
                                            <i class="ti ti-hourglass-high" style="font-size: 1.2rem;"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <strong class="d-block"><?php echo e($scope); ?></strong>
                                            <?php if($isApproved && $scopeApproval): ?>
                                                <small class="text-success">
                                                    <i class="ti ti-check-circle me-1"></i>
                                                    Disetujui oleh <?php echo e($scopeApproval->user->name ?? 'Admin'); ?>

                                                </small>
                                                <br>
                                                <small class="text-muted">
                                                    <?php echo e($scopeApproval->approved_at?->format('d M Y H:i')); ?>

                                                </small>
                                            <?php else: ?>
                                                <small class="text-warning">
                                                    <i class="ti ti-clock me-1"></i>
                                                    Menunggu approval
                                                    <?php if($isUserScope): ?>
                                                        <span class="badge bg-primary ms-1" style="font-size: 0.7rem;">Scope Anda</span>
                                                    <?php endif; ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                        <div class="mt-3 pt-2">
                            <div class="progress" style="height: 8px;">
                                <?php
                                    $progressPercentage = count($approvedScopes) > 0 ? (count($approvedScopes) / count($requestDocument->documentType->scope)) * 100 : 0;
                                ?>
                                <div class="progress-bar bg-success" role="progressbar" 
                                     style="width: <?php echo e($progressPercentage); ?>%;" 
                                     aria-valuenow="<?php echo e($progressPercentage); ?>" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100">
                                </div>
                            </div>
                            <small class="text-muted d-block mt-1 text-center">
                                <?php echo e(count($approvedScopes)); ?> dari <?php echo e(count($requestDocument->documentType->scope)); ?> sudah approve
                            </small>
                        </div>

                        <?php if($requestDocument->status === 'APPROVED'): ?>
                            <div class="alert alert-success mt-3 mb-0" role="alert">
                                <i class="ti ti-circle-check me-2"></i>
                                <strong>Semua pihak telah menyetujui dokumen ini!</strong>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Revision Note Display (if any) -->
            <?php if($requestDocument->status === 'REVISE' && $requestDocument->revision_note): ?>
                <div class="card mb-4" style="border-left: 4px solid var(--warning-color);">
                    <div class="card-header" style="background-color: #fffbeb; border-bottom: 1px solid #fde68a;">
                        <h6 class="mb-0" style="color: #92400e;">
                            <i class="ti ti-alert-triangle me-2"></i>Catatan Revisi
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-0 text-muted"><?php echo e($requestDocument->revision_note); ?></p>
                        <?php if($requestDocument->revision_deadline_at): ?>
                            <hr class="my-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <i class="ti ti-clock me-1"></i>
                                    Deadline: <?php echo e($requestDocument->revision_deadline_at->format('d M Y H:i')); ?>

                                </small>
                                <div id="revisionCountdown"></div>
                            </div>
                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    initCountdown('revisionCountdown', '<?php echo e($requestDocument->revision_deadline_at->toIso8601String()); ?>');
                                });
                            </script>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Document History -->
            <div class="card mb-4">
                <div class="card-header" style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                    <h6 class="mb-0">
                        <i class="ti ti-history me-2"></i>Riwayat Dokumen
                    </h6>
                </div>
                <div class="card-body p-0">
                    <?php if($requestDocument->histories->count() > 0): ?>
                        <div class="list-group list-group-flush">
                            <?php $__currentLoopData = $requestDocument->histories->sortByDesc('created_at'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $history): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="list-group-item p-3" style="border-bottom: 1px solid #e2e8f0;">
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <div>
                                            <p class="mb-1 small fw-600"><?php echo e($history->actor_name ?? 'System'); ?></p>
                                            <?php
                                                // Determine badge color based on action
                                                $badgeClass = match($history->action) {
                                                    'APPROVE' => 'bg-success',
                                                    'APPROVED_FINAL' => 'bg-success',
                                                    'PARTIAL_APPROVE' => 'bg-info',
                                                    'REJECT_REVISE' => 'bg-warning text-dark',
                                                    'UPLOAD' => 'bg-primary',
                                                    default => 'bg-light text-dark',
                                                };
                                            ?>
                                            <span class="badge <?php echo e($badgeClass); ?>" style="font-size: 0.75rem;">
                                                <?php if($history->action === 'APPROVE'): ?>
                                                    <i class="ti ti-check me-1"></i>
                                                <?php elseif($history->action === 'APPROVED_FINAL'): ?>
                                                    <i class="ti ti-circle-check me-1"></i>
                                                <?php elseif($history->action === 'PARTIAL_APPROVE'): ?>
                                                    <i class="ti ti-checks me-1"></i>
                                                <?php elseif($history->action === 'REJECT_REVISE'): ?>
                                                    <i class="ti ti-alert-triangle me-1"></i>
                                                <?php endif; ?>
                                                <?php echo e($history->action); ?>

                                            </span>
                                        </div>
                                        <small class="text-muted"><?php echo e($history->created_at->format('d M y H:i')); ?></small>
                                    </div>
                                    <?php if($history->note): ?>
                                        <p class="mb-0 mt-2 small text-muted"><?php echo e($history->note); ?></p>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php else: ?>
                        <div class="p-4 text-center text-muted">
                            <i class="ti ti-inbox" style="font-size: 2rem; opacity: 0.3;"></i>
                            <p class="mb-0 small mt-2">Belum ada history</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Approval Actions (Sticky) -->
            <?php if($requestDocument->status === 'SUBMITTED'): ?>
                <?php
                    // Check apakah user scope ini sudah approve
                    $userScopeApproved = $requestDocument->approvals
                        ->where('scope', $userScope)
                        ->where('status', 'APPROVED')
                        ->first();
                ?>

                <?php if(!$userScopeApproved): ?>
                    <div class="card" style="border-top: 3px solid var(--accent-color);">
                        <div class="card-header" style="background-color: #f8fafc;">
                            <h6 class="mb-0">
                                <i class="ti ti-check-circle me-2"></i>Keputusan Approval
                            </h6>
                        </div>
                        <div class="card-body">
                            <?php if(count($requestDocument->documentType->scope ?? []) > 1): ?>
                                <div class="alert alert-info" role="alert">
                                    <i class="ti ti-info-circle me-2"></i>
                                    <small>
                                        Anda akan meng-approve sebagai <strong><?php echo e($userScope); ?></strong>. 
                                        <?php
                                            $pendingCount = count($requestDocument->getPendingScopes());
                                        ?>
                                        <?php if($pendingCount > 1): ?>
                                            Dokumen masih memerlukan <?php echo e($pendingCount - 1); ?> approval lainnya.
                                        <?php else: ?>
                                            Setelah approval Anda, dokumen akan berstatus <strong>APPROVED</strong>.
                                        <?php endif; ?>
                                    </small>
                                </div>
                            <?php endif; ?>

                            <div class="d-grid gap-2 mb-3">
                                <form action="<?php echo e(route('review.approve', $requestDocument)); ?>" method="POST">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="btn btn-success" style="padding: 0.75rem;">
                                        <i class="ti ti-check-circle me-2"></i>
                                        <strong>Setujui Dokumen (<?php echo e($userScope); ?>)</strong>
                                    </button>
                                </form>
                            </div>

                            <button type="button" class="btn btn-warning w-100" data-bs-toggle="modal" data-bs-target="#revisionModal" style="padding: 0.75rem;">
                                <i class="ti ti-edit-circle me-2"></i>
                                <strong>Minta Revisi</strong>
                            </button>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-success text-center" role="alert">
                        <i class="ti ti-check-circle me-2"></i>
                        <strong>Anda sudah meng-approve dokumen ini sebagai <?php echo e($userScope); ?></strong>
                        <hr>
                        <small class="text-muted">
                            <?php if($requestDocument->allScopesApproved()): ?>
                                Semua pihak sudah approve. Dokumen berstatus APPROVED.
                            <?php else: ?>
                                Menunggu approval dari: 
                                <?php $__currentLoopData = $requestDocument->getPendingScopes(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pending): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <span class="badge bg-warning text-dark"><?php echo e($pending); ?></span>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php endif; ?>
                        </small>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="alert alert-info text-center" role="alert">
                    <i class="ti ti-info-circle me-2"></i>
                    <strong>Status Final:</strong> Dokumen sudah berstatus <strong><?php echo e($requestDocument->status); ?></strong>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Revision Modal -->
<div class="modal fade" id="revisionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #fffbeb; border-bottom: 2px solid #fde68a;">
                <h5 class="modal-title" style="color: #92400e;">
                    <i class="ti ti-alert-triangle me-2"></i>Minta Revisi Dokumen
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?php echo e(route('review.reject', $requestDocument)); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <div class="modal-body">
                    <!-- Quick Templates -->
                    <label class="form-label small text-muted mb-2">Pilih Template Revisi (Opsional)</label>
                    <div class="mb-3" id="templateButtons">
                        <button type="button" class="btn btn-outline-secondary btn-sm me-2 mb-2" onclick="useTemplate('Dokumen tidak lengkap. Mohon lengkapi seluruh halaman.')">
                            <i class="ti ti-file-text me-1"></i>Tidak Lengkap
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm me-2 mb-2" onclick="useTemplate('Kualitas dokumen kurang jelas. Mohon upload ulang dengan resolusi lebih tinggi.')">
                            <i class="ti ti-photo me-1"></i>Kualitas Buruk
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm me-2 mb-2" onclick="useTemplate('Format dokumen tidak sesuai. Mohon upload dalam format yang disyaratkan.')">
                            <i class="ti ti-alert-triangle me-1"></i>Format Salah
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm me-2 mb-2" onclick="useTemplate('Informasi tidak lengkap. Mohon tambahkan informasi yang diperlukan.')">
                            <i class="ti ti-note me-1"></i>Info Kurang
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm mb-2" onclick="useTemplate('Data tidak sesuai dengan PO/specification. Mohon koreksi.')">
                            <i class="ti ti-bookmark me-1"></i>Data Tidak Sesuai
                        </button>
                    </div>

                    <hr>

                    <!-- Revision Note Textarea -->
                    <div class="mb-3">
                        <label for="revision_note" class="form-label">
                            <strong>Catatan Revisi</strong>
                            <span class="text-danger">*</span>
                            <small class="text-muted float-end">
                                <span id="charCount">0</span>/1000
                            </small>
                        </label>
                        <textarea class="form-control" id="revision_note" name="revision_note" rows="5" 
                                  placeholder="Jelaskan detail apa yang perlu direvisi oleh supplier..." 
                                  maxlength="1000" required></textarea>
                        <small class="text-muted">Catatan ini akan dikirimkan via email ke supplier</small>
                    </div>

                    <!-- Info Alert -->
                    <div class="alert alert-info" role="alert">
                        <i class="ti ti-info-circle me-2"></i>
                        <strong>Informasi:</strong> Supplier akan menerima email dengan catatan revisi ini dan memiliki 2 hari kalender untuk melakukan perbaikan. Pengingat otomatis akan dikirim sebelum deadline.
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

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>
// PDF.js setup
pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

let pdfDoc = null;
let pageNum = 1;
let pageRendering = false;
let pageNumPending = null;
let pdfZoom = 1;
let pdfRotation = 0;

const canvas = document.getElementById('pdfCanvas');
const ctx = canvas?.getContext('2d');

// Check if PDF exists and render
<?php if($inspectionStandard && $inspectionStandard->pdf_file_path && str_ends_with($inspectionStandard->pdf_file_path, '.pdf')): ?>
    loadPDF('<?php echo e(route('inspection-standard.pdf', $inspectionStandard->id)); ?>');
<?php elseif(str_ends_with($requestDocument->file_path ?? '', '.pdf')): ?>
    loadPDF('<?php echo e(Storage::disk('public')->url($requestDocument->file_path)); ?>');
<?php endif; ?>

function loadPDF(pdfUrl) {
    pdfjsLib.getDocument(pdfUrl).promise.then(function(pdf) {
        pdfDoc = pdf;
        document.getElementById('pageCount').textContent = pdfDoc.numPages;
        renderPage(pageNum);
    }).catch(function(error) {
        console.error('Error loading PDF:', error);
    });
}

function renderPage(num) {
    if (pageRendering) {
        pageNumPending = num;
        return;
    }
    pageRendering = true;

    pdfDoc.getPage(num).then(function(page) {
        const viewport = page.getViewport({scale: pdfZoom, rotation: pdfRotation});
        canvas.height = viewport.height;
        canvas.width = viewport.width;

        const renderContext = {
            canvasContext: ctx,
            viewport: viewport
        };
        page.render(renderContext).promise.then(function() {
            pageRendering = false;
            document.getElementById('pageNum').textContent = num;
            if (pageNumPending !== null) {
                renderPage(pageNumPending);
                pageNumPending = null;
            }
        });
    });
}

document.getElementById('prevPageBtn')?.addEventListener('click', function() {
    if (pageNum <= 1) return;
    pageNum--;
    renderPage(pageNum);
});

document.getElementById('nextPageBtn')?.addEventListener('click', function() {
    if (pageNum >= pdfDoc.numPages) return;
    pageNum++;
    renderPage(pageNum);
});

document.getElementById('zoomInBtn')?.addEventListener('click', function() {
    pdfZoom += 0.25;
    renderPage(pageNum);
});

document.getElementById('zoomOutBtn')?.addEventListener('click', function() {
    if (pdfZoom <= 0.5) return;
    pdfZoom -= 0.25;
    renderPage(pageNum);
});

document.getElementById('rotateBtn')?.addEventListener('click', function() {
    pdfRotation = (pdfRotation + 90) % 360;
    renderPage(pageNum);
});

// Character counter
document.getElementById('revision_note')?.addEventListener('keyup', function() {
    document.getElementById('charCount').textContent = this.value.length;
});

// Template functions
function useTemplate(template) {
    document.getElementById('revision_note').value = template;
    document.getElementById('charCount').textContent = template.length;
}
</script>

<style>
.list-group-item:last-child {
    border-bottom: none !important;
}

#pdfContainer {
    position: relative;
}

#pdfCanvas {
    display: block;
    margin: 0 auto;
}
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\KERJAAN\akebono-approval\resources\views/admin/review/show.blade.php ENDPATH**/ ?>