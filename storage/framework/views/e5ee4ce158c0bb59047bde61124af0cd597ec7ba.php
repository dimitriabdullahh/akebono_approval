<?php $__env->startSection('title', 'Part Submission Warrant (PSW) Form - PT Akebono'); ?>

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
                                <i class="ti ti-file-text me-2"></i>Part Submission Warrant (PSW) Form
                            </h2>
                            <p class="mb-2">Silakan isi form di bawah untuk membuat Part Submission Warrant secara digital.</p>
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
                            <p class="step-label mb-0">Part<br>Information</p>
                        </div>
                        
                        <!-- Arrow -->
                        <div class="flex-grow-1 mx-2">
                            <div class="step-divider"></div>
                        </div>
                        
                        <!-- Step 2 -->
                        <div class="step-item" data-step="2">
                            <div class="step-circle">
                                <i class="ti ti-building"></i>
                            </div>
                            <p class="step-label mb-0">Supplier<br>Information</p>
                        </div>
                        
                        <!-- Arrow -->
                        <div class="flex-grow-1 mx-2">
                            <div class="step-divider"></div>
                        </div>
                        
                        <!-- Step 3 -->
                        <div class="step-item" data-step="3">
                            <div class="step-circle">
                                <i class="ti ti-list"></i>
                            </div>
                            <p class="step-label mb-0">Reason &<br>Results</p>
                        </div>
                        
                        <!-- Arrow -->
                        <div class="flex-grow-1 mx-2">
                            <div class="step-divider"></div>
                        </div>
                        
                        <!-- Step 4 -->
                        <div class="step-item" data-step="4">
                            <div class="step-circle">
                                <i class="ti ti-check"></i>
                            </div>
                            <p class="step-label mb-0">Declaration &<br>Submit</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Content -->
    <div class="row">
        <div class="col-12">
            <form id="pswForm" method="POST" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                
                <!-- Step 1: Part Information & Engineering Changes -->
                <div class="step-content active" id="step-1">
                    <div class="card">
                        <div class="card-header bg-light border-bottom">
                            <h5 class="mb-0"><i class="ti ti-info-circle me-2"></i>Part Information & Engineering Changes</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Part Name -->
                                <div class="col-md-6 mb-3">
                                    <label for="part_name" class="form-label"><span class="text-danger">*</span> Part Name</label>
                                    <input type="text" class="form-control" id="part_name" name="part_name" value="<?php echo e(old('part_name', $psw->part_name ?? '')); ?>" required placeholder="Enter part name">
                                    <small class="form-text text-muted">Required field</small>
                                </div>

                                <!-- Part Number -->
                                <div class="col-md-6 mb-3">
                                    <label for="part_number" class="form-label"><span class="text-danger">*</span> Part Number</label>
                                    <input type="text" class="form-control" id="part_number" name="part_number" value="<?php echo e(old('part_number', $psw->part_number ?? '')); ?>" required placeholder="Enter part number">
                                    <small class="form-text text-muted">Required field</small>
                                </div>

                                <!-- Safety / Government Regulation -->
                                <div class="col-md-6 mb-3">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="safety_government_regulation" name="safety_government_regulation" value="1" <?php echo e(old('safety_government_regulation', $psw->safety_government_regulation ?? false) ? 'checked' : ''); ?>>
                                        <label class="form-check-label" for="safety_government_regulation">
                                            Safety / Government Regulation
                                        </label>
                                    </div>
                                </div>

                                <!-- Engineering Drawing Change Level -->
                                <div class="col-md-6 mb-3">
                                    <label for="engineering_drawing_change_level" class="form-label">Engineering Drawing Change Level</label>
                                    <input type="text" class="form-control" id="engineering_drawing_change_level" name="engineering_drawing_change_level" value="<?php echo e(old('engineering_drawing_change_level', $psw->engineering_drawing_change_level ?? '')); ?>" placeholder="e.g., Level A, Rev 1">
                                </div>

                                <!-- Engineering Drawing Change Date -->
                                <div class="col-md-6 mb-3">
                                    <label for="engineering_drawing_change_date" class="form-label">Engineering Drawing Change Date</label>
                                    <input type="date" class="form-control" id="engineering_drawing_change_date" name="engineering_drawing_change_date" value="<?php echo e(old('engineering_drawing_change_date', $psw->engineering_drawing_change_date?->format('Y-m-d') ?? '')); ?>">
                                </div>

                                <!-- Additional Engineering Changes -->
                                <div class="col-md-6 mb-3">
                                    <label for="additional_engineering_changes" class="form-label">Additional Engineering Changes</label>
                                    <textarea class="form-control" id="additional_engineering_changes" name="additional_engineering_changes" rows="3" placeholder="Describe additional changes"><?php echo e(old('additional_engineering_changes', $psw->additional_engineering_changes ?? '')); ?></textarea>
                                </div>

                                <!-- Additional Engineering Changes Date -->
                                <div class="col-md-6 mb-3">
                                    <label for="additional_engineering_changes_date" class="form-label">Additional Engineering Changes Date</label>
                                    <input type="date" class="form-control" id="additional_engineering_changes_date" name="additional_engineering_changes_date" value="<?php echo e(old('additional_engineering_changes_date', $psw->additional_engineering_changes_date?->format('Y-m-d') ?? '')); ?>">
                                </div>

                                <!-- Shown on Drawing No. -->
                                <div class="col-md-6 mb-3">
                                    <label for="shown_on_drawing_no" class="form-label">Shown on Drawing No.</label>
                                    <input type="text" class="form-control" id="shown_on_drawing_no" name="shown_on_drawing_no" value="<?php echo e(old('shown_on_drawing_no', $psw->shown_on_drawing_no ?? '')); ?>" placeholder="Drawing number">
                                </div>

                                <!-- Purchase Order No. -->
                                <div class="col-md-6 mb-3">
                                    <label for="purchase_order_no" class="form-label">Purchase Order No.</label>
                                    <input type="text" class="form-control" id="purchase_order_no" name="purchase_order_no" value="<?php echo e(old('purchase_order_no', $psw->purchase_order_no ?? '')); ?>" placeholder="PO number">
                                </div>

                                <!-- Weight (kg) -->
                                <div class="col-md-6 mb-3">
                                    <label for="weight_kg" class="form-label">Weight (kg)</label>
                                    <input type="number" class="form-control" id="weight_kg" name="weight_kg" step="0.001" value="<?php echo e(old('weight_kg', $psw->weight_kg ?? '')); ?>" placeholder="Enter weight in kg">
                                </div>

                                <!-- Checking Aid No. -->
                                <div class="col-md-6 mb-3">
                                    <label for="checking_aid_no" class="form-label">Checking Aid No.</label>
                                    <input type="text" class="form-control" id="checking_aid_no" name="checking_aid_no" value="<?php echo e(old('checking_aid_no', $psw->checking_aid_no ?? '')); ?>" placeholder="Checking aid number">
                                </div>

                                <!-- Process Engineering Change Level -->
                                <div class="col-md-6 mb-3">
                                    <label for="process_engineering_change_level" class="form-label">Process Engineering Change Level</label>
                                    <input type="text" class="form-control" id="process_engineering_change_level" name="process_engineering_change_level" value="<?php echo e(old('process_engineering_change_level', $psw->process_engineering_change_level ?? '')); ?>" placeholder="e.g., Level A, Rev 1">
                                </div>

                                <!-- Process Engineering Change Date -->
                                <div class="col-md-6 mb-3">
                                    <label for="process_engineering_change_date" class="form-label">Process Engineering Change Date</label>
                                    <input type="date" class="form-control" id="process_engineering_change_date" name="process_engineering_change_date" value="<?php echo e(old('process_engineering_change_date', $psw->process_engineering_change_date?->format('Y-m-d') ?? '')); ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Supplier & Submission Information -->
                <div class="step-content" id="step-2">
                    <div class="card">
                        <div class="card-header bg-light border-bottom">
                            <h5 class="mb-0"><i class="ti ti-building me-2"></i>Supplier & Submission Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Supplier Name -->
                                <div class="col-md-6 mb-3">
                                    <label for="supplier_name" class="form-label"><span class="text-danger">*</span> Supplier Name</label>
                                    <input type="text" class="form-control" id="supplier_name" name="supplier_name" value="<?php echo e(old('supplier_name', $psw->supplier_name ?? '')); ?>" required placeholder="Enter supplier name">
                                </div>

                                <!-- Supplier Code -->
                                <div class="col-md-6 mb-3">
                                    <label for="supplier_code" class="form-label"><span class="text-danger">*</span> Supplier Code</label>
                                    <input type="text" class="form-control" id="supplier_code" name="supplier_code" value="<?php echo e(old('supplier_code', $psw->supplier_code ?? '')); ?>" required placeholder="Enter supplier code">
                                </div>

                                <!-- Street Address -->
                                <div class="col-md-12 mb-3">
                                    <label for="street_address" class="form-label">Street Address</label>
                                    <input type="text" class="form-control" id="street_address" name="street_address" value="<?php echo e(old('street_address', $psw->street_address ?? '')); ?>" placeholder="Enter street address">
                                </div>

                                <!-- City -->
                                <div class="col-md-4 mb-3">
                                    <label for="city" class="form-label">City</label>
                                    <input type="text" class="form-control" id="city" name="city" value="<?php echo e(old('city', $psw->city ?? '')); ?>" placeholder="City">
                                </div>

                                <!-- State -->
                                <div class="col-md-4 mb-3">
                                    <label for="state" class="form-label">State</label>
                                    <input type="text" class="form-control" id="state" name="state" value="<?php echo e(old('state', $psw->state ?? '')); ?>" placeholder="State">
                                </div>

                                <!-- Zip -->
                                <div class="col-md-4 mb-3">
                                    <label for="zip" class="form-label">Zip Code</label>
                                    <input type="text" class="form-control" id="zip" name="zip" value="<?php echo e(old('zip', $psw->zip ?? '')); ?>" placeholder="Zip code">
                                </div>

                                <!-- Customer Name / Division -->
                                <div class="col-md-6 mb-3">
                                    <label for="customer_name_division" class="form-label">Customer Name / Division</label>
                                    <input type="text" class="form-control" id="customer_name_division" name="customer_name_division" value="<?php echo e(old('customer_name_division', $psw->customer_name_division ?? '')); ?>" placeholder="Customer name / division">
                                </div>

                                <!-- Buyer / Buyer Code -->
                                <div class="col-md-6 mb-3">
                                    <label for="buyer_buyer_code" class="form-label">Buyer / Buyer Code</label>
                                    <input type="text" class="form-control" id="buyer_buyer_code" name="buyer_buyer_code" value="<?php echo e(old('buyer_buyer_code', $psw->buyer_buyer_code ?? '')); ?>" placeholder="Buyer / buyer code">
                                </div>

                                <!-- Application -->
                                <div class="col-md-12 mb-3">
                                    <label for="application" class="form-label">Application</label>
                                    <textarea class="form-control" id="application" name="application" rows="3" placeholder="Describe the application"><?php echo e(old('application', $psw->application ?? '')); ?></textarea>
                                </div>

                                <!-- Submission Information (Checkboxes) -->
                                <div class="col-md-12 mb-3">
                                    <label class="form-label d-block mb-2">Submission Information</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="submission_first_time" name="submission_information[]" value="first_time">
                                        <label class="form-check-label" for="submission_first_time">First time submission</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="submission_change" name="submission_information[]" value="change">
                                        <label class="form-check-label" for="submission_change">Change submission</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="submission_re_approval" name="submission_information[]" value="re_approval">
                                        <label class="form-check-label" for="submission_re_approval">Re-approval submission</label>
                                    </div>
                                </div>

                                <!-- Contains Prohibited Substances -->
                                <div class="col-md-6 mb-3">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="contains_prohibited_substances" name="contains_prohibited_substances" value="1" <?php echo e(old('contains_prohibited_substances', $psw->contains_prohibited_substances ?? false) ? 'checked' : ''); ?>>
                                        <label class="form-check-label" for="contains_prohibited_substances">
                                            Contains Prohibited Substances
                                        </label>
                                    </div>
                                </div>

                                <!-- Plastic Has ISO Marking -->
                                <div class="col-md-6 mb-3">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="plastic_has_iso_marking" name="plastic_has_iso_marking" value="1" <?php echo e(old('plastic_has_iso_marking', $psw->plastic_has_iso_marking ?? false) ? 'checked' : ''); ?>>
                                        <label class="form-check-label" for="plastic_has_iso_marking">
                                            Plastic Has ISO Marking
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Reason & Results -->
                <div class="step-content" id="step-3">
                    <div class="card">
                        <div class="card-header bg-light border-bottom">
                            <h5 class="mb-0"><i class="ti ti-list me-2"></i>Reason for Submission & Results</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Reason for Submission -->
                                <div class="col-md-12 mb-3">
                                    <label class="form-label d-block mb-2">Reason for Submission</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="reason_initial" name="reason_for_submission[]" value="initial">
                                        <label class="form-check-label" for="reason_initial">Initial submission</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="reason_design_change" name="reason_for_submission[]" value="design_change">
                                        <label class="form-check-label" for="reason_design_change">Design change</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="reason_process_change" name="reason_for_submission[]" value="process_change">
                                        <label class="form-check-label" for="reason_process_change">Process change</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="reason_supplier_change" name="reason_for_submission[]" value="supplier_change">
                                        <label class="form-check-label" for="reason_supplier_change">Supplier change</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="reason_other" name="reason_for_submission[]" value="other">
                                        <label class="form-check-label" for="reason_other">Other</label>
                                    </div>
                                </div>

                                <!-- Reason Other Text -->
                                <div class="col-md-12 mb-3">
                                    <label for="reason_other_text" class="form-label">Other Reason Details</label>
                                    <textarea class="form-control" id="reason_other_text" name="reason_other_text" rows="3" placeholder="If 'Other' is selected, explain here"><?php echo e(old('reason_other_text', $psw->reason_other_text ?? '')); ?></textarea>
                                </div>

                                <!-- Submission Results -->
                                <div class="col-md-12 mb-3">
                                    <label class="form-label d-block mb-2">Submission Results</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="result_pass" name="submission_results[]" value="pass">
                                        <label class="form-check-label" for="result_pass">Pass - meets all requirements</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="result_conditional" name="submission_results[]" value="conditional">
                                        <label class="form-check-label" for="result_conditional">Conditional pass with minor deviations</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="result_fail" name="submission_results[]" value="fail">
                                        <label class="form-check-label" for="result_fail">Fail - does not meet requirements</label>
                                    </div>
                                </div>

                                <!-- Meets All Requirements -->
                                <div class="col-md-12 mb-3">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="meets_all_requirements" name="meets_all_requirements" value="1" <?php echo e(old('meets_all_requirements', $psw->meets_all_requirements ?? false) ? 'checked' : ''); ?>>
                                        <label class="form-check-label" for="meets_all_requirements">
                                            Meets All Requirements
                                        </label>
                                    </div>
                                </div>

                                <!-- Explanation if Not Meet -->
                                <div class="col-md-12 mb-3">
                                    <label for="explanation_if_not_meet" class="form-label">Explanation (if not meet)</label>
                                    <textarea class="form-control" id="explanation_if_not_meet" name="explanation_if_not_meet" rows="4" placeholder="Explain any deviations or failures"><?php echo e(old('explanation_if_not_meet', $psw->explanation_if_not_meet ?? '')); ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 4: Declaration & Signature -->
                <div class="step-content" id="step-4">
                    <div class="card">
                        <div class="card-header bg-light border-bottom">
                            <h5 class="mb-0"><i class="ti ti-check me-2"></i>Declaration & Signature</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Explanation / Comments -->
                                <div class="col-md-12 mb-3">
                                    <label for="explanation_comments" class="form-label">Explanation / Comments</label>
                                    <textarea class="form-control" id="explanation_comments" name="explanation_comments" rows="4" placeholder="Any additional comments or explanations"><?php echo e(old('explanation_comments', $psw->explanation_comments ?? '')); ?></textarea>
                                </div>

                                <!-- Print Name -->
                                <div class="col-md-6 mb-3">
                                    <label for="print_name" class="form-label"><span class="text-danger">*</span> Print Name</label>
                                    <input type="text" class="form-control" id="print_name" name="print_name" value="<?php echo e(old('print_name', $psw->print_name ?? '')); ?>" required placeholder="Enter your name">
                                </div>

                                <!-- Title -->
                                <div class="col-md-6 mb-3">
                                    <label for="title" class="form-label"><span class="text-danger">*</span> Title</label>
                                    <input type="text" class="form-control" id="title" name="title" value="<?php echo e(old('title', $psw->title ?? '')); ?>" required placeholder="Enter your title">
                                </div>

                                <!-- Phone No. -->
                                <div class="col-md-6 mb-3">
                                    <label for="phone_no" class="form-label">Phone No.</label>
                                    <input type="tel" class="form-control" id="phone_no" name="phone_no" value="<?php echo e(old('phone_no', $psw->phone_no ?? '')); ?>" placeholder="Phone number">
                                </div>

                                <!-- Fax No. -->
                                <div class="col-md-6 mb-3">
                                    <label for="fax_no" class="form-label">Fax No.</label>
                                    <input type="tel" class="form-control" id="fax_no" name="fax_no" value="<?php echo e(old('fax_no', $psw->fax_no ?? '')); ?>" placeholder="Fax number">
                                </div>

                                <!-- Signature Date -->
                                <div class="col-md-6 mb-3">
                                    <label for="signature_date" class="form-label"><span class="text-danger">*</span> Signature Date</label>
                                    <input type="date" class="form-control" id="signature_date" name="signature_date" value="<?php echo e(old('signature_date', $psw->signature_date?->format('Y-m-d') ?? '')); ?>" required>
                                </div>

                                <!-- Signature Upload -->
                                <div class="col-md-6 mb-3">
                                    <label for="supplier_authorized_signature" class="form-label"><span class="text-danger">*</span> Authorized Signature (PDF/JPG/PNG)</label>
                                    <div class="mb-2">
                                        <input type="file" class="form-control" id="supplier_authorized_signature" name="supplier_authorized_signature" accept=".pdf,.jpg,.jpeg,.png" required>
                                        <small class="form-text text-muted">Max file size: 5MB</small>
                                    </div>
                                    <?php if($psw->supplier_authorized_signature): ?>
                                        <div class="alert alert-info mb-0">
                                            <i class="ti ti-check-circle me-2"></i>Signature file uploaded
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Certification -->
                            <div class="alert alert-warning mt-4" role="alert">
                                <i class="ti ti-alert-triangle me-2"></i>
                                <strong>Important Declaration:</strong><br>
                                I hereby declare that the information provided in this Part Submission Warrant is true, accurate, and complete to the best of my knowledge. I understand that any false or misleading information may result in rejection of this submission.
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
                                        Step <span id="currentStep">1</span> of 4
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

    .form-control, .form-check-input {
        border-color: #E5E7EB;
        border-radius: 8px;
        padding: 0.65rem 0.875rem;
    }

    .form-control:focus {
        border-color: #2563EB;
        box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.25);
    }

    .form-check-input:focus {
        border-color: #2563EB;
        box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.25);
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentStep = 1;
    const totalSteps = 4;

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
        const formData = new FormData(document.getElementById('pswForm'));
        
        fetch('/api/part-submission-warrants/<?php echo e($psw->id); ?>/draft', {
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
        const formData = new FormData(document.getElementById('pswForm'));
        
        fetch('/api/part-submission-warrants/<?php echo e($psw->id); ?>/submit', {
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

<?php echo $__env->make('layouts.supplier', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\KERJAAN\akebono-approval\resources\views/part-submission-warrant/form.blade.php ENDPATH**/ ?>