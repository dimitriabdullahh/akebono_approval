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
                        <label for="revision" class="form-label">Revision <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="revision" name="revision" required placeholder="e.g., Rev. 0">
                    </div>

                    <!-- Date -->
                    <div class="col-md-4 mb-3">
                        <label for="date" class="form-label">Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="date" name="date" required>
                    </div>

                    <!-- Customer -->
                    <div class="col-md-6 mb-3">
                        <label for="customer" class="form-label">Customer</label>
                        <input type="text" class="form-control" id="customer" name="customer" value="PT. AKEBONO BRAKE ASTRA INDONESIA" placeholder="Customer name">
                    </div>

                    <!-- Model -->
                    <div class="col-md-6 mb-3">
                        <label for="model" class="form-label">Model</label>
                        <input type="text" class="form-control bg-light" id="model" name="model" readonly placeholder="Auto-populated">
                        <small class="form-text text-muted">Auto-populated from request</small>
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

                    <!-- Supplier Name -->
                    <div class="col-md-6 mb-3">
                        <label for="supplier_name" class="form-label">Supplier Name</label>
                        <input type="text" class="form-control bg-light" id="supplier_name" name="supplier_name" readonly placeholder="Auto-populated">
                        <small class="form-text text-muted">Auto-populated (Supplier name)</small>
                    </div>

                    <!-- Qty -->
                    <div class="col-md-6 mb-3">
                        <label for="qty" class="form-label">Qty <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="qty" name="qty" required placeholder="Enter quantity">
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

                    <!-- Packaging Size (mm) -->
                    <div class="col-md-6 mb-3">
                        <label for="packaging_size_mm" class="form-label">Packaging Size (mm) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="packaging_size_mm" name="packaging_size_mm" required placeholder="e.g., 300 x 200 x 150">
                    </div>

                    <!-- Packaging Lot (pcs/box) -->
                    <div class="col-md-4 mb-3">
                        <label for="packaging_lot_pcs_box" class="form-label">Packaging Lot (pcs/box) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="packaging_lot_pcs_box" name="packaging_lot_pcs_box" required placeholder="Parts per box">
                    </div>

                    <!-- Net Weight (g/pc) -->
                    <div class="col-md-4 mb-3">
                        <label for="net_weight_g_pc" class="form-label">Net Weight (g/pc)</label>
                        <input type="number" step="0.01" class="form-control" id="net_weight_g_pc" name="net_weight_g_pc" placeholder="Grams per piece">
                    </div>

                    <!-- Total Weight (kg/box) -->
                    <div class="col-md-4 mb-3">
                        <label for="total_weight_kg_box" class="form-label">Total Weight (kg/box)</label>
                        <input type="number" step="0.01" class="form-control" id="total_weight_kg_box" name="total_weight_kg_box" placeholder="Kilograms per box">
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 3: Part/Packaging Materials -->
        <div class="card mb-4">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Part / Packaging Materials</h5>
                <button type="button" class="btn btn-sm btn-primary" onclick="addMaterial()">
                    <i class="ti ti-plus"></i> Add Material
                </button>
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
                                <th style="width: 10%;">Qty</th>
                                <th style="width: 5%;"></th>
                            </tr>
                        </thead>
                        <tbody id="materialsBody">
                            <!-- Materials will be added here -->
                        </tbody>
                    </table>
                </div>
                <div id="noMaterialsMessage" class="alert alert-info mt-3">
                    <i class="ti ti-info-circle me-2"></i>
                    Click "Add Material" button above to add packaging materials
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
                    <!-- Image of Packaging -->
                    <div class="col-md-6 mb-3">
                        <label for="image_of_packaging" class="form-label">Image of Packaging</label>
                        <input type="file" class="form-control" id="image_of_packaging" name="image_of_packaging" accept="image/*" onchange="handleImageUpload(this, 'packaging')">
                        <div id="packaging-image-preview" class="mt-2"></div>
                    </div>

                    <!-- Part Identification Point -->
                    <div class="col-md-6 mb-3">
                        <label for="part_identification_point" class="form-label">Part Identification Point</label>
                        <input type="file" class="form-control" id="part_identification_point" name="part_identification_point" accept="image/*" onchange="handleImageUpload(this, 'identification')">
                        <div id="identification-image-preview" class="mt-2"></div>
                    </div>

                    <!-- Prepared By -->
                    <div class="col-md-4 mb-3">
                        <label for="prepared_by_name" class="form-label">Prepared By</label>
                        <input type="text" class="form-control" id="prepared_by_name" name="prepared_by_name" placeholder="Name">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="prepared_by_date" class="form-label">Date</label>
                        <input type="date" class="form-control" id="prepared_by_date" name="prepared_by_date">
                    </div>

                    <!-- Checked By -->
                    <div class="col-md-4 mb-3">
                        <label for="checked_by_name" class="form-label">Checked By</label>
                        <input type="text" class="form-control" id="checked_by_name" name="checked_by_name" placeholder="Name">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="checked_by_date" class="form-label">Date</label>
                        <input type="date" class="form-control" id="checked_by_date" name="checked_by_date">
                    </div>

                    <!-- Approved By -->
                    <div class="col-md-4 mb-3">
                        <label for="approved_by_name" class="form-label">Approved By</label>
                        <input type="text" class="form-control" id="approved_by_name" name="approved_by_name" placeholder="Name">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="approved_by_date" class="form-label">Date</label>
                        <input type="date" class="form-control" id="approved_by_date" name="approved_by_date">
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
window.materials = [];
let materialCounter = 0;

