@extends('layouts.supplier')

@section('title', 'Part Submission Warrant (PSW)')

@section('content')
<div class="container py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h3 class="mb-3">Part Submission Warrant (PSW) Form</h3>
            <p class="text-muted">Complete all required fields marked with <span class="text-danger">*</span></p>
        </div>
    </div>

    <!-- Form -->
    <form id="pswForm" method="POST" enctype="multipart/form-data">
        @csrf

        <!-- General Information -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="mb-3"><i class="ti ti-info-circle me-2"></i>General Information</h5>
                <div class="row">
                    <!-- Part Name -->
                    <div class="col-md-6 mb-3">
                        <label for="part_name" class="form-label">Part Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control bg-light" id="part_name" name="part_name" required readonly>
                        <small class="text-muted">Auto-populated from request</small>
                    </div>

                    <!-- Part Number -->
                    <div class="col-md-6 mb-3">
                        <label for="part_number" class="form-label">Part Number <span class="text-danger">*</span></label>
                        <input type="text" class="form-control bg-light" id="part_number" name="part_number" required readonly>
                        <small class="text-muted">Auto-populated from request</small>
                    </div>

                    <!-- Safety/Government Regulation -->
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Safety and/or Government Regulation <span class="text-danger">*</span></label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="safety_government_regulation" id="safety_yes" value="1" required>
                            <label class="form-check-label" for="safety_yes">Yes</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="safety_government_regulation" id="safety_no" value="0" required>
                            <label class="form-check-label" for="safety_no">No</label>
                        </div>
                    </div>

                    <!-- Engineering Drawing Change Level -->
                    <div class="col-md-4 mb-3">
                        <label for="engineering_drawing_change_level" class="form-label">Engineering Drawing Change Level</label>
                        <input type="text" class="form-control" id="engineering_drawing_change_level" name="engineering_drawing_change_level">
                    </div>

                    <!-- Dated -->
                    <div class="col-md-2 mb-3">
                        <label for="engineering_drawing_change_date" class="form-label">Dated</label>
                        <input type="date" class="form-control" id="engineering_drawing_change_date" name="engineering_drawing_change_date">
                    </div>

                    <!-- Additional Engineering Changes -->
                    <div class="col-md-4 mb-3">
                        <label for="additional_engineering_changes" class="form-label">Additional Engineering Changes</label>
                        <input type="text" class="form-control" id="additional_engineering_changes" name="additional_engineering_changes">
                    </div>

                    <!-- Dated -->
                    <div class="col-md-2 mb-3">
                        <label for="additional_engineering_changes_date" class="form-label">Dated</label>
                        <input type="date" class="form-control" id="additional_engineering_changes_date" name="additional_engineering_changes_date">
                    </div>

                    <!-- Shown on Drawing No -->
                    <div class="col-md-4 mb-3">
                        <label for="shown_on_drawing_no" class="form-label">Shown on Drawing No.</label>
                        <input type="text" class="form-control" id="shown_on_drawing_no" name="shown_on_drawing_no">
                    </div>

                    <!-- Purchase Order No -->
                    <div class="col-md-4 mb-3">
                        <label for="purchase_order_no" class="form-label">Purchase Order No.</label>
                        <input type="text" class="form-control" id="purchase_order_no" name="purchase_order_no">
                    </div>

                    <!-- Weight (kg) -->
                    <div class="col-md-4 mb-3">
                        <label for="weight_kg" class="form-label">Weight (kg)</label>
                        <input type="number" step="0.001" min="0" class="form-control" id="weight_kg" name="weight_kg">
                    </div>

                    <!-- Checking Aid No -->
                    <div class="col-md-4 mb-3">
                        <label for="checking_aid_no" class="form-label">Checking Aid No.</label>
                        <input type="text" class="form-control" id="checking_aid_no" name="checking_aid_no">
                    </div>

                    <!-- Process Engineering Change Level -->
                    <div class="col-md-4 mb-3">
                        <label for="process_engineering_change_level" class="form-label">Process Engineering Change Level</label>
                        <input type="text" class="form-control" id="process_engineering_change_level" name="process_engineering_change_level">
                    </div>

                    <!-- Dated -->
                    <div class="col-md-4 mb-3">
                        <label for="process_engineering_change_date" class="form-label">Dated</label>
                        <input type="date" class="form-control" id="process_engineering_change_date" name="process_engineering_change_date">
                    </div>
                </div>
            </div>
        </div>

        <!-- Supplier & Submission Information -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="mb-3"><i class="ti ti-building me-2"></i>Supplier & Submission Information</h5>
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="mb-3">Supplier Manufacturing Information</h6>
                        
                        <!-- Supplier Name & Code -->
                        <div class="mb-3">
                            <label for="supplier_name" class="form-label">Supplier Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control bg-light" id="supplier_name" name="supplier_name" required readonly>
                        </div>
                        
                        <div class="mb-3">
                            <label for="supplier_code" class="form-label">Supplier Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control bg-light" id="supplier_code" name="supplier_code" required readonly>
                        </div>

                        <!-- Street Address -->
                        <div class="mb-3">
                            <label for="street_address" class="form-label">Street Address</label>
                            <input type="text" class="form-control bg-light" id="street_address" name="street_address" readonly>
                        </div>

                        <!-- City, State, Zip -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="city" class="form-label">City</label>
                                <input type="text" class="form-control bg-light" id="city" name="city" readonly>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="state" class="form-label">State</label>
                                <input type="text" class="form-control bg-light" id="state" name="state" readonly>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="zip" class="form-label">Zip</label>
                                <input type="text" class="form-control bg-light" id="zip" name="zip" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <h6 class="mb-3">Submission Information</h6>
                        
                        <!-- Submission Information Checkboxes -->
                        <div class="mb-3">
                            <label class="form-label">Select all that apply:</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="submission_dimensional" name="submission_information[]" value="dimensional">
                                <label class="form-check-label" for="submission_dimensional">Dimensional</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="submission_materials" name="submission_information[]" value="materials_functional">
                                <label class="form-check-label" for="submission_materials">Materials/Functional</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="submission_appearance" name="submission_information[]" value="appearance">
                                <label class="form-check-label" for="submission_appearance">Appearance</label>
                            </div>
                        </div>

                        <!-- Customer Name/Division -->
                        <div class="mb-3">
                            <label for="customer_name_division" class="form-label">Customer Name/Division</label>
                            <input type="text" class="form-control bg-light" id="customer_name_division" name="customer_name_division" value="PT. AAIJ / Purchasing / QA" readonly>
                        </div>

                        <!-- Buyer/Buyer Code -->
                        <div class="mb-3">
                            <label for="buyer_buyer_code" class="form-label">Buyer/Buyer Code</label>
                            <input type="text" class="form-control" id="buyer_buyer_code" name="buyer_buyer_code">
                        </div>

                        <!-- Application -->
                        <div class="mb-3">
                            <label for="application" class="form-label">Application</label>
                            <input type="text" class="form-control" id="application" name="application">
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <hr>
                <h6 class="mb-3">Notes:</h6>
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Does this part contain any restricted or reportable substances?</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="contains_prohibited_substances" id="substance_yes" value="1">
                            <label class="form-check-label" for="substance_yes">Yes</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="contains_prohibited_substances" id="substance_no" value="0" checked>
                            <label class="form-check-label" for="substance_no">No</label>
                        </div>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="form-label">Are plastic parts identified with appropriate ISO marking codes?</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="plastic_has_iso_marking" id="iso_yes" value="1">
                            <label class="form-check-label" for="iso_yes">Yes</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="plastic_has_iso_marking" id="iso_no" value="0" checked>
                            <label class="form-check-label" for="iso_no">No</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reason for Submission & Results -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="mb-3"><i class="ti ti-clipboard-list me-2"></i>Reason for Submission & Results</h5>
                <h6 class="mb-3">Reason for Submission (Select all that apply)</h6>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="reason_initial" name="reason_for_submission[]" value="initial_submission">
                            <label class="form-check-label" for="reason_initial">Initial Submission</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="reason_eng_change" name="reason_for_submission[]" value="engineering_changes">
                            <label class="form-check-label" for="reason_eng_change">Engineering Change(s)</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="reason_tooling" name="reason_for_submission[]" value="tooling_transfer">
                            <label class="form-check-label" for="reason_tooling">Tooling: Transfer, Replacement, Refurbishment, or additional</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="reason_correction" name="reason_for_submission[]" value="correction_discrepancy">
                            <label class="form-check-label" for="reason_correction">Correction of Discrepancy</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="reason_inactive" name="reason_for_submission[]" value="tooling_inactive">
                            <label class="form-check-label" for="reason_inactive">Tooling inactive > than 1 year</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="reason_optional" name="reason_for_submission[]" value="change_optional_construction">
                            <label class="form-check-label" for="reason_optional">Change to Optional Construction or Material</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="reason_sub_supplier" name="reason_for_submission[]" value="sub_supplier_change">
                            <label class="form-check-label" for="reason_sub_supplier">Sub-Supplier or Material Source Change</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="reason_processing" name="reason_for_submission[]" value="change_part_processing">
                            <label class="form-check-label" for="reason_processing">Change in Part Processing</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="reason_additional" name="reason_for_submission[]" value="parts_additional_location">
                            <label class="form-check-label" for="reason_additional">Parts Produced at Additional Location</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="reason_other" name="reason_for_submission[]" value="other">
                            <label class="form-check-label" for="reason_other">Other - please specify</label>
                        </div>
                    </div>
                </div>

                <!-- REMOVED: reason_other_text field - column dropped from database
                <div class="mb-3 mt-3">
                    <label for="reason_other_text" class="form-label">Other Reason (if selected above)</label>
                    <textarea class="form-control" id="reason_other_text" name="reason_other_text" rows="2"></textarea>
                </div>
                -->

                <hr>

                <h6 class="mb-3">Submission Results</h6>
                <p class="text-muted">The results for:</p>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="result_dimensional" name="submission_results[]" value="dimensional_measurements">
                            <label class="form-check-label" for="result_dimensional">Dimensional measurements</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="result_appearance" name="submission_results[]" value="appearance_criteria">
                            <label class="form-check-label" for="result_appearance">Appearance criteria</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="result_material" name="submission_results[]" value="material_functional_tests">
                            <label class="form-check-label" for="result_material">Material and functional tests</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="result_statistical" name="submission_results[]" value="statistical_process_evaluation">
                            <label class="form-check-label" for="result_statistical">Statistical process evaluation</label>
                        </div>
                    </div>
                </div>

                <div class="mb-3 mt-3">
                    <label class="form-label">These results meet all drawing and specification requirements: <span class="text-danger">*</span></label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="meets_all_requirements" id="meets_yes" value="1" required>
                        <label class="form-check-label" for="meets_yes">Yes</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="meets_all_requirements" id="meets_no" value="0" required>
                        <label class="form-check-label" for="meets_no">No (if "No" - Explanation Required)</label>
                    </div>
                </div>

                <div class="mb-3" id="explanation_if_not_meet_container" style="display: none;">
                    <label for="explanation_if_not_meet" class="form-label">Explanation (Required when answer is "No") <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="explanation_if_not_meet" name="explanation_if_not_meet" rows="3" placeholder="Please explain why the results do not meet all requirements..."></textarea>
                </div>

            </div>
        </div>

        <!-- Declaration -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="mb-3"><i class="ti ti-file-signature me-2"></i>Declaration</h5>
                <div class="alert alert-info">
                    <strong>Declaration:</strong> I hereby affirm that the samples represented by this warrant are representative of our parts, and have been made to the applicable Customer drawings and specification and are made from specified materials on regular production tooling, regular production process. I have noted any deviations from this declaration below:
                </div>

                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="explanation_comments" class="form-label">Explanation/Comments</label>
                        <textarea class="form-control" id="explanation_comments" name="explanation_comments" rows="3"></textarea>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="print_name" class="form-label">Print Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="print_name" name="print_name" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="phone_no" class="form-label">Phone No.</label>
                        <input type="text" class="form-control" id="phone_no" name="phone_no">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="fax_no" class="form-label">Fax No.</label>
                        <input type="text" class="form-control" id="fax_no" name="fax_no">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="signature_date" class="form-label">Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="signature_date" name="signature_date" required>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-center gap-3">
                    <button type="button" class="btn btn-cyan btn-lg" id="saveDraftBtn" onclick="saveDraft()" disabled>
                        <i class="ti ti-file-text"></i> Save Draft
                    </button>
                    <button type="button" class="btn btn-blue btn-lg" id="submitBtn" onclick="submitForm()" disabled>
                        <i class="ti ti-send"></i> Submit
                    </button>
                </div>
                <div id="formLoadingStatus" class="text-center mt-2">
                    <small class="text-warning"><i class="ti ti-loader-3"></i> Loading form...</small>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
