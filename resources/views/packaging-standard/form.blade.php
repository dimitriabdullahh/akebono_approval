@extends('layouts.supplier')

@section('title', 'Packaging Standard (PS)')

@section('content')
<div class="container py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h3 class="mb-3">Packaging Standard (PS) Form</h3>
        </div>
    </div>

    <!-- Form -->
    <form id="psForm" method="POST" enctype="multipart/form-data">
        @csrf

        <!-- Section 1: General Information -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">General Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Document No -->
                    <div class="col-md-4 mb-3">
                        <label for="document_no" class="form-label">Document No. <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="document_no" name="document_no" required placeholder="Enter document number">
                    </div>

                    <!-- Revision -->
                    <div class="col-md-4 mb-3">
                        <label for="revision" class="form-label">Revision</label>
                        <input type="text" class="form-control" id="revision" name="revision" placeholder="e.g., Rev. 0">
                    </div>

                    <!-- Date -->
                    <div class="col-md-4 mb-3">
                        <label for="date" class="form-label">Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="date" name="date" required>
                    </div>

                    <!-- Customer (read-only, auto-filled) -->
                    <div class="col-md-12 mb-3">
                        <label for="customer" class="form-label">Customer</label>
                        <input type="text" class="form-control bg-light" id="customer" name="customer" value="PT. AKEBONO BRAKE ASTRA INDONESIA" readonly>
                        <small class="form-text text-muted">Auto-populated</small>
                    </div>

                    <!-- Part No -->
                    <div class="col-md-6 mb-3">
                        <label for="part_no" class="form-label">Part No. <span class="text-danger">*</span></label>
                        <input type="text" class="form-control bg-light" id="part_no" name="part_no" required readonly placeholder="Auto-populated">
                        <small class="form-text text-muted">Auto-populated from request</small>
                    </div>

                    <!-- Part Name -->
                    <div class="col-md-6 mb-3">
                        <label for="part_name" class="form-label">Part Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control bg-light" id="part_name" name="part_name" required readonly placeholder="Auto-populated">
                        <small class="form-text text-muted">Auto-populated from request</small>
                    </div>

                    <!-- Company Logo -->
                    <div class="col-md-12 mb-3">
                        <label for="company_logo" class="form-label">Company Logo <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="file" class="form-control" id="company_logo" accept="image/*" onchange="handleCompanyLogoUpload(this)">
                            <button class="btn btn-outline-secondary" type="button" onclick="clearCompanyLogo()">Clear</button>
                        </div>
                        <small class="form-text text-muted">Upload your company logo (displayed with company name in PDF header). Max 5MB. Best: horizontal logo, ~300x60 pixels.</small>
                        <div id="company-logo-preview" class="mt-2"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 2: Packaging Specifications -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">Packaging Specifications</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Packaging Type -->
                    <div class="col-md-6 mb-3">
                        <label for="packaging_type" class="form-label">Packaging Type <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="packaging_type" name="packaging_type" required placeholder="e.g., Cardboard Box, Plastic Container">
                    </div>

                    <!-- Packaging Size (mm) - P, L, T -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Packaging Size (mm) <span class="text-danger">*</span></label>
                        <div class="row g-2">
                            <div class="col-4">
                                <label for="packaging_size_p" class="form-label form-label-sm">P (Panjang)</label>
                                <input type="number" class="form-control" id="packaging_size_p" name="packaging_size_p" required placeholder="Panjang" step="0.01" min="0">
                            </div>
                            <div class="col-4">
                                <label for="packaging_size_l" class="form-label form-label-sm">L (Lebar)</label>
                                <input type="number" class="form-control" id="packaging_size_l" name="packaging_size_l" required placeholder="Lebar" step="0.01" min="0">
                            </div>
                            <div class="col-4">
                                <label for="packaging_size_t" class="form-label form-label-sm">T (Tinggi)</label>
                                <input type="number" class="form-control" id="packaging_size_t" name="packaging_size_t" required placeholder="Tinggi" step="0.01" min="0">
                            </div>
                        </div>
                    </div>

                    <!-- Packaging Lot (pcs/box) -->
                    <div class="col-md-4 mb-3">
                        <label for="packaging_lot_pcs_box" class="form-label">Packaging Lot (pcs/box) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="packaging_lot_pcs_box" name="packaging_lot_pcs_box" required placeholder="Parts per box" min="0">
                    </div>

                    <!-- Net Weight (g/pc) -->
                    <div class="col-md-4 mb-3">
                        <label for="net_weight_g_pc" class="form-label">Net Weight (g/pc)</label>
                        <input type="number" step="0.01" class="form-control" id="net_weight_g_pc" name="net_weight_g_pc" placeholder="Grams per piece" min="0">
                    </div>

                    <!-- Total Weight (kg/box) -->
                    <div class="col-md-4 mb-3">
                        <label for="total_weight_kg_box" class="form-label">Total Weight (kg/box)</label>
                        <input type="number" step="0.01" class="form-control" id="total_weight_kg_box" name="total_weight_kg_box" placeholder="Kilograms per box" min="0">
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 3: Part/Packaging Materials -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">Part / Packaging Materials</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="materialsTable">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 5%;">No.</th>
                                <th style="width: 25%;">Part No.</th>
                                <th style="width: 30%;">Part Name</th>
                                <th style="width: 25%;">Supplier</th>
                                <th style="width: 15%;">Qty</th>
                            </tr>
                        </thead>
                        <tbody id="materialsBody">
                            @for($i = 1; $i <= 6; $i++)
                            <tr>
                                <td class="text-center">{{ $i }}</td>
                                <td><input type="text" class="form-control form-control-sm" name="materials[{{ $i-1 }}][part_no]" data-index="{{ $i-1 }}" data-field="part_no"></td>
                                <td><input type="text" class="form-control form-control-sm" name="materials[{{ $i-1 }}][part_name]" data-index="{{ $i-1 }}" data-field="part_name"></td>
                                <td><input type="text" class="form-control form-control-sm" name="materials[{{ $i-1 }}][supplier]" data-index="{{ $i-1 }}" data-field="supplier"></td>
                                <td><input type="number" class="form-control form-control-sm" name="materials[{{ $i-1 }}][qty]" data-index="{{ $i-1 }}" data-field="qty" min="0" step="1"></td>
                            </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Section 4: Visualization & Approval -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">Visualization & Approval</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Image of Packaging (Multiple Upload) -->
                    <div class="col-md-6 mb-3">
                        <label for="image_of_packaging" class="form-label">Image of Packaging</label>
                        <div class="input-group">
                            <input type="file" class="form-control" id="image_of_packaging" 
                                   accept="image/*" multiple 
                                   onchange="handleMultipleImageUpload(this, 'packaging')">
                            <button class="btn btn-outline-secondary" type="button" 
                                    onclick="clearImageUpload('packaging')">Clear All</button>
                        </div>
                        <small class="form-text text-muted">Select multiple images (max 5MB each)</small>
                        <div id="packaging-images-preview" class="mt-2"></div>
                    </div>

                    <!-- Part Identification Point (Multiple Upload) -->
                    <div class="col-md-6 mb-3">
                        <label for="part_identification_point" class="form-label">Part Identification Point</label>
                        <div class="input-group">
                            <input type="file" class="form-control" id="part_identification_point" 
                                   accept="image/*" multiple 
                                   onchange="handleMultipleImageUpload(this, 'identification')">
                            <button class="btn btn-outline-secondary" type="button" 
                                    onclick="clearImageUpload('identification')">Clear All</button>
                        </div>
                        <small class="form-text text-muted">Select multiple images (max 5MB each)</small>
                        <div id="identification-images-preview" class="mt-2"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 5: Approval with QR Code -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">Approval Signatures (QR Code)</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="ti ti-info-circle"></i> Isi data approval untuk menghasilkan QR code signature yang akan muncul di PDF.
                </div>
                
                <!-- Prepared By -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="text-dark mb-3">Prepared By</h6>
                    </div>
                    <div class="col-12 mb-3">
                        <label for="prepared_by_name" class="form-label">Nama</label>
                        <input type="text" class="form-control" id="prepared_by_name" name="prepared_by_name" 
                               placeholder="Nama lengkap">
                    </div>
                </div>

                <hr>

                <!-- Checked By -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="text-dark mb-3">Checked By</h6>
                    </div>
                    <div class="col-12 mb-3">
                        <label for="checked_by_name" class="form-label">Nama</label>
                        <input type="text" class="form-control" id="checked_by_name" name="checked_by_name" 
                               placeholder="Nama lengkap">
                    </div>
                </div>

                <hr>

                <!-- Approved By -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="text-dark mb-3">Approved By</h6>
                    </div>
                    <div class="col-12 mb-3">
                        <label for="approved_by_name" class="form-label">Nama</label>
                        <input type="text" class="form-control" id="approved_by_name" name="approved_by_name" 
                               placeholder="Nama lengkap">
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
window.packagingStandardId = null;
window.materials = [
    {part_no: '', part_name: '', supplier: '', qty: ''},
    {part_no: '', part_name: '', supplier: '', qty: ''},
    {part_no: '', part_name: '', supplier: '', qty: ''},
    {part_no: '', part_name: '', supplier: '', qty: ''},
    {part_no: '', part_name: '', supplier: '', qty: ''},
    {part_no: '', part_name: '', supplier: '', qty: ''}
];
window.uploadedImages = {
    packaging: [],      // Array of images
    identification: []  // Array of images
};
window.companyLogo = null; // Store company logo as base64