// ===== UTILITY FUNCTIONS =====
function formatFileSize(bytes) {
    if (!bytes) return 'Unknown';
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    if (bytes === 0) return '0 Byte';
    const i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
    return Math.round(bytes / Math.pow(1024, i), 2) + ' ' + sizes[i];
}

// ===== DRAWINGS MANAGEMENT =====
function addDrawing() {
    const index = drawingCounter++;
    const drawing = {
        id: null,
        drawing_name: '',
        drawing_no: '',
        revision: '',
        description: '',
        file_path: null
    };
    
    window.drawings.push(drawing);
    
    const container = document.getElementById('drawingsContainer');
    const drawingCard = document.createElement('div');
    drawingCard.className = 'card mb-3';
    drawingCard.dataset.index = window.drawings.length - 1;
    
    drawingCard.innerHTML = `
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0">Drawing #${window.drawings.length}</h6>
                <button type="button" class="btn btn-sm btn-danger" onclick="removeDrawing(${window.drawings.length - 1})">
                    <i class="ti ti-trash"></i>
                </button>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Drawing Name</label>
                    <input type="text" class="form-control" onchange="updateDrawing(${window.drawings.length - 1}, 'drawing_name', this.value)" placeholder="Drawing name">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Drawing No</label>
                    <input type="text" class="form-control" onchange="updateDrawing(${window.drawings.length - 1}, 'drawing_no', this.value)" placeholder="Drawing number">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Revision</label>
                    <input type="text" class="form-control" onchange="updateDrawing(${window.drawings.length - 1}, 'revision', this.value)" placeholder="Rev.">
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" rows="2" onchange="updateDrawing(${window.drawings.length - 1}, 'description', this.value)" placeholder="Description"></textarea>
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Drawing File (PDF/Image)</label>
                    <input type="file" class="form-control" accept=".pdf,image/*" onchange="handleDrawingFileUpload(${window.drawings.length - 1}, this.files[0])">
                    <div id="drawing-file-${window.drawings.length - 1}" class="mt-2"></div>
                </div>
            </div>
        </div>
    `;
    
    container.appendChild(drawingCard);
    document.getElementById('noDrawingsMessage').style.display = 'none';
}

function updateDrawing(index, field, value) {
    if (window.drawings[index]) {
        window.drawings[index][field] = value;
    }
}

function handleDrawingFileUpload(index, file) {
    if (!file) return;
    
    if (file.size > 10 * 1024 * 1024) {
        Swal.fire('Error', 'File exceeds 10MB limit', 'error');
        return;
    }
    
    const reader = new FileReader();
    reader.onload = function(e) {
        window.drawings[index].file_path = e.target.result;
        window.drawings[index].file_name = file.name;
        
        document.getElementById(`drawing-file-${index}`).innerHTML = `
            <div class="alert alert-success">
                <i class="ti ti-check"></i> ${file.name} (${formatFileSize(file.size)})
            </div>
        `;
    };
    reader.readAsDataURL(file);
}

function removeDrawing(index) {
    Swal.fire({
        title: 'Are you sure?',
        text: 'Do you want to remove this drawing?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, remove it!'
    }).then((result) => {
        if (result.isConfirmed) {
            window.drawings.splice(index, 1);
            renderDrawings();
        }
    });
}