// ===== GLOBAL VARIABLES =====
window.partSubmissionWarrantId = null;

// ===== FORM INITIALIZATION =====
function initializeForm() {
    console.log('🚀 Initializing PSW form');
    
    const urlPath = window.location.pathname;
    const token = urlPath.split('/').pop();
    
    if (!token) {
        Swal.fire('Error', 'Invalid token', 'error');
        return;
    }
    
    console.log('📝 Token:', token);
    
    fetch(`/api/part-submission-warrants/${token}`)
        .then(response => {
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            return response.json();
        })
        .then(data => {
            console.log('✅ Form data loaded:', data);
            
            window.partSubmissionWarrantId = data.id;
            populateFormData(data);
            
            document.getElementById('saveDraftBtn').disabled = false;
            document.getElementById('submitBtn').disabled = false;
            document.getElementById('formLoadingStatus').innerHTML = '<small class="text-success"><i class="ti ti-check"></i> Form loaded successfully</small>';
            
            setupAutoSave();
        })
        .catch(error => {
            console.error('❌ Error loading form:', error);
            document.getElementById('formLoadingStatus').innerHTML = '<small class="text-danger"><i class="ti ti-alert-circle"></i> Error loading form</small>';
            Swal.fire('Error', 'Failed to load form data: ' + error.message, 'error');
        });
}