// ===== UTILITY FUNCTIONS =====
function formatFileSize(bytes) {
    if (!bytes) return 'Unknown';
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    if (bytes === 0) return '0 Byte';
    const i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
    return Math.round(bytes / Math.pow(1024, i), 2) + ' ' + sizes[i];
}

// ===== MATERIALS MANAGEMENT =====
// Setup event listeners for material inputs
function setupMaterialInputs() {
    document.querySelectorAll('#materialsBody input').forEach(input => {
        input.addEventListener('change', function() {
            const index = parseInt(this.dataset.index);
            const field = this.dataset.field;
            if (window.materials[index]) {
                window.materials[index][field] = this.value;
            }
        });
    });
}

// ===== IMAGE UPLOAD HANDLING =====
function handleMultipleImageUpload(input, type) {
    const files = input.files;
    if (!files || files.length === 0) return;
    
    const previewId = type + '-images-preview';
    const previewContainer = document.getElementById(previewId);
    
    // Clear previous content
    previewContainer.innerHTML = '';
    
    let uploadedCount = 0;
    let errorCount = 0;
    
    // Reset array for this type
    window.uploadedImages[type] = [];
    
    for (let i = 0; i < files.length; i++) {
        const file = files[i];
        
        // Validate file size (5MB max)
        if (file.size > 5 * 1024 * 1024) {
            previewContainer.innerHTML += `
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="ti ti-alert-circle"></i> ${file.name} exceeds 5MB limit
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            errorCount++;
            continue;
        }
        
        // Read and store image
        const reader = new FileReader();
        reader.onload = function(e) {
            window.uploadedImages[type].push({
                name: file.name,
                data: e.target.result,
                size: file.size
            });
            
            // Add preview
            const imageIndex = window.uploadedImages[type].length - 1;
            const imageCard = `
                <div class="card mb-2" id="${type}-image-${imageIndex}">
                    <div class="card-body p-2">
                        <div class="row g-2 align-items-center">
                            <div class="col-auto">
                                <img src="${e.target.result}" style="max-width: 80px; max-height: 80px; border-radius: 4px;" />
                            </div>
                            <div class="col-auto flex-grow-1">
                                <small class="d-block"><strong>${file.name}</strong></small>
                                <small class="text-muted d-block">${formatFileSize(file.size)}</small>
                            </div>
                            <div class="col-auto">
                                <button type="button" class="btn btn-sm btn-danger" 
                                        onclick="removeImage('${type}', ${imageIndex})">
                                    <i class="ti ti-trash"></i> Remove
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            previewContainer.innerHTML += imageCard;
        };
        reader.readAsDataURL(file);
        uploadedCount++;
    }
    
    // Show summary
    if (uploadedCount > 0) {
        const summary = `<div class="alert alert-success"><i class="ti ti-check"></i> ${uploadedCount} image(s) ready to upload</div>`;
        previewContainer.innerHTML = summary + previewContainer.innerHTML;
    }
}