function renderDrawings() {
    const container = document.getElementById('drawingsContainer');
    container.innerHTML = '';
    
    if (window.drawings.length === 0) {
        document.getElementById('noDrawingsMessage').style.display = 'block';
        return;
    }
    
    document.getElementById('noDrawingsMessage').style.display = 'none';
    
    window.drawings.forEach((drawing, index) => {
        const drawingCard = document.createElement('div');
        drawingCard.className = 'card mb-3';
        
        drawingCard.innerHTML = `
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0">Drawing #${index + 1}</h6>
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeDrawing(${index})">
                        <i class="ti ti-trash"></i>
                    </button>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Drawing Name</label>
                        <input type="text" class="form-control" value="${drawing.drawing_name || ''}" onchange="updateDrawing(${index}, 'drawing_name', this.value)">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Drawing No</label>
                        <input type="text" class="form-control" value="${drawing.drawing_no || ''}" onchange="updateDrawing(${index}, 'drawing_no', this.value)">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Revision</label>
                        <input type="text" class="form-control" value="${drawing.revision || ''}" onchange="updateDrawing(${index}, 'revision', this.value)">
                    </div>
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" rows="2" onchange="updateDrawing(${index}, 'description', this.value)">${drawing.description || ''}</textarea>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Drawing File</label>
                        ${drawing.file_path ? `<div class="alert alert-success"><i class="ti ti-check"></i> File uploaded</div>` : '<input type="file" class="form-control" accept=".pdf,image/*" onchange="handleDrawingFileUpload(' + index + ', this.files[0])">'}
                    </div>
                </div>
            </div>
        `;
        
        container.appendChild(drawingCard);
    });
}

// ===== INSPECTION ITEMS MANAGEMENT =====
function addInspectionItem() {
    const item = {
        id: null,
        inspection_item: '',
        inspection_instrument: '',
        inspection_criterion: '',
        rank: '',
        supplier_sampling: '',
        aaij_sampling: '',
        remark_inspection_method: ''
    };
    
    window.inspectionItems.push(item);
    renderInspectionItems();
}

function updateInspectionItem(index, field, value) {
    if (window.inspectionItems[index]) {
        window.inspectionItems[index][field] = value;
    }
}

function removeInspectionItem(index) {
    Swal.fire({
        title: 'Are you sure?',
        text: 'Do you want to remove this item?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, remove it!'
    }).then((result) => {
        if (result.isConfirmed) {
            window.inspectionItems.splice(index, 1);
            renderInspectionItems();
        }
    });
}