function populateFormData(data) {
    // Step 1: General Information
    console.log('🔍 populateFormData received:', data);
    console.log('🔍 part_number value:', data.part_number);
    console.log('🔍 part_name value:', data.part_name);
    
    if (data.part_name) document.getElementById('part_name').value = data.part_name;
    if (data.part_number) document.getElementById('part_number').value = data.part_number;
    
    if (data.safety_government_regulation !== undefined) {
        if (data.safety_government_regulation) {
            document.getElementById('safety_yes').checked = true;
        } else {
            document.getElementById('safety_no').checked = true;
        }
    }
    
    if (data.engineering_drawing_change_level) document.getElementById('engineering_drawing_change_level').value = data.engineering_drawing_change_level;
    if (data.engineering_drawing_change_date) document.getElementById('engineering_drawing_change_date').value = data.engineering_drawing_change_date;
    if (data.additional_engineering_changes) document.getElementById('additional_engineering_changes').value = data.additional_engineering_changes;
    if (data.additional_engineering_changes_date) document.getElementById('additional_engineering_changes_date').value = data.additional_engineering_changes_date;
    if (data.shown_on_drawing_no) document.getElementById('shown_on_drawing_no').value = data.shown_on_drawing_no;
    if (data.purchase_order_no) document.getElementById('purchase_order_no').value = data.purchase_order_no;
    if (data.weight_kg) document.getElementById('weight_kg').value = data.weight_kg;
    if (data.checking_aid_no) document.getElementById('checking_aid_no').value = data.checking_aid_no;
    if (data.process_engineering_change_level) document.getElementById('process_engineering_change_level').value = data.process_engineering_change_level;
    if (data.process_engineering_change_date) document.getElementById('process_engineering_change_date').value = data.process_engineering_change_date;
    
    // Step 2: Supplier & Submission
    if (data.supplier_name) document.getElementById('supplier_name').value = data.supplier_name;
    if (data.supplier_code) document.getElementById('supplier_code').value = data.supplier_code;
    if (data.street_address) document.getElementById('street_address').value = data.street_address;
    if (data.city) document.getElementById('city').value = data.city;
    if (data.state) document.getElementById('state').value = data.state;
    if (data.zip) document.getElementById('zip').value = data.zip;
    if (data.customer_name_division) document.getElementById('customer_name_division').value = data.customer_name_division;
    if (data.buyer_buyer_code) document.getElementById('buyer_buyer_code').value = data.buyer_buyer_code;
    if (data.application) document.getElementById('application').value = data.application;
    
    // Submission Information
    if (data.submission_information && Array.isArray(data.submission_information)) {
        data.submission_information.forEach(value => {
            const checkbox = document.querySelector(`input[name="submission_information[]"][value="${value}"]`);
            if (checkbox) checkbox.checked = true;
        });
    }
    
    // Notes
    if (data.contains_prohibited_substances !== undefined) {
        if (data.contains_prohibited_substances) {
            document.getElementById('substance_yes').checked = true;
        } else {
            document.getElementById('substance_no').checked = true;
        }
    }
    
    if (data.plastic_has_iso_marking !== undefined) {
        if (data.plastic_has_iso_marking) {
            document.getElementById('iso_yes').checked = true;
        } else {
            document.getElementById('iso_no').checked = true;
        }
    }
    
    // Step 3: Reason & Results
    if (data.reason_for_submission && Array.isArray(data.reason_for_submission)) {
        data.reason_for_submission.forEach(value => {
            const checkbox = document.querySelector(`input[name="reason_for_submission[]"][value="${value}"]`);
            if (checkbox) checkbox.checked = true;
        });
    }
    
    // REMOVED: reason_other_text - column dropped from database
    // if (data.reason_other_text) document.getElementById('reason_other_text').value = data.reason_other_text;
    
    if (data.submission_results && Array.isArray(data.submission_results)) {
        data.submission_results.forEach(value => {
            const checkbox = document.querySelector(`input[name="submission_results[]"][value="${value}"]`);
            if (checkbox) checkbox.checked = true;
        });
    }
    
    if (data.meets_all_requirements !== undefined) {
        if (data.meets_all_requirements) {
            document.getElementById('meets_yes').checked = true;
            document.getElementById('explanation_if_not_meet_container').style.display = 'none';
        } else {
            document.getElementById('meets_no').checked = true;
            document.getElementById('explanation_if_not_meet_container').style.display = 'block';
        }
    }
    
    if (data.explanation_if_not_meet) document.getElementById('explanation_if_not_meet').value = data.explanation_if_not_meet;
    
    // Step 4: Declaration
    if (data.explanation_comments) document.getElementById('explanation_comments').value = data.explanation_comments;
    if (data.print_name) document.getElementById('print_name').value = data.print_name;
    if (data.title) document.getElementById('title').value = data.title;
    if (data.phone_no) document.getElementById('phone_no').value = data.phone_no;
    if (data.fax_no) document.getElementById('fax_no').value = data.fax_no;
    if (data.signature_date) document.getElementById('signature_date').value = data.signature_date;
}