function removeImage(type, index) {
    window.uploadedImages[type].splice(index, 1);
    // Refresh preview
    const previewId = type + '-images-preview';
    const previewContainer = document.getElementById(previewId);
    previewContainer.innerHTML = '';
    
    window.uploadedImages[type].forEach((image, idx) => {
        const imageCard = `
            <div class="card mb-2" id="${type}-image-${idx}">
                <div class="card-body p-2">
                    <div class="row g-2 align-items-center">
                        <div class="col-auto">
                            <img src="${image.data}" style="max-width: 80px; max-height: 80px; border-radius: 4px;" />
                        </div>
                        <div class="col-auto flex-grow-1">
                            <small class="d-block"><strong>${image.name}</strong></small>
                            <small class="text-muted d-block">${formatFileSize(image.size)}</small>
                        </div>
                        <div class="col-auto">
                            <button type="button" class="btn btn-sm btn-danger" 
                                    onclick="removeImage('${type}', ${idx})">
                                <i class="ti ti-trash"></i> Remove
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        previewContainer.innerHTML += imageCard;
    });
}

function clearImageUpload(type) {
    window.uploadedImages[type] = [];
    document.getElementById(type === 'packaging' ? 'image_of_packaging' : 'part_identification_point').value = '';
    document.getElementById(type + '-images-preview').innerHTML = '';
}

// ===== COMPANY LOGO HANDLING =====
function handleCompanyLogoUpload(input) {
    const file = input.files[0];
    if (!file) return;
    
    const previewContainer = document.getElementById('company-logo-preview');
    
    // Validate file type
    if (!file.type.startsWith('image/')) {
        previewContainer.innerHTML = `
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="ti ti-alert-circle"></i> File must be an image
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        input.value = '';
        return;
    }
    
    // Validate file size (5MB max)
    if (file.size > 5 * 1024 * 1024) {
        previewContainer.innerHTML = `
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="ti ti-alert-circle"></i> Logo file exceeds 5MB limit
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        input.value = '';
        return;
    }
    
    // Read and store logo
    const reader = new FileReader();
    reader.onload = function(e) {
        window.companyLogo = e.target.result;
        
        // Show preview
        previewContainer.innerHTML = `
            <div class="card">
                <div class="card-body p-3">
                    <div class="row g-2 align-items-center">
                        <div class="col-auto">
                            <img src="${e.target.result}" style="max-width: 150px; max-height: 80px; border: 1px solid #ddd; border-radius: 4px; padding: 4px; background: white;" alt="Company Logo Preview" />
                        </div>
                        <div class="col-auto flex-grow-1">
                            <small class="d-block"><strong>${file.name}</strong></small>
                            <small class="text-muted d-block">${formatFileSize(file.size)}</small>
                            <small class="text-success d-block"><i class="ti ti-check"></i> Logo ready to upload</small>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        console.log('✅ Company logo loaded:', {
            name: file.name,
            size: file.size,
            dataLength: e.target.result.length
        });
    };
    reader.readAsDataURL(file);
}

function clearCompanyLogo() {
    window.companyLogo = null;
    document.getElementById('company_logo').value = '';
    document.getElementById('company-logo-preview').innerHTML = '';
}

function displayLoadedCompanyLogo() {
    if (!window.companyLogo) return;
    
    const previewContainer = document.getElementById('company-logo-preview');
    previewContainer.innerHTML = `
        <div class="card">
            <div class="card-body p-3">
                <div class="row g-2 align-items-center">
                    <div class="col-auto">
                        <img src="${window.companyLogo}" style="max-width: 150px; max-height: 80px; border: 1px solid #ddd; border-radius: 4px; padding: 4px; background: white;" alt="Company Logo Preview" />
                    </div>
                    <div class="col-auto flex-grow-1">
                        <small class="text-info d-block"><i class="ti ti-info-circle"></i> Company logo loaded</small>
                    </div>
                </div>
            </div>
        </div>
    `;
}

function displayLoadedImages(type) {
    const previewId = type + '-images-preview';
    const previewContainer = document.getElementById(previewId);
    previewContainer.innerHTML = '';
    
    if (window.uploadedImages[type].length === 0) return;
    
    const summary = `<div class="alert alert-info"><i class="ti ti-info-circle"></i> ${window.uploadedImages[type].length} image(s) loaded</div>`;
    previewContainer.innerHTML = summary;
    
    window.uploadedImages[type].forEach((image, idx) => {
        const imageCard = `
            <div class="card mb-2" id="${type}-image-${idx}">
                <div class="card-body p-2">
                    <div class="row g-2 align-items-center">
                        <div class="col-auto">
                            <img src="${image.data}" style="max-width: 80px; max-height: 80px; border-radius: 4px;" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2280%22 height=%2280%22%3E%3Crect fill=%22%23ddd%22 width=%2280%22 height=%2280%22/%3E%3C/svg%3E'" />
                        </div>
                        <div class="col-auto flex-grow-1">
                            <small class="d-block"><strong>${image.name}</strong></small>
                            <small class="text-muted d-block">${formatFileSize(image.size)}</small>
                        </div>
                        <div class="col-auto">
                            <button type="button" class="btn btn-sm btn-danger" 
                                    onclick="removeImage('${type}', ${idx})">
                                <i class="ti ti-trash"></i> Remove
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        previewContainer.innerHTML += imageCard;
    });
}

// ===== FORM INITIALIZATION =====
function initializeForm() {
    console.log('🚀 Initializing PS form');
    
    const urlPath = window.location.pathname;
    const token = urlPath.split('/').pop();
    
    if (!token) {
        Swal.fire('Error', 'Invalid token', 'error');
        return;
    }
    
    console.log('📝 Token:', token);
    
    fetch(`/api/packaging-standards/${token}`)
        .then(response => {
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            return response.json();
        })
        .then(data => {
            console.log('✅ Form data loaded:', data);
            
            window.packagingStandardId = data.id;
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
    // General Information
    console.log('🔍 populateFormData received:', data);
    console.log('🔍 part_no value:', data.part_no);
    console.log('🔍 part_name value:', data.part_name);
    
    if (data.document_no) document.getElementById('document_no').value = data.document_no;
    if (data.revision) document.getElementById('revision').value = data.revision;
    if (data.date) document.getElementById('date').value = data.date;
    // Customer is always set to PT. AKEBONO BRAKE ASTRA INDONESIA (read-only)
    document.getElementById('customer').value = 'PT. AKEBONO BRAKE ASTRA INDONESIA';
    if (data.part_no) document.getElementById('part_no').value = data.part_no;
    if (data.part_name) document.getElementById('part_name').value = data.part_name;
    
    // Packaging Specifications
    if (data.packaging_type) document.getElementById('packaging_type').value = data.packaging_type;
    
    // Parse packaging_size_mm from format "P: 100, L: 200, T: 300"
    if (data.packaging_size_mm) {
        const sizeMatch = data.packaging_size_mm.match(/P:\s*([\d.]+).*L:\s*([\d.]+).*T:\s*([\d.]+)/i);
        if (sizeMatch) {
            document.getElementById('packaging_size_p').value = sizeMatch[1] || '';
            document.getElementById('packaging_size_l').value = sizeMatch[2] || '';
            document.getElementById('packaging_size_t').value = sizeMatch[3] || '';
        }
    }
    
    if (data.packaging_lot_pcs_box) document.getElementById('packaging_lot_pcs_box').value = data.packaging_lot_pcs_box;
    if (data.net_weight_g_pc) document.getElementById('net_weight_g_pc').value = data.net_weight_g_pc;
    if (data.total_weight_kg_box) document.getElementById('total_weight_kg_box').value = data.total_weight_kg_box;
    
    // Materials - populate the 6 fixed rows
    if (data.form_data_json && data.form_data_json.materials) {
        const materials = data.form_data_json.materials;
        materials.forEach((material, index) => {
            if (index < 6) {
                window.materials[index] = material;
                const partNoInput = document.querySelector(`input[name="materials[${index}][part_no]"]`);
                const partNameInput = document.querySelector(`input[name="materials[${index}][part_name]"]`);
                const supplierInput = document.querySelector(`input[name="materials[${index}][supplier]"]`);
                const qtyInput = document.querySelector(`input[name="materials[${index}][qty]"]`);
                
                if (partNoInput) partNoInput.value = material.part_no || material.partNo || '';
                if (partNameInput) partNameInput.value = material.part_name || material.partName || '';
                if (supplierInput) supplierInput.value = material.supplier || '';
                if (qtyInput) qtyInput.value = material.qty || '';
            }
        });
    }
    
    // Setup material input listeners
    setupMaterialInputs();
    
    // Load company logo if available
    if (data.company_logo) {
        console.log('🔍 Loading existing company logo', {
            dataLength: data.company_logo.length,
            startsWithData: data.company_logo.startsWith('data:image')
        });
        window.companyLogo = data.company_logo;
        displayLoadedCompanyLogo();
    }
    
    // Load existing images if available
    if (data.packaging_images && Array.isArray(data.packaging_images)) {
        data.packaging_images.forEach((img, idx) => {
            window.uploadedImages.packaging.push({
                name: img.original_filename || `packaging_${idx}`,
                data: img.file_path,
                size: img.file_size,
                id: img.id
            });
        });
        displayLoadedImages('packaging');
    }
    
    if (data.identification_images && Array.isArray(data.identification_images)) {
        data.identification_images.forEach((img, idx) => {
            window.uploadedImages.identification.push({
                name: img.original_filename || `identification_${idx}`,
                data: img.file_path,
                size: img.file_size,
                id: img.id
            });
        });
        displayLoadedImages('identification');
    }
    
    // Populate approval QR fields if available
    if (data.prepared_by_name) document.getElementById('prepared_by_name').value = data.prepared_by_name;
    if (data.checked_by_name) document.getElementById('checked_by_name').value = data.checked_by_name;
    if (data.approved_by_name) document.getElementById('approved_by_name').value = data.approved_by_name;
}

function collectFormData() {
    // Combine P, L, T into packaging_size_mm format - ALL THREE REQUIRED
    const packagingSizeP = document.getElementById('packaging_size_p').value;
    const packagingSizeL = document.getElementById('packaging_size_l').value;
    const packagingSizeT = document.getElementById('packaging_size_t').value;
    
    // Only combine if all three are filled
    let packagingSizeMm = '';
    if (packagingSizeP && packagingSizeL && packagingSizeT) {
        packagingSizeMm = `P: ${packagingSizeP}, L: ${packagingSizeL}, T: ${packagingSizeT}`;
    }
    // If any is empty, leave as empty string '' to trigger backend validation
    
    // Helper function to convert numeric strings safely
    // For optional fields - returns null if empty
    const toNumber = (value) => {
        if (value === '' || value === null || value === undefined) return null;
        const num = parseFloat(value);
        return isNaN(num) ? null : num;
    };
    
    // For REQUIRED numeric fields - returns empty string if invalid (triggers backend validation)
    const toRequiredInteger = (value) => {
        if (value === '' || value === null || value === undefined) return '';
        const num = parseInt(value, 10);
        return isNaN(num) ? '' : num;
    };
    
    const formData = {
        document_no: document.getElementById('document_no').value || '',
        revision: document.getElementById('revision').value || '',
        date: document.getElementById('date').value || '',
        part_no: document.getElementById('part_no').value || '',
        part_name: document.getElementById('part_name').value || '',
        packaging_type: document.getElementById('packaging_type').value || '',
        packaging_size_mm: packagingSizeMm || '',
        packaging_lot_pcs_box: toRequiredInteger(document.getElementById('packaging_lot_pcs_box').value),
        net_weight_g_pc: toNumber(document.getElementById('net_weight_g_pc').value),
        total_weight_kg_box: toNumber(document.getElementById('total_weight_kg_box').value),
        materials: window.materials,
        image_of_packaging: window.uploadedImages.packaging || [],      // Array of base64 images
        part_identification_point: window.uploadedImages.identification || [],  // Array of base64 images
        company_logo: window.companyLogo || null,  // Company logo as base64
        // Approval QR fields
        prepared_by_name: document.getElementById('prepared_by_name').value || '',
        checked_by_name: document.getElementById('checked_by_name').value || '',
        approved_by_name: document.getElementById('approved_by_name').value || ''
    };
    
    console.log('📦 Collected form data:', formData);
    console.log('🏢 Company logo present:', !!window.companyLogo);
    if (window.companyLogo) {
        console.log('🏢 Company logo length:', window.companyLogo.length);
    }
    return formData;
}

// ===== AUTO-SAVE SETUP =====
let autoSaveTimer = null;
function setupAutoSave() {
    const formInputs = document.querySelectorAll('#psForm input, #psForm textarea, #psForm select');
    
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
    if (!window.packagingStandardId) {
        if (!silent) Swal.fire('Error', 'Form not fully loaded. Please refresh the page and try again.', 'error');
        return;
    }

    const formData = collectFormData();
    formData.status = 'DRAFT';
    
    fetch(`/api/packaging-standards/${window.packagingStandardId}/draft`, {
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
        {id: 'document_no', label: 'Document No.'},
        {id: 'date', label: 'Date'},
        {id: 'part_no', label: 'Part No.'},
        {id: 'part_name', label: 'Part Name'},
        {id: 'packaging_type', label: 'Packaging Type'},
        {id: 'packaging_size_p', label: 'Packaging Size P (Panjang)'},
        {id: 'packaging_size_l', label: 'Packaging Size L (Lebar)'},
        {id: 'packaging_size_t', label: 'Packaging Size T (Tinggi)'},
        {id: 'packaging_lot_pcs_box', label: 'Packaging Lot (pcs/box)'}
    ];
    
    const missingFields = [];
    const invalidFields = [];
    
    for (const field of required) {
        const element = document.getElementById(field.id);
        
        if (!element) {
            console.warn(`Field element not found: ${field.id}`);
            continue;
        }
        
        const value = element.value ? element.value.trim() : '';
        
        // Check if empty
        if (!value) {
            missingFields.push(field.label);
            continue;
        }
        
        // Check numeric fields validity
        if (field.id.includes('packaging_size') || field.id === 'packaging_lot_pcs_box') {
            const numValue = parseFloat(value);
            if (isNaN(numValue) || numValue <= 0) {
                invalidFields.push(`${field.label}: Must be a positive number`);
            }
        }
    }
    
    // Build error message
    let errorMsg = '';
    if (missingFields.length > 0) {
        errorMsg += 'Missing required fields:\n• ' + missingFields.join('\n• ');
    }
    if (invalidFields.length > 0) {
        if (errorMsg) errorMsg += '\n\n';
        errorMsg += 'Invalid field values:\n• ' + invalidFields.join('\n• ');
    }
    
    if (errorMsg) {
        Swal.fire({
            icon: 'warning',
            title: 'Validation Error',
            html: `<div style="text-align: left; white-space: pre-wrap;">${errorMsg.replace(/\n/g, '<br>')}</div>`,
            confirmButtonText: 'OK'
        });
        return false;
    }
    
    return true;
}

function submitForm() {
    if (!validate()) return;

    if (!window.packagingStandardId) {
        Swal.fire('Error', 'Form not fully loaded. Please refresh the page and try again.', 'error');
        return;
    }

    // Show loading indicator
    Swal.fire({
        title: 'Submitting...',
        text: 'Please wait while your form is being submitted',
        icon: 'info',
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    const formData = collectFormData();
    formData.status = 'SUBMITTED';
    
    // DEBUG: Log all form data
    console.log('📦 FULL FORM DATA:', formData);
    console.log('📋 Fields present:', {
        document_no: formData.document_no,
        date: formData.date,
        packaging_type: formData.packaging_type,
        packaging_size_mm: formData.packaging_size_mm,
        packaging_lot_pcs_box: formData.packaging_lot_pcs_box,
        net_weight_g_pc: formData.net_weight_g_pc,
        total_weight_kg_box: formData.total_weight_kg_box,
        materials_count: formData.materials ? formData.materials.length : 0,
        images_packaging_count: formData.image_of_packaging ? formData.image_of_packaging.length : 0,
        images_identification_count: formData.part_identification_point ? formData.part_identification_point.length : 0
    });
    
    fetch(`/api/packaging-standards/${window.packagingStandardId}/submit`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(formData)
    })
    .then(response => {
        console.log('📨 Response status:', response.status);
        if (!response.ok) {
            // Try to parse error response
            return response.json().then(err => {
                throw {
                    status: response.status,
                    data: err
                };
            }).catch(parseErr => {
                throw {
                    status: response.status,
                    data: { message: `HTTP ${response.status}` }
                };
            });
        }
        return response.json();
    })
    .then(result => {
        console.log('✅ Submit response:', result);
        if (result.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'Packaging Standard submitted successfully',
                confirmButtonText: 'OK'
            }).then(() => {
                window.location.href = result.redirect || '/supplier/dashboard';
            });
        } else {
            // Handle validation errors
            let errorMessage = result.message || 'Failed to submit form';
            if (result.errors && Array.isArray(result.errors)) {
                errorMessage += '\n\n' + result.errors.join('\n');
            } else if (result.errors && typeof result.errors === 'object') {
                const errorDetails = Object.entries(result.errors).map(([field, msgs]) => {
                    return `${field}: ${Array.isArray(msgs) ? msgs.join(', ') : msgs}`;
                }).join('\n');
                errorMessage += '\n\n' + errorDetails;
            }
            console.error('❌ Validation errors:', result.errors);
            Swal.fire('Submission Failed', errorMessage, 'error');
        }
    })
    .catch(error => {
        console.error('❌ Error submitting form:', error);
        
        let errorMessage = 'An error occurred while submitting the form';
        let errorDetails = [];
        
        if (error.status === 422) {
            if (error.data && error.data.message) {
                errorMessage = error.data.message;
            }
            
            // Parse detailed field errors
            if (error.data && error.data.errors) {
                if (typeof error.data.errors === 'object') {
                    for (const [field, msgs] of Object.entries(error.data.errors)) {
                        const fieldLabel = field.replace(/_/g, ' ').toUpperCase();
                        const errorMsg = Array.isArray(msgs) ? msgs[0] : msgs;
                        errorDetails.push(`• ${fieldLabel}: ${errorMsg}`);
                    }
                }
            }
        } else if (error.status) {
            errorMessage = `HTTP ${error.status}: ${error.data?.message || 'Server error'}`;
        } else if (error.message) {
            errorMessage = error.message;
        }
        
        // Build final error message with field details
        let finalErrorMessage = errorMessage;
        if (errorDetails.length > 0) {
            finalErrorMessage += '\n\n' + errorDetails.join('\n');
        }
        
        Swal.fire({
            icon: 'error',
            title: 'Submission Failed',
            html: `<div style="text-align: left; white-space: pre-wrap;">${finalErrorMessage.replace(/\n/g, '<br>')}</div>`,
            confirmButtonText: 'OK'
        });
    });
}

// ===== PAGE LOAD =====
document.addEventListener('DOMContentLoaded', function() {
    console.log('📄 DOM Content Loaded');
    initializeForm();
});
</script>
@endsection