function renderInspectionItems() {
    const tbody = document.getElementById('inspectionItemsBody');
    tbody.innerHTML = '';
    
    if (window.inspectionItems.length === 0) {
        document.getElementById('noItemsMessage').style.display = 'block';
        return;
    }
    
    document.getElementById('noItemsMessage').style.display = 'none';
    
    window.inspectionItems.forEach((item, index) => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td class="text-center">${index + 1}</td>
            <td><input type="text" class="form-control form-control-sm" value="${item.inspection_item || ''}" onchange="updateInspectionItem(${index}, 'inspection_item', this.value)" required></td>
            <td><input type="text" class="form-control form-control-sm" value="${item.inspection_instrument || ''}" onchange="updateInspectionItem(${index}, 'inspection_instrument', this.value)"></td>
            <td><input type="text" class="form-control form-control-sm" value="${item.inspection_criterion || ''}" onchange="updateInspectionItem(${index}, 'inspection_criterion', this.value)" required></td>
            <td><input type="text" class="form-control form-control-sm" value="${item.rank || ''}" onchange="updateInspectionItem(${index}, 'rank', this.value)" required></td>
            <td><input type="text" class="form-control form-control-sm" value="${item.supplier_sampling || ''}" onchange="updateInspectionItem(${index}, 'supplier_sampling', this.value)"></td>
            <td><input type="text" class="form-control form-control-sm" value="${item.aaij_sampling || ''}" onchange="updateInspectionItem(${index}, 'aaij_sampling', this.value)"></td>
            <td><input type="text" class="form-control form-control-sm" value="${item.remark_inspection_method || ''}" onchange="updateInspectionItem(${index}, 'remark_inspection_method', this.value)"></td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-danger" onclick="removeInspectionItem(${index})">
                    <i class="ti ti-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

// ===== FORM INITIALIZATION =====
function initializeForm() {
    console.log('🚀 Initializing IS form');
    
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
    if (data.model) document.getElementById('model').value = data.model;
    if (data.date_issued) document.getElementById('date_issued').value = data.date_issued;
    if (data.ref_no) document.getElementById('ref_no').value = data.ref_no;
    if (data.part_route) document.getElementById('part_route').value = data.part_route;
    if (data.part_no) document.getElementById('part_no').value = data.part_no;
    if (data.part_name) document.getElementById('part_name').value = data.part_name;
    if (data.manufacturer) document.getElementById('manufacturer').value = data.manufacturer;
    if (data.material) document.getElementById('material').value = data.material;
    if (data.general_tolerance) document.getElementById('general_tolerance').value = data.general_tolerance;
    if (data.weight) document.getElementById('weight').value = data.weight;
    if (data.revision_date) document.getElementById('revision_date').value = data.revision_date;
    if (data.revision_record) document.getElementById('revision_record').value = data.revision_record;
    
    // Drawings
    if (data.drawings && data.drawings.length > 0) {
        window.drawings = data.drawings;
        renderDrawings();
    }
    
    // Inspection Items
    if (data.inspection_items && data.inspection_items.length > 0) {
        window.inspectionItems = data.inspection_items;
        renderInspectionItems();
    }
}

function collectFormData() {
    return {
        model: document.getElementById('model').value,
        date_issued: document.getElementById('date_issued').value,
        ref_no: document.getElementById('ref_no').value,
        part_route: document.getElementById('part_route').value,
        part_no: document.getElementById('part_no').value,
        part_name: document.getElementById('part_name').value,
        manufacturer: document.getElementById('manufacturer').value,
        material: document.getElementById('material').value,
        general_tolerance: document.getElementById('general_tolerance').value,
        weight: document.getElementById('weight').value,
        revision_date: document.getElementById('revision_date').value,
        revision_record: document.getElementById('revision_record').value,
        drawings: window.drawings,
        inspection_items: window.inspectionItems
    };
}

// ===== AUTO-SAVE SETUP =====
let autoSaveTimer = null;
function setupAutoSave() {
    const formInputs = document.querySelectorAll('#isForm input, #isForm textarea, #isForm select');
    
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
        {id: 'model', label: 'Model'},
        {id: 'date_issued', label: 'Date of Issued'},
        {id: 'ref_no', label: 'Ref. No'},
        {id: 'part_no', label: 'Part No'},
        {id: 'part_name', label: 'Part Name'},
        {id: 'manufacturer', label: 'Manufacturer'},
        {id: 'material', label: 'Material'}
    ];
    
    for (const field of required) {
        const element = document.getElementById(field.id);
        if (!element.value) {
            Swal.fire('Validation Error', `${field.label} is required`, 'warning');
            element.focus();
            return false;
        }
    }
    
    if (window.drawings.length === 0) {
        Swal.fire('Validation Error', 'At least one drawing is required', 'warning');
        return false;
    }
    
    if (window.inspectionItems.length === 0) {
        Swal.fire('Validation Error', 'At least one inspection item is required', 'warning');
        return false;
    }
    
    // Validate inspection items
    for (let i = 0; i < window.inspectionItems.length; i++) {
        const item = window.inspectionItems[i];
        if (!item.inspection_item) {
            Swal.fire('Validation Error', `Inspection Item #${i + 1}: Item description is required`, 'warning');
            return false;
        }
        if (!item.inspection_criterion) {
            Swal.fire('Validation Error', `Inspection Item #${i + 1}: Inspection criterion is required`, 'warning');
            return false;
        }
        if (!item.rank) {
            Swal.fire('Validation Error', `Inspection Item #${i + 1}: Rank is required`, 'warning');
            return false;
        }
    }
    
    return true;
}

function submitForm() {
    if (!validate()) return;

    if (!window.packagingStandardId) {
        Swal.fire('Error', 'Form not fully loaded. Please refresh the page and try again.', 'error');
        return;
    }

    const formData = collectFormData();
    formData.status = 'SUBMITTED';
    
    fetch(`/api/packaging-standards/${window.packagingStandardId}/submit`, {
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
                text: 'Packaging Standard submitted successfully',
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

// ===== PAGE LOAD =====
document.addEventListener('DOMContentLoaded', function() {
    console.log('📄 DOM Content Loaded');
    initializeForm();
});
</script>
@endsection