function collectFormData() {
    return {
        // Step 1
        part_name: document.getElementById('part_name').value,
        part_number: document.getElementById('part_number').value,
        safety_government_regulation: document.querySelector('input[name="safety_government_regulation"]:checked')?.value === '1',
        engineering_drawing_change_level: document.getElementById('engineering_drawing_change_level').value,
        engineering_drawing_change_date: document.getElementById('engineering_drawing_change_date').value,
        additional_engineering_changes: document.getElementById('additional_engineering_changes').value,
        additional_engineering_changes_date: document.getElementById('additional_engineering_changes_date').value,
        shown_on_drawing_no: document.getElementById('shown_on_drawing_no').value,
        purchase_order_no: document.getElementById('purchase_order_no').value,
        weight_kg: document.getElementById('weight_kg').value,
        checking_aid_no: document.getElementById('checking_aid_no').value,
        process_engineering_change_level: document.getElementById('process_engineering_change_level').value,
        process_engineering_change_date: document.getElementById('process_engineering_change_date').value,
        // Step 2
        supplier_name: document.getElementById('supplier_name').value,
        supplier_code: document.getElementById('supplier_code').value,
        street_address: document.getElementById('street_address').value,
        city: document.getElementById('city').value,
        state: document.getElementById('state').value,
        zip: document.getElementById('zip').value,
        customer_name_division: document.getElementById('customer_name_division').value,
        buyer_buyer_code: document.getElementById('buyer_buyer_code').value,
        application: document.getElementById('application').value,
        submission_information: Array.from(document.querySelectorAll('input[name="submission_information[]"]:checked')).map(cb => cb.value),
        contains_prohibited_substances: document.querySelector('input[name="contains_prohibited_substances"]:checked')?.value === '1',
        plastic_has_iso_marking: document.querySelector('input[name="plastic_has_iso_marking"]:checked')?.value === '1',
        // Step 3
        reason_for_submission: Array.from(document.querySelectorAll('input[name="reason_for_submission[]"]:checked')).map(cb => cb.value),
        // REMOVED: reason_other_text - column dropped from database
        // reason_other_text: document.getElementById('reason_other_text').value,
        submission_results: Array.from(document.querySelectorAll('input[name="submission_results[]"]:checked')).map(cb => cb.value),
        meets_all_requirements: document.querySelector('input[name="meets_all_requirements"]:checked')?.value === '1',
        explanation_if_not_meet: document.getElementById('explanation_if_not_meet').value,
        // Step 4
        explanation_comments: document.getElementById('explanation_comments').value,
        print_name: document.getElementById('print_name').value,
        title: document.getElementById('title').value,
        phone_no: document.getElementById('phone_no').value,
        fax_no: document.getElementById('fax_no').value,
        signature_date: document.getElementById('signature_date').value
    };
}

