<div class="card">
    <div class="card-body">
        <!-- Section 1: Inspection Standard Information -->
        <div class="form-section">
            <div class="form-section-title">
                <i class="ti ti-info-circle"></i>
                Inspection Standard Information
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="model" class="form-label">
                        Model
                        <span class="required-indicator">*</span>
                    </label>
                    <input type="text" class="form-control" id="model" name="model" 
                           placeholder="e.g., Model A, Model B" required>
                    <small class="text-muted">Enter the model designation</small>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="dateIssued" class="form-label">
                        Date of Issued
                        <span class="required-indicator">*</span>
                    </label>
                    <input type="date" class="form-control" id="dateIssued" name="dateIssued" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="refNo" class="form-label">
                        Ref. No
                        <span class="required-indicator">*</span>
                    </label>
                    <input type="text" class="form-control" id="refNo" name="refNo" 
                           placeholder="e.g., IS-2024-0001" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="partRoute" class="form-label">Part Route</label>
                    <input type="text" class="form-control" id="partRoute" name="partRoute" 
                           placeholder="e.g., AAIJ">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="partNo" class="form-label">
                        Part No
                        <span class="required-indicator">*</span>
                    </label>
                    <input type="text" class="form-control" id="partNo" name="partNo" 
                           placeholder="e.g., 12345-001" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="partName" class="form-label">
                        Part Name
                        <span class="required-indicator">*</span>
                    </label>
                    <input type="text" class="form-control" id="partName" name="partName" 
                           placeholder="e.g., Control Valve" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="manufacturer" class="form-label">
                        Manufacturer
                        <span class="required-indicator">*</span>
                    </label>
                    <input type="text" class="form-control" id="manufacturer" name="manufacturer" 
                           placeholder="Manufacturer name" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="material" class="form-label">
                        Material
                        <span class="required-indicator">*</span>
                    </label>
                    <input type="text" class="form-control" id="material" name="material" 
                           placeholder="e.g., Steel, Aluminum, Cast Iron" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="generalTolerance" class="form-label">General Tolerance</label>
                    <input type="text" class="form-control" id="generalTolerance" name="generalTolerance" 
                           placeholder="e.g., ±0.5mm">
                </div>

                <div class="col-md-6 mb-3">
                    <label for="weight" class="form-label">Weight</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="weight" name="weight" 
                               placeholder="e.g., 250">
                        <span class="input-group-text">kg</span>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="revisionDate" class="form-label">Revision Date</label>
                    <input type="date" class="form-control" id="revisionDate" name="revisionDate">
                </div>

                <div class="col-md-6 mb-3">
                    <label for="revisionRecord" class="form-label">Revision Record</label>
                    <textarea class="form-control" id="revisionRecord" name="revisionRecord" 
                              rows="2" placeholder="Document revision history"></textarea>
                </div>
            </div>
        </div>

        <!-- Section 2: Supplier & Manufacturer Information -->
        <div class="form-section">
            <div class="form-section-title">
                <i class="ti ti-building"></i>
                Supplier & Manufacturer Information
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="supplier" class="form-label">Supplier</label>
                    <input type="text" class="form-control" id="supplier" name="supplier" 
                           readonly style="background-color: #e9ecef;">
                    <small class="text-muted">Auto-populated from your account</small>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="customer" class="form-label">Customer</label>
                    <input type="text" class="form-control" id="customer" name="customer" 
                           value="AAIJ (Purchasing & Quality Division)" readonly 
                           style="background-color: #e9ecef;">
                    <small class="text-muted">Fixed value</small>
                </div>
            </div>

            <!-- Approval Information - Read Only -->
            <div class="row">
                <div class="col-md-4">
                    <div class="alert alert-info p-3 mb-0">
                        <p class="mb-2"><strong>Manufacturer Approval</strong></p>
                        <div class="d-flex gap-2">
                            <span class="badge bg-light text-dark">Approved</span>
                            <span class="badge bg-light text-dark">Checked</span>
                            <span class="badge bg-light text-dark">Issued</span>
                        </div>
                        <small class="text-muted">To be filled during review process</small>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="alert alert-info p-3 mb-0">
                        <p class="mb-2"><strong>Supplier Approval</strong></p>
                        <div class="d-flex gap-2">
                            <span class="badge bg-light text-dark">Approved</span>
                            <span class="badge bg-light text-dark">Checked</span>
                            <span class="badge bg-light text-dark">Issued</span>
                        </div>
                        <small class="text-muted">To be filled during review process</small>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="alert alert-info p-3 mb-0">
                        <p class="mb-2"><strong>Customer Approval</strong></p>
                        <div class="d-flex gap-2">
                            <span class="badge bg-light text-dark">Approved</span>
                            <span class="badge bg-light text-dark">Checked</span>
                            <span class="badge bg-light text-dark">Received</span>
                        </div>
                        <small class="text-muted">To be filled during review process</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Help Text -->
        <div class="alert alert-light border-start border-4 border-info mt-4">
            <i class="ti ti-info-circle me-2"></i>
            <strong>Tip:</strong> Fields marked with <span class="required-indicator">*</span> are required. 
            Fields like Supplier and Customer are automatically filled from your account or system configuration.
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('[Step 1] DOMContentLoaded - Initializing Step 1');
    
    // Collect form data for Step 1
    window.collectStep1Data = function() {
        const data = {
            model: document.getElementById('model').value,
            dateIssued: document.getElementById('dateIssued').value,
            refNo: document.getElementById('refNo').value,
            partRoute: document.getElementById('partRoute').value,
            partNo: document.getElementById('partNo').value,
            partName: document.getElementById('partName').value,
            manufacturer: document.getElementById('manufacturer').value,
            material: document.getElementById('material').value,
            generalTolerance: document.getElementById('generalTolerance').value,
            weight: document.getElementById('weight').value,
            revisionDate: document.getElementById('revisionDate').value,
            revisionRecord: document.getElementById('revisionRecord').value,
        };
        console.log('[Step 1] collectStep1Data():', data);
        return data;
    };

    function populateStep1Fields() {
        console.log('[Step 1] populateStep1Fields() called');
        const data = window.formData.generalInformation;
        
        console.log('[Step 1] Data to populate:', data);
        
        if (data.model) document.getElementById('model').value = data.model;
        if (data.dateIssued) document.getElementById('dateIssued').value = data.dateIssued;
        if (data.refNo) document.getElementById('refNo').value = data.refNo;
        if (data.partRoute) document.getElementById('partRoute').value = data.partRoute;
        if (data.partNo) document.getElementById('partNo').value = data.partNo;
        if (data.partName) document.getElementById('partName').value = data.partName;
        if (data.manufacturer) document.getElementById('manufacturer').value = data.manufacturer;
        if (data.material) document.getElementById('material').value = data.material;
        if (data.generalTolerance) document.getElementById('generalTolerance').value = data.generalTolerance;
        if (data.weight) document.getElementById('weight').value = data.weight;
        if (data.revisionDate) document.getElementById('revisionDate').value = data.revisionDate;
        if (data.revisionRecord) document.getElementById('revisionRecord').value = data.revisionRecord;
        
        console.log('[Step 1] All fields populated');
    }
    
    // Expose globally untuk dipanggil dari form.blade.php
    window.populateStep1Fields = populateStep1Fields;

    // When form data is loaded, populate the fields
    const originalLoadInterval = setInterval(() => {
        if (window.formData && window.formData.generalInformation && Object.keys(window.formData.generalInformation).length > 0) {
            console.log('[Step 1] formData tersedia, memanggil populateStep1Fields()');
            populateStep1Fields();
            clearInterval(originalLoadInterval);
        }
    }, 100);

    // Validate Step 1
    window.validateStep1 = function() {
        const errors = [];
        const requiredFields = ['model', 'dateIssued', 'refNo', 'partNo', 'partName', 'manufacturer', 'material'];
        
        requiredFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (!field || !field.value.trim()) {
                const label = field ? field.previousElementSibling.textContent.split('\n')[0] : fieldId;
                errors.push(`${label} is required`);
                if (field) field.classList.add('is-invalid');
            } else if (field) {
                field.classList.remove('is-invalid');
            }
        });

        if (errors.length > 0) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    html: errors.map(e => `<p class="mb-2">${e}</p>`).join(''),
                    confirmButtonColor: '#0d6efd',
                });
            } else {
                alert('Validation errors:\n' + errors.join('\n'));
            }
            return false;
        }
        return true;
    };
});
</script>
