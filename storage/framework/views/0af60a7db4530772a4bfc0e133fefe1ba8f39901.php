<div class="p-4">
    <!-- Header -->
    <div class="mb-4 pb-3 border-bottom">
        <h5 class="mb-3">
            <i class="ti ti-file-text me-2"></i>Part Submission Warrant (PSW)
        </h5>
        <div class="row">
            <div class="col-md-4">
                <small class="text-muted">Part Number</small>
                <p class="mb-0 fw-semibold"><?php echo e($psw->part_number); ?></p>
            </div>
            <div class="col-md-4">
                <small class="text-muted">Status</small>
                <p class="mb-0">
                    <span class="badge bg-<?php echo e($psw->status_color); ?>"><?php echo e($psw->status_label); ?></span>
                </p>
            </div>
            <div class="col-md-4">
                <small class="text-muted">Supplier</small>
                <p class="mb-0"><?php echo e($psw->supplier?->name ?? $psw->supplier_name); ?></p>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" href="#psw-step1" data-bs-toggle="tab">
                <i class="ti ti-info-circle me-2"></i>Informasi Umum
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#psw-step2" data-bs-toggle="tab">
                <i class="ti ti-building me-2"></i>Supplier
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#psw-step3" data-bs-toggle="tab">
                <i class="ti ti-flask me-2"></i>Alasan & Hasil
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#psw-step4" data-bs-toggle="tab">
                <i class="ti ti-signature me-2"></i>Deklarasi
            </a>
        </li>
    </ul>

    <div class="tab-content">
        <!-- Step 1 -->
        <div class="tab-pane fade show active" id="psw-step1">
            <div class="row mb-3">
                <div class="col-md-6 mb-3">
                    <small class="text-muted">Part Name</small>
                    <p class="mb-0"><?php echo e($psw->part_name); ?></p>
                </div>
                <div class="col-md-6 mb-3">
                    <small class="text-muted">Part Number</small>
                    <p class="mb-0 fw-semibold"><?php echo e($psw->part_number); ?></p>
                </div>
                <div class="col-md-6 mb-3">
                    <small class="text-muted">Model</small>
                    <p class="mb-0"><?php echo e($psw->model ?? '-'); ?></p>
                </div>
                <div class="col-md-6 mb-3">
                    <small class="text-muted">Revision</small>
                    <p class="mb-0"><?php echo e($psw->revision ?? '-'); ?></p>
                </div>
                <div class="col-md-12 mb-3">
                    <small class="text-muted">Date Submitted</small>
                    <p class="mb-0"><?php echo e($psw->date_submitted?->format('d M Y') ?? '-'); ?></p>
                </div>
            </div>
        </div>

        <!-- Step 2 -->
        <div class="tab-pane fade" id="psw-step2">
            <div class="row mb-3">
                <div class="col-md-6 mb-3">
                    <small class="text-muted">Supplier Name</small>
                    <p class="mb-0"><?php echo e($psw->supplier_name); ?></p>
                </div>
                <div class="col-md-6 mb-3">
                    <small class="text-muted">Address</small>
                    <p class="mb-0"><?php echo e($psw->supplier_address ?? '-'); ?></p>
                </div>
                <div class="col-md-6 mb-3">
                    <small class="text-muted">Contact</small>
                    <p class="mb-0"><?php echo e($psw->supplier_contact ?? '-'); ?></p>
                </div>
                <div class="col-md-6 mb-3">
                    <small class="text-muted">Phone</small>
                    <p class="mb-0"><?php echo e($psw->supplier_phone ?? '-'); ?></p>
                </div>
                <div class="col-md-12 mb-3">
                    <small class="text-muted">Email</small>
                    <p class="mb-0"><?php echo e($psw->supplier_email ?? '-'); ?></p>
                </div>
            </div>
        </div>

        <!-- Step 3 -->
        <div class="tab-pane fade" id="psw-step3">
            <div class="mb-3">
                <small class="text-muted">Alasan Perubahan</small>
                <p class="mb-0" style="white-space: pre-wrap;"><?php echo e($psw->reason_for_change ?? '-'); ?></p>
            </div>
            <div class="row mb-3">
                <div class="col-md-6 mb-3">
                    <small class="text-muted">Evaluation Result</small>
                    <p class="mb-0"><?php echo e($psw->evaluation_result ?? '-'); ?></p>
                </div>
                <div class="col-md-6 mb-3">
                    <small class="text-muted">Durability Test</small>
                    <p class="mb-0"><?php echo e($psw->durability_test ?? '-'); ?></p>
                </div>
            </div>
            <div class="mb-3">
                <small class="text-muted">Functionality Test</small>
                <p class="mb-0"><?php echo e($psw->functionality_test ?? '-'); ?></p>
            </div>
            <div class="mb-3">
                <small class="text-muted">Test Results</small>
                <p class="mb-0" style="white-space: pre-wrap;"><?php echo e($psw->test_results ?? '-'); ?></p>
            </div>
        </div>

        <!-- Step 4 -->
        <div class="tab-pane fade" id="psw-step4">
            <div class="mb-3">
                <small class="text-muted">Declaration Text</small>
                <p class="mb-0" style="white-space: pre-wrap;"><?php echo e($psw->declaration_text ?? '-'); ?></p>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <small class="text-muted">Date</small>
                    <p class="mb-0"><?php echo e($psw->declaration_date?->format('d M Y') ?? '-'); ?></p>
                </div>
                <div class="col-md-6 mb-3">
                    <small class="text-muted">Signatory</small>
                    <p class="mb-0"><?php echo e($psw->declaration_signatory ?? '-'); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>
<?php /**PATH E:\KERJAAN\akebono-approval\resources\views/admin/forms/psw-preview-embed.blade.php ENDPATH**/ ?>