// ===== AUTO-SAVE SETUP =====
let autoSaveTimer = null;
function setupAutoSave() {
    const formInputs = document.querySelectorAll('#pswForm input, #pswForm textarea, #pswForm select');
    
    formInputs.forEach(input => {
        input.addEventListener('change', () => {
            if (autoSaveTimer) clearTimeout(autoSaveTimer);
            autoSaveTimer = setTimeout(() => {
                console.log('💾 Auto-saving...');
                saveDraft(true);
            }, 2000);
        });
    });
}

// ===== FORM SUBMISSION =====
function saveDraft(silent = false) {
    if (!window.partSubmissionWarrantId) {
        if (!silent) Swal.fire('Error', 'Form not fully loaded. Please refresh the page and try again.', 'error');
        return;
    }

    const formData = collectFormData();
    formData.status = 'DRAFT';
    
    fetch(`/api/part-submission-warrants/${window.partSubmissionWarrantId}/draft`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(formData)
    }).then(r => {
        if (!r.ok) throw new Error(`HTTP ${r.status}`);
        return r.json();
    }).then(result => {
        if (result.success) {
            if (!silent) Swal.fire('Success', 'Draft saved successfully!', 'success');
            else console.log('✅ Auto-save successful');
        } else {
            if (!silent) Swal.fire('Error', result.message || 'Failed to save draft', 'error');
        }
    }).catch(error => {
        console.error('Error saving draft:', error);
        if (!silent) Swal.fire('Error', 'Failed to save draft: ' + error.message, 'error');
    });
}

