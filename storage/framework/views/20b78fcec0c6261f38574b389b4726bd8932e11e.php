<?php $__env->startSection('title', 'Packaging Standard Form - PT Akebono'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4 animate-on-scroll">
    <!-- Header Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-gradient-primary text-white">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="mb-3">
                                <i class="ti ti-file-text me-2"></i>Packaging Standard (PS) Form
                            </h2>
                            <p class="mb-2">Silakan isi form di bawah untuk membuat Packaging Standard secara digital.</p>
                            <div class="alert alert-info mb-0 bg-white bg-opacity-20 border-0">
                                <i class="ti ti-info-circle me-2"></i>
                                <strong>Info:</strong> Dokumen akan disimpan dan dapat diubah sebelum di-submit. Setelah submit, dokumen akan di-generate menjadi PDF otomatis.
                            </div>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="bg-white bg-opacity-20 rounded-lg p-3">
                                <h4 class="text-white mb-2">Status</h4>
                                <p class="text-white-50 mb-0" id="formStatus">Draft</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stepper Navigation -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body py-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <!-- Step 1 -->
                        <div class="step-item active" data-step="1">
                            <div class="step-circle">
                                <i class="ti ti-info-circle"></i>
                            </div>
                            <p class="step-label mb-0">General<br>Information</p>
                        </div>
                        
                        <!-- Arrow -->
                        <div class="flex-grow-1 mx-2">
                            <div class="step-divider"></div>
                        </div>
                        
                        <!-- Step 2 -->
                        <div class="step-item" data-step="2">
                            <div class="step-circle">
                                <i class="ti ti-package"></i>
                            </div>
                            <p class="step-label mb-0">Packaging<br>Specifications</p>
                        </div>
                        
                        <!-- Arrow -->
                        <div class="flex-grow-1 mx-2">
                            <div class="step-divider"></div>
                        </div>
                        
                        <!-- Step 3 -->
                        <div class="step-item" data-step="3">
                            <div class="step-circle">
                                <i class="ti ti-check"></i>
                            </div>
                            <p class="step-label mb-0">Visualization &<br>Approval</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Content -->
    <div class="row">
        <div class="col-12">
            <form id="psForm" method="POST" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                
                <!-- Step 1: General Information -->
                <div class="step-content active" id="step-1">
                    <div class="card">
                        <div class="card-header bg-light border-bottom">
                            <h5 class="mb-0"><i class="ti ti-info-circle me-2"></i>General Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Document No. -->
                                <div class="col-md-6 mb-3">
                                    <label for="document_no" class="form-label"><span class="text-danger">*</span> Document No.</label>
                                    <input type="text" class="form-control" id="document_no" name="document_no" value="<?php echo e(old('document_no', $ps->document_no ?? '')); ?>" required placeholder="Enter document number">
                                    <small class="form-text text-muted">Required field</small>
                                </div>

                                <!-- Revision -->
                                <div class="col-md-6 mb-3">
                                    <label for="revision" class="form-label"><span class="text-danger">*</span> Revision</label>
                                    <input type="text" class="form-control" id="revision" name="revision" value="<?php echo e(old('revision', $ps->revision ?? '')); ?>" required placeholder="e.g., A, B, 1.0, Rev 1">
                                    <small class="form-text text-muted">Required field</small>
                                </div>

                                <!-- Date -->
                                <div class="col-md-6 mb-3">
                                    <label for="date" class="form-label"><span class="text-danger">*</span> Date</label>
                                    <input type="date" class="form-control" id="date" name="date" value="<?php echo e(old('date', $ps->date?->format('Y-m-d') ?? '')); ?>" required>
                                </div>

                                <!-- Customer (Read-only) -->
                                <div class="col-md-6 mb-3">
                                    <label for="customer" class="form-label">Customer</label>
                                    <input type="text" class="form-control" id="customer" name="customer" value="<?php echo e($ps->customer ?? 'AAIJ (Purchasing & Quality Division)'); ?>" disabled>
                                    <small class="form-text text-muted">Auto-populated</small>
                                </div>

                                <!-- Part No. -->
                                <div class="col-md-6 mb-3">
                                    <label for="part_no" class="form-label"><span class="text-danger">*</span> Part No.</label>
                                    <input type="text" class="form-control" id="part_no" name="part_no" value="<?php echo e(old('part_no', $ps->part_no ?? '')); ?>" required placeholder="Enter part number">
                                </div>

                                <!-- Part Name -->
                                <div class="col-md-6 mb-3">
                                    <label for="part_name" class="form-label"><span class="text-danger">*</span> Part Name</label>
                                    <input type="text" class="form-control" id="part_name" name="part_name" value="<?php echo e(old('part_name', $ps->part_name ?? '')); ?>" required placeholder="Enter part name">
                                </div>

                                <!-- Supplier Name (Read-only) -->
                                <div class="col-md-6 mb-3">
                                    <label for="supplier_name" class="form-label">Supplier Name</label>
                                    <input type="text" class="form-control" id="supplier_name" name="supplier_name" value="<?php echo e($ps->supplier_name ?? ($requestDocument->requestPart?->documentRequest?->supplier?->name ?? '')); ?>" disabled>
                                    <small class="form-text text-muted">Auto-populated</small>
                                </div>

                                <!-- Qty -->
                                <div class="col-md-6 mb-3">
                                    <label for="qty" class="form-label"><span class="text-danger">*</span> Qty</label>
                                    <input type="number" class="form-control" id="qty" name="qty" value="<?php echo e(old('qty', $ps->qty ?? '')); ?>" required min="1" placeholder="Enter quantity">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Packaging Specifications -->
                <div class="step-content" id="step-2">
                    <div class="card">
                        <div class="card-header bg-light border-bottom">
                            <h5 class="mb-0"><i class="ti ti-package me-2"></i>Packaging Specifications</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Packaging Type -->
                                <div class="col-md-6 mb-3">
                                    <label for="packaging_type" class="form-label"><span class="text-danger">*</span> Packaging Type</label>
                                    <input type="text" class="form-control" id="packaging_type" name="packaging_type" value="<?php echo e(old('packaging_type', $ps->packaging_type ?? '')); ?>" required placeholder="e.g., Box, Plastic Bag, Carton">
                                </div>

                                <!-- Packaging Size (mm) -->
                                <div class="col-md-6 mb-3">
                                    <label for="packaging_size_mm" class="form-label"><span class="text-danger">*</span> Packaging Size (mm)</label>
                                    <input type="text" class="form-control" id="packaging_size_mm" name="packaging_size_mm" value="<?php echo e(old('packaging_size_mm', $ps->packaging_size_mm ?? '')); ?>" required placeholder="e.g., 100 x 50 x 30">
                                </div>

                                <!-- Packaging Lot (pcs/box) -->
                                <div class="col-md-6 mb-3">
                                    <label for="packaging_lot_pcs_box" class="form-label"><span class="text-danger">*</span> Packaging Lot (pcs/box)</label>
                                    <input type="number" class="form-control" id="packaging_lot_pcs_box" name="packaging_lot_pcs_box" value="<?php echo e(old('packaging_lot_pcs_box', $ps->packaging_lot_pcs_box ?? '')); ?>" required min="1" placeholder="Pieces per box">
                                </div>

                                <!-- Net Weight (g/pc) -->
                                <div class="col-md-6 mb-3">
                                    <label for="net_weight_g_pc" class="form-label">Net Weight (g/pc)</label>
                                    <input type="number" class="form-control" id="net_weight_g_pc" name="net_weight_g_pc" step="0.01" value="<?php echo e(old('net_weight_g_pc', $ps->net_weight_g_pc ?? '')); ?>" placeholder="Weight in grams per piece">
                                </div>

                                <!-- Total Weight (kg/box) -->
                                <div class="col-md-6 mb-3">
                                    <label for="total_weight_kg_box" class="form-label">Total Weight (kg/box)</label>
                                    <input type="number" class="form-control" id="total_weight_kg_box" name="total_weight_kg_box" step="0.01" value="<?php echo e(old('total_weight_kg_box', $ps->total_weight_kg_box ?? '')); ?>" placeholder="Total weight in kg per box">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Visualization & Approval -->
                <div class="step-content" id="step-3">
                    <div class="card">
                        <div class="card-header bg-light border-bottom">
                            <h5 class="mb-0"><i class="ti ti-photo me-2"></i>Visualization & Approval</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Image - Photographic -->
                                <div class="col-md-6 mb-4">
                                    <label class="form-label">Image - Photographic</label>
                                    <div class="image-upload-zone mb-2" data-image-type="photographic">
                                        <input type="file" class="form-control d-none" name="image_photographic" accept="image/jpeg,image/png" data-image-type="photographic">
                                        <div class="upload-placeholder">
                                            <i class="ti ti-photo-plus fs-1"></i>
                                            <p class="mb-0">Click to upload or drag image</p>
                                            <small>JPG or PNG, max 10MB</small>
                                        </div>
                                    </div>
                                    <?php if($ps->image_photographic): ?>
                                        <div class="alert alert-info mb-0">
                                            <i class="ti ti-check-circle me-2"></i>Image uploaded
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Image - Part Identification -->
                                <div class="col-md-6 mb-4">
                                    <label class="form-label">Image - Part Identification</label>
                                    <div class="image-upload-zone mb-2" data-image-type="part_identification">
                                        <input type="file" class="form-control d-none" name="image_part_identification" accept="image/jpeg,image/png" data-image-type="part_identification">
                                        <div class="upload-placeholder">
                                            <i class="ti ti-photo-plus fs-1"></i>
                                            <p class="mb-0">Click to upload or drag image</p>
                                            <small>JPG or PNG, max 10MB</small>
                                        </div>
                                    </div>
                                    <?php if($ps->image_part_identification): ?>
                                        <div class="alert alert-info mb-0">
                                            <i class="ti ti-check-circle me-2"></i>Image uploaded
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Image - Visualisasi Packing -->
                                <div class="col-md-6 mb-4">
                                    <label class="form-label">Image - Visualisasi Packing</label>
                                    <div class="image-upload-zone mb-2" data-image-type="visualisasi_packing">
                                        <input type="file" class="form-control d-none" name="image_visualisasi_packing" accept="image/jpeg,image/png" data-image-type="visualisasi_packing">
                                        <div class="upload-placeholder">
                                            <i class="ti ti-photo-plus fs-1"></i>
                                            <p class="mb-0">Click to upload or drag image</p>
                                            <small>JPG or PNG, max 10MB</small>
                                        </div>
                                    </div>
                                    <?php if($ps->image_visualisasi_packing): ?>
                                        <div class="alert alert-info mb-0">
                                            <i class="ti ti-check-circle me-2"></i>Image uploaded
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Image - Label Packing -->
                                <div class="col-md-6 mb-4">
                                    <label class="form-label">Image - Label Packing</label>
                                    <div class="image-upload-zone mb-2" data-image-type="label_packing">
                                        <input type="file" class="form-control d-none" name="image_label_packing" accept="image/jpeg,image/png" data-image-type="label_packing">
                                        <div class="upload-placeholder">
                                            <i class="ti ti-photo-plus fs-1"></i>
                                            <p class="mb-0">Click to upload or drag image</p>
                                            <small>JPG or PNG, max 10MB</small>
                                        </div>
                                    </div>
                                    <?php if($ps->image_label_packing): ?>
                                        <div class="alert alert-info mb-0">
                                            <i class="ti ti-check-circle me-2"></i>Image uploaded
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Approval Section -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <h6 class="mb-3">Approval Signatures</h6>
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th colspan="3" class="text-center bg-light">PREPARED BY (Supplier)</th>
                                                    <th colspan="3" class="text-center bg-light">CHECKED BY (Reviewer)</th>
                                                    <th colspan="3" class="text-center bg-light">APPROVED BY (Final Approval)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td colspan="3" class="text-center">
                                                        <label for="prepared_by_name" class="form-label"><span class="text-danger">*</span> Name</label>
                                                        <input type="text" class="form-control" id="prepared_by_name" name="prepared_by_name" value="<?php echo e(old('prepared_by_name', $ps->prepared_by_name ?? '')); ?>" required placeholder="Enter name">
                                                    </td>
                                                    <td colspan="3" class="text-center">
                                                        <label for="checked_by_name" class="form-label">Name</label>
                                                        <input type="text" class="form-control" id="checked_by_name" name="checked_by_name" value="<?php echo e(old('checked_by_name', $ps->checked_by_name ?? '')); ?>" placeholder="Enter name" disabled>
                                                    </td>
                                                    <td colspan="3" class="text-center">
                                                        <label for="approved_by_name" class="form-label">Name</label>
                                                        <input type="text" class="form-control" id="approved_by_name" name="approved_by_name" value="<?php echo e(old('approved_by_name', $ps->approved_by_name ?? '')); ?>" placeholder="Enter name" disabled>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="3" class="text-center">
                                                        <label for="prepared_by_date" class="form-label"><span class="text-danger">*</span> Date</label>
                                                        <input type="date" class="form-control" id="prepared_by_date" name="prepared_by_date" value="<?php echo e(old('prepared_by_date', $ps->prepared_by_date?->format('Y-m-d') ?? '')); ?>" required>
                                                    </td>
                                                    <td colspan="3" class="text-center">
                                                        <label for="checked_by_date" class="form-label">Date</label>
                                                        <input type="date" class="form-control" id="checked_by_date" name="checked_by_date" value="<?php echo e(old('checked_by_date', $ps->checked_by_date?->format('Y-m-d') ?? '')); ?>" disabled>
                                                    </td>
                                                    <td colspan="3" class="text-center">
                                                        <label for="approved_by_date" class="form-label">Date</label>
                                                        <input type="date" class="form-control" id="approved_by_date" name="approved_by_date" value="<?php echo e(old('approved_by_date', $ps->approved_by_date?->format('Y-m-d') ?? '')); ?>" disabled>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <button type="button" class="btn btn-outline-secondary" id="prevBtn" style="display: none;">
                                            <i class="ti ti-arrow-left me-2"></i>Previous
                                        </button>
                                    </div>
                                    <div class="text-center" id="stepIndicator">
                                        Step <span id="currentStep">1</span> of 3
                                    </div>
                                    <div>
                                        <button type="button" class="btn btn-outline-primary" id="nextBtn">
                                            Next<i class="ti ti-arrow-right ms-2"></i>
                                        </button>
                                        <button type="button" class="btn btn-success" id="submitBtn" style="display: none;">
                                            <i class="ti ti-check me-2"></i>Submit Form
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .step-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        cursor: pointer;
        position: relative;
    }

    .step-item.active .step-circle {
        background-color: #0F2942;
        color: white;
    }

    .step-circle {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #e9ecef;
        color: #0F2942;
        font-size: 24px;
        margin-bottom: 8px;
        transition: all 0.3s ease;
    }

    .step-label {
        font-size: 12px;
        font-weight: 600;
        text-align: center;
        color: #0F2942;
    }

    .step-divider {
        height: 2px;
        background-color: #e9ecef;
        width: 100%;
    }

    .step-content {
        display: none;
        animation: fadeIn 0.3s ease;
    }

    .step-content.active {
        display: block;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .form-label {
        font-weight: 600;
        color: #334155;
        font-size: 0.95rem;
    }

    .form-control {
        border-color: #E5E7EB;
        border-radius: 8px;
        padding: 0.65rem 0.875rem;
    }

    .form-control:focus {
        border-color: #2563EB;
        box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.25);
    }

    .image-upload-zone {
        border: 2px dashed #CBD5E1;
        border-radius: 8px;
        padding: 30px;
        text-align: center;
        cursor: pointer;
        background-color: #F8FAFC;
        transition: all 0.3s ease;
    }

    .image-upload-zone:hover {
        border-color: #2563EB;
        background-color: #DBEAFE;
    }

    .upload-placeholder {
        color: #64748B;
    }

    .upload-placeholder i {
        color: #94A3B8;
    }

    .table-responsive {
        border-radius: 8px;
        overflow: hidden;
    }

    .table {
        margin-bottom: 0;
    }

    .table th {
        background-color: #F3F4F6;
        font-weight: 600;
        border-color: #E5E7EB;
        padding: 12px;
    }

    .table td {
        border-color: #E5E7EB;
        padding: 12px;
    }

    .table td .form-control {
        margin-top: 8px;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentStep = 1;
    const totalSteps = 3;

    const nextBtn = document.getElementById('nextBtn');
    const prevBtn = document.getElementById('prevBtn');
    const submitBtn = document.getElementById('submitBtn');

    nextBtn.addEventListener('click', function() {
        if (currentStep < totalSteps) {
            currentStep++;
            updateStep();
        }
    });

    prevBtn.addEventListener('click', function() {
        if (currentStep > 1) {
            currentStep--;
            updateStep();
        }
    });

    submitBtn.addEventListener('click', function() {
        if (confirm('Are you sure you want to submit this form? You will not be able to edit it after submission.')) {
            submitForm();
        }
    });

    // Image upload handling
    document.querySelectorAll('.image-upload-zone').forEach(zone => {
        zone.addEventListener('click', function() {
            zone.querySelector('input[type="file"]').click();
        });

        zone.addEventListener('dragover', function(e) {
            e.preventDefault();
            zone.style.borderColor = '#2563EB';
            zone.style.backgroundColor = '#DBEAFE';
        });

        zone.addEventListener('dragleave', function() {
            zone.style.borderColor = '#CBD5E1';
            zone.style.backgroundColor = '#F8FAFC';
        });

        zone.addEventListener('drop', function(e) {
            e.preventDefault();
            zone.style.borderColor = '#CBD5E1';
            zone.style.backgroundColor = '#F8FAFC';
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                zone.querySelector('input[type="file"]').files = files;
            }
        });

        zone.querySelector('input[type="file"]').addEventListener('change', function(e) {
            if (e.target.files.length > 0) {
                zone.querySelector('.upload-placeholder').innerHTML = '<i class="ti ti-check-circle fs-1 text-success"></i><p class="mb-0 text-success">Image uploaded</p>';
            }
        });
    });

    function updateStep() {
        // Hide all step contents
        document.querySelectorAll('.step-content').forEach(content => {
            content.classList.remove('active');
        });

        // Show current step content
        document.getElementById('step-' + currentStep).classList.add('active');

        // Update step indicators
        document.querySelectorAll('.step-item').forEach((item, index) => {
            if (index + 1 === currentStep) {
                item.classList.add('active');
            } else {
                item.classList.remove('active');
            }
        });

        // Update button visibility
        document.getElementById('currentStep').textContent = currentStep;
        prevBtn.style.display = currentStep > 1 ? 'block' : 'none';
        nextBtn.style.display = currentStep < totalSteps ? 'block' : 'none';
        submitBtn.style.display = currentStep === totalSteps ? 'block' : 'none';

        // Auto-save draft when changing steps
        saveDraft();
    }

    function saveDraft() {
        const formData = new FormData(document.getElementById('psForm'));
        
        fetch('/api/packaging-standards/<?php echo e($ps->id); ?>/draft', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('[name="_token"]').value,
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Draft saved');
            }
        })
        .catch(error => console.error('Error saving draft:', error));
    }

    function submitForm() {
        const formData = new FormData(document.getElementById('psForm'));
        
        fetch('/api/packaging-standards/<?php echo e($ps->id); ?>/submit', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('[name="_token"]').value,
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Form submitted successfully!');
                window.location.href = data.redirect;
            } else {
                alert('Error submitting form: ' + (data.errors ? data.errors.join(', ') : data.message));
            }
        })
        .catch(error => {
            console.error('Error submitting form:', error);
            alert('Error submitting form');
        });
    }
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.supplier', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\KERJAAN\akebono-approval\resources\views/packaging-standard/form.blade.php ENDPATH**/ ?>