function validate() {
    const required = [
        {id: 'part_name', label: 'Part Name'},
        {id: 'part_number', label: 'Part Number'},
        {id: 'supplier_name', label: 'Supplier Name'},
        {id: 'supplier_code', label: 'Supplier Code'},
        {id: 'print_name', label: 'Print Name'},
        {id: 'title', label: 'Title'},
        {id: 'signature_date', label: 'Signature Date'}
    ];
    
    for (const field of required) {
        const element = document.getElementById(field.id);
        if (!element.value) {
            Swal.fire('Validation Error', `${field.label} is required`, 'warning');
            element.focus();
            return false;
        }
    }
    
    // Validate radio buttons
    if (!document.querySelector('input[name="safety_government_regulation"]:checked')) {
        Swal.fire('Validation Error', 'Safety and/or Government Regulation selection is required', 'warning');
        return false;
    }
    
    if (!document.querySelector('input[name="meets_all_requirements"]:checked')) {
        Swal.fire('Validation Error', 'Meets All Requirements selection is required', 'warning');
        return false;
    }
    
    // Validate explanation if "No" is selected for meets_all_requirements
    const meetsAllRequirements = document.querySelector('input[name="meets_all_requirements"]:checked');
    if (meetsAllRequirements && meetsAllRequirements.value === '0') {
        const explanation = document.getElementById('explanation_if_not_meet');
        if (!explanation.value.trim()) {
            Swal.fire('Validation Error', 'Explanation is required when results do not meet all requirements', 'warning');
            explanation.focus();
            return false;
        }
    }
    
    return true;
}

function submitForm() {
    if (!validate()) return;

    if (!window.partSubmissionWarrantId) {
        Swal.fire('Error', 'Form not fully loaded. Please refresh the page and try again.', 'error');
        return;
    }

    const formData = collectFormData();
    formData.status = 'SUBMITTED';
    
    // DEBUG: Log checkbox data
    console.log('📦 PSW SUBMIT - Full Form Data:', formData);
    console.log('📋 PSW SUBMIT - Checkbox Data:', {
        submission_information: formData.submission_information,
        submission_info_count: formData.submission_information?.length || 0,
        reason_for_submission: formData.reason_for_submission,
        reason_count: formData.reason_for_submission?.length || 0,
        submission_results: formData.submission_results,
        results_count: formData.submission_results?.length || 0
    });
    
    // ALERT if checkboxes are empty
    if (!formData.submission_information || formData.submission_information.length === 0) {
        console.warn('⚠️  WARNING: submission_information is empty!');
    }
    if (!formData.reason_for_submission || formData.reason_for_submission.length === 0) {
        console.warn('⚠️  WARNING: reason_for_submission is empty!');
    }
    if (!formData.submission_results || formData.submission_results.length === 0) {
        console.warn('⚠️  WARNING: submission_results is empty!');
    }
    
    fetch(`/api/part-submission-warrants/${window.partSubmissionWarrantId}/submit`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(formData)
    }).then(r => {
        if (!r.ok) throw new Error(`HTTP ${r.status}`);
        return r.json();
    }).then(result => {
        if (result.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'Part Submission Warrant submitted successfully',
                confirmButtonText: 'OK'
            }).then(() => {
                window.location.href = result.redirect || '/supplier/dashboard';
            });
        } else {
            Swal.fire('Error', result.message || 'Failed to submit form', 'error');
        }
    }).catch(error => {
        console.error('Error submitting form:', error);
        Swal.fire('Error', 'Failed to submit form: ' + error.message, 'error');
    });
}

// ===== TOGGLE EXPLANATION FIELD =====
function setupMeetsAllRequirementsToggle() {
    const meetsYes = document.getElementById('meets_yes');
    const meetsNo = document.getElementById('meets_no');
    const explanationContainer = document.getElementById('explanation_if_not_meet_container');
    
    if (meetsYes) {
        meetsYes.addEventListener('change', function() {
            if (this.checked) {
                explanationContainer.style.display = 'none';
                document.getElementById('explanation_if_not_meet').value = ''; // Clear explanation
            }
        });
    }
    
    if (meetsNo) {
        meetsNo.addEventListener('change', function() {
            if (this.checked) {
                explanationContainer.style.display = 'block';
            }
        });
    }
}

// ===== PAGE LOAD =====
document.addEventListener('DOMContentLoaded', function() {
    console.log('📄 DOM Content Loaded');
    setupMeetsAllRequirementsToggle();
    initializeForm();
});
</script>
@endsection
