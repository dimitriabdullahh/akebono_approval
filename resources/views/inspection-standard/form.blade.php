@extends('layouts.supplier')

@section('title', 'Inspection Standard (IS)')

@push('styles')
<style>
    /* Fix for inspection items table overflow */
    #inspectionItemsTable {
        table-layout: auto;
        width: 100%;
    }
    
    #inspectionItemsTable th,
    #inspectionItemsTable td {
        white-space: nowrap;
    }
    
    #inspectionItemsTable input.form-control-sm {
        font-size: 0.875rem;
        padding: 0.25rem 0.5rem;
    }
    
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    /* Ensure card doesn't overflow */
    .card {
        overflow: hidden;
    }
    
    /* Compact button in table */
    #inspectionItemsTable .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
</style>
@endpush

@section('content')
<div class="container py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h3 class="mb-3">Inspection Standard (IS) Form</h3>
        </div>
    </div>

    <!-- Revision Alert Banner (only show if document is in revision status) -->
    <div id="revisionBanner" class="alert alert-warning border-warning" style="display: none; border-left: 4px solid #FFA500;">
        <div class="d-flex align-items-start">
            <div class="flex-shrink-0">
                <i class="ti ti-alert-triangle" style="font-size: 2rem; color: #FFA500;"></i>
            </div>
            <div class="flex-grow-1 ms-3">
                <h5 class="alert-heading mb-2">
                    <i class="ti ti-pencil"></i> Dokumen Perlu Direvisi
                </h5>
                <p class="mb-2">
                    <strong>Direvisi oleh:</strong> <span id="revisionTeam"></span>
                </p>
                <p class="mb-2">
                    <strong>Catatan Revisi:</strong><br>
                    <span id="revisionNote" class="text-dark"></span>
                </p>
                <p class="mb-0 text-muted small">
                    <i class="ti ti-clock"></i> <span id="revisionDate"></span>
                </p>
                <hr class="my-2">
                <p class="mb-0 small">
                    <i class="ti ti-info-circle"></i> Silakan perbaiki dokumen sesuai catatan di atas, kemudian klik <strong>Submit</strong> untuk mengirim ulang.
                </p>
            </div>
        </div>
    </div>

    <!-- Form -->
    <form id="isForm" method="POST" enctype="multipart/form-data">
        @csrf

        <!-- Section 1: General Information -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">General Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Model -->
                    <div class="col-md-6 mb-3">
                        <label for="model" class="form-label">Model <span class="text-danger">*</span></label>
                        <input type="text" class="form-control bg-light" id="model" name="model" required readonly placeholder="Auto-populated">
                    </div>

                    <!-- Date of Issued -->
                    <div class="col-md-6 mb-3">
                        <label for="date_issued" class="form-label">Date of Issued <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="date_issued" name="date_issued" required>
                    </div>

                    <!-- Ref. No -->
                    <div class="col-md-6 mb-3">
                        <label for="ref_no" class="form-label">Ref. No <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="ref_no" name="ref_no" required placeholder="Enter reference number">
                    </div>

                    <!-- Part Route -->
                    <div class="col-md-6 mb-3">
                        <label for="part_route" class="form-label">Part Route</label>
                        <input type="text" class="form-control bg-light" id="part_route" name="part_route" readonly placeholder="Auto-populated">
                    </div>

                    <!-- Part No -->
                    <div class="col-md-6 mb-3">
                        <label for="part_no" class="form-label">Part No <span class="text-danger">*</span></label>
                        <input type="text" class="form-control bg-light" id="part_no" name="part_no" required readonly placeholder="Auto-populated">
                    </div>

                    <!-- Part Name -->
                    <div class="col-md-6 mb-3">
                        <label for="part_name" class="form-label">Part Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control bg-light" id="part_name" name="part_name" required readonly placeholder="Auto-populated">
                    </div>

                    <!-- Manufacturer -->
                    <div class="col-md-6 mb-3">
                        <label for="manufacturer" class="form-label">Manufacturer <span class="text-danger">*</span></label>
                        <input type="text" class="form-control bg-light" id="manufacturer" name="manufacturer" required readonly placeholder="Auto-populated">
                    </div>

                    <!-- Material -->
                    <div class="col-md-6 mb-3">
                        <label for="material" class="form-label">Material <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="material" name="material" required placeholder="Enter material specification">
                    </div>

                    <!-- General Tolerance -->
                    <div class="col-md-6 mb-3">
                        <label for="general_tolerance" class="form-label">General Tolerance</label>
                        <input type="text" class="form-control" id="general_tolerance" name="general_tolerance" placeholder="e.g., ±0.1mm">
                    </div>

                    <!-- Weight -->
                    <div class="col-md-6 mb-3">
                        <label for="weight" class="form-label">Weight (kg)</label>
                        <div class="input-group">
                            <button type="button" class="btn btn-outline-secondary" onclick="decrementWeight()">
                                <i class="ti ti-minus"></i>
                            </button>
                            <input type="number" class="form-control text-center" id="weight" name="weight" placeholder="0.00" min="0" step="0.01" inputmode="decimal">
                            <button type="button" class="btn btn-outline-secondary" onclick="incrementWeight()">
                                <i class="ti ti-plus"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Revision Date -->
                    <div class="col-md-6 mb-3">
                        <label for="revision_date" class="form-label">Revision Date</label>
                        <input type="date" class="form-control" id="revision_date" name="revision_date">
                    </div>

                    <!-- Revision Record -->
                    <div class="col-md-6 mb-3">
                        <label for="revision_record" class="form-label">Revision Record</label>
                        <input type="text" class="form-control" id="revision_record" name="revision_record" placeholder="Revision history">
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 2: Part Drawings -->
        <div class="card mb-4">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Part Drawings</h5>
                <button type="button" class="btn btn-sm btn-primary" onclick="addDrawing()">
                    <i class="ti ti-plus"></i> Add Drawing
                </button>
            </div>
            <div class="card-body">
                <div id="drawingsContainer">
                    <!-- Drawings will be added here -->
                </div>
                <div id="noDrawingsMessage" class="alert alert-info">
                    <i class="ti ti-info-circle me-2"></i>
                    Click "Add Drawing" button above to add part drawings
                </div>
            </div>
        </div>

        <!-- Section 3: Inspection Items -->
        <div class="card mb-4">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Inspection Items</h5>
                <button type="button" class="btn btn-sm btn-primary" onclick="addInspectionItem()">
                    <i class="ti ti-plus"></i> Add Item
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="overflow-x: auto;">
                    <table class="table table-bordered mb-0" id="inspectionItemsTable" style="min-width: 100%;">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 40px; min-width: 40px;">No.</th>
                                <th style="width: 150px; min-width: 150px;">Inspection Item <span class="text-danger">*</span></th>
                                <th style="width: 120px; min-width: 120px;">Instrument</th>
                                <th style="width: 150px; min-width: 150px;">Criterion <span class="text-danger">*</span></th>
                                <th style="width: 60px; min-width: 60px;">Rank <span class="text-danger">*</span></th>
                                <th style="width: 100px; min-width: 100px;">Supplier Sampling</th>
                                <th style="width: 100px; min-width: 100px;">AAIJ Sampling</th>
                                <th style="width: 120px; min-width: 120px;">Remark / Method</th>
                                <th style="width: 50px; min-width: 50px;"></th>
                            </tr>
                        </thead>
                        <tbody id="inspectionItemsBody">
                            <!-- Items will be added here -->
                        </tbody>
                    </table>
                </div>
                <div id="noItemsMessage" class="alert alert-info m-3">
                    <i class="ti ti-info-circle me-2"></i>
                    Click "Add Item" button above to add inspection items
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
window.inspectionStandardId = null;
window.drawings = [];
window.inspectionItems = [];
let drawingCounter = 0;
let itemCounter = 0;

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
    
    console.log(`🖼️ Uploading file for drawing ${index}:`, file.name, file.size);
    
    if (file.size > 10 * 1024 * 1024) {
        Swal.fire('Error', 'File exceeds 10MB limit', 'error');
        return;
    }
    
    const reader = new FileReader();
    reader.onload = function(e) {
        console.log(`✅ File loaded, saving to drawing ${index}`);
        window.drawings[index].file_path = e.target.result;
        window.drawings[index].file_name = file.name;
        
        console.log(`📊 Drawing ${index} updated:`, {
            file_path: !!window.drawings[index].file_path,
            file_name: window.drawings[index].file_name,
            file_size: file.size
        });
        
        document.getElementById(`drawing-file-${index}`).innerHTML = `
            <div class="alert alert-success">
                <i class="ti ti-check"></i> ${file.name} (${formatFileSize(file.size)})
            </div>
        `;
    };
    reader.onerror = function(error) {
        console.error(`❌ Error reading file for drawing ${index}:`, error);
        Swal.fire('Error', 'Failed to read file', 'error');
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
    console.log('➕ Adding new inspection item');
    console.log('📊 Current items count:', window.inspectionItems.length);
    
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
    console.log('📊 After push, total items:', window.inspectionItems.length);
    console.log('📦 Items array:', window.inspectionItems);
    
    // Force re-render
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
    console.log('🔄 Rendering inspection items, count:', window.inspectionItems.length);
    const tbody = document.getElementById('inspectionItemsBody');
    const noItemsMessage = document.getElementById('noItemsMessage');
    const inspectionItemsTable = document.getElementById('inspectionItemsTable');
    
    if (!tbody) {
        console.error('❌ tbody element not found!');
        return;
    }
    
    tbody.innerHTML = '';
    
    if (window.inspectionItems.length === 0) {
        if (noItemsMessage) noItemsMessage.style.display = 'block';
        if (inspectionItemsTable) inspectionItemsTable.style.display = 'none';
        return;
    }
    
    if (noItemsMessage) noItemsMessage.style.display = 'none';
    if (inspectionItemsTable) inspectionItemsTable.style.display = 'table';
    
    window.inspectionItems.forEach((item, index) => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td class="text-center align-middle" style="padding: 0.5rem;">${index + 1}</td>
            <td style="padding: 0.25rem;"><input type="text" class="form-control form-control-sm" style="min-width: 140px;" value="${item.inspection_item || ''}" onchange="updateInspectionItem(${index}, 'inspection_item', this.value)" required></td>
            <td style="padding: 0.25rem;"><input type="text" class="form-control form-control-sm" style="min-width: 110px;" value="${item.inspection_instrument || ''}" onchange="updateInspectionItem(${index}, 'inspection_instrument', this.value)"></td>
            <td style="padding: 0.25rem;"><input type="text" class="form-control form-control-sm" style="min-width: 140px;" value="${item.inspection_criterion || ''}" onchange="updateInspectionItem(${index}, 'inspection_criterion', this.value)" required></td>
            <td style="padding: 0.25rem;"><input type="text" class="form-control form-control-sm text-center" style="min-width: 50px;" value="${item.rank || ''}" onchange="updateInspectionItem(${index}, 'rank', this.value)" required></td>
            <td style="padding: 0.25rem;"><input type="text" class="form-control form-control-sm" style="min-width: 90px;" value="${item.supplier_sampling || ''}" onchange="updateInspectionItem(${index}, 'supplier_sampling', this.value)"></td>
            <td style="padding: 0.25rem;"><input type="text" class="form-control form-control-sm" style="min-width: 90px;" value="${item.aaij_sampling || ''}" onchange="updateInspectionItem(${index}, 'aaij_sampling', this.value)"></td>
            <td style="padding: 0.25rem;"><input type="text" class="form-control form-control-sm" style="min-width: 110px;" value="${item.remark_inspection_method || ''}" onchange="updateInspectionItem(${index}, 'remark_inspection_method', this.value)"></td>
            <td class="text-center align-middle" style="padding: 0.25rem;">
                <button type="button" class="btn btn-sm btn-danger" onclick="removeInspectionItem(${index})" style="padding: 0.25rem 0.5rem;">
                    <i class="ti ti-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });
    console.log('✅ Rendered', window.inspectionItems.length, 'items');
}

// ===== WEIGHT INPUT MANAGEMENT =====
function incrementWeight() {
    const weightInput = document.getElementById('weight');
    const currentValue = parseFloat(weightInput.value) || 0;
    const newValue = Math.round((currentValue + 0.1) * 100) / 100;
    weightInput.value = newValue.toFixed(2);
}

function decrementWeight() {
    const weightInput = document.getElementById('weight');
    const currentValue = parseFloat(weightInput.value) || 0;
    const newValue = Math.max(0, Math.round((currentValue - 0.1) * 100) / 100);
    weightInput.value = newValue.toFixed(2);
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
    
    fetch(`/api/is/form/${token}`)
        .then(response => {
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            return response.json();
        })
        .then(data => {
            console.log('✅ Form data loaded:', data);
            
            window.inspectionStandardId = data.id;
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
    // Check and display revision banner if document is in revision status
    if (data.request_document && data.request_document.status === 'REVISE' && data.request_document.revised_by_team) {
        displayRevisionBanner(data.request_document);
    }
    
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
    
    // Drawings - only populate from server if window.drawings is empty
    // This prevents overwriting user's new drawings with server data
    if (data.drawings && data.drawings.length > 0 && window.drawings.length === 0) {
        window.drawings = data.drawings;
        console.log('📥 Loaded', window.drawings.length, 'drawings from server');
    }
    
    // Inspection Items - only populate from server if window.inspectionItems is empty
    if (data.inspection_items && data.inspection_items.length > 0 && window.inspectionItems.length === 0) {
        window.inspectionItems = data.inspection_items;
        console.log('📥 Loaded', window.inspectionItems.length, 'inspection items from server');
    }
    
    // Always render inspection items to update UI
    renderInspectionItems();
}

// Display revision banner with revision information
function displayRevisionBanner(requestDocument) {
    const banner = document.getElementById('revisionBanner');
    if (!banner) return;
    
    // Get team label
    const teamLabels = {
        'qa': 'Quality Assurance (QA)',
        'purchasing': 'Purchasing',
        'ppc': 'Production Planning & Control (PPC)'
    };
    const teamLabel = teamLabels[requestDocument.revised_by_team] || requestDocument.revised_by_team;
    
    // Populate banner content
    document.getElementById('revisionTeam').textContent = teamLabel;
    document.getElementById('revisionNote').textContent = requestDocument.revision_note || 'Tidak ada catatan';
    
    // Format revision date
    if (requestDocument.revised_at) {
        const revDate = new Date(requestDocument.revised_at);
        document.getElementById('revisionDate').textContent = revDate.toLocaleString('id-ID', {
            day: '2-digit',
            month: 'long',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }
    
    // Show the banner
    banner.style.display = 'block';
    
    console.log('📋 Revision banner displayed for team:', requestDocument.revised_by_team);
}

function collectFormData() {
    return {
        generalInformation: {
            model: document.getElementById('model').value,
            dateIssued: document.getElementById('date_issued').value,
            refNo: document.getElementById('ref_no').value,
            partRoute: document.getElementById('part_route').value,
            partNo: document.getElementById('part_no').value,
            partName: document.getElementById('part_name').value,
            manufacturer: document.getElementById('manufacturer').value,
            material: document.getElementById('material').value,
            generalTolerance: document.getElementById('general_tolerance').value,
            weight: document.getElementById('weight').value,
            revisionDate: document.getElementById('revision_date').value,
            revisionRecord: document.getElementById('revision_record').value,
        },
        drawings: window.drawings.map(d => ({
            id: d.id,
            drawingName: d.drawing_name,
            drawingNo: d.drawing_no,
            revision: d.revision,
            description: d.description,
            filePath: d.file_path
        })),
        inspectionItems: window.inspectionItems.map(item => ({
            id: item.id,
            inspectionItem: item.inspection_item,
            inspectionInstrument: item.inspection_instrument,
            inspectionCriterion: item.inspection_criterion,
            rank: item.rank,
            supplierSampling: item.supplier_sampling,
            aaijSampling: item.aaij_sampling,
            remarkInspectionMethod: item.remark_inspection_method
        }))
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
    
    // Add validation for weight input
    const weightInput = document.getElementById('weight');
    if (weightInput) {
        weightInput.addEventListener('blur', function() {
            let value = parseFloat(this.value);
            // If value is negative, set to 0
            if (value < 0) {
                this.value = '0.00';
            } else if (value >= 0) {
                // Format to 2 decimal places
                this.value = value.toFixed(2);
            }
        });
        
        // Prevent negative input while typing
        weightInput.addEventListener('input', function() {
            if (this.value < 0) {
                this.value = 0;
            }
        });
    }
}

// ===== FORM SUBMISSION =====
function saveDraft(silent = false) {
    if (!window.inspectionStandardId) {
        if (!silent) Swal.fire('Error', 'Form not fully loaded. Please refresh the page and try again.', 'error');
        return;
    }

    const formData = collectFormData();
    
    fetch(`/api/is/${window.inspectionStandardId}/draft`, {
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
    
    console.log('📋 Validating drawings:', {
        count: window.drawings.length,
        drawings: window.drawings.map((d, i) => ({
            index: i,
            has_file: !!d.file_path,
            file_name: d.file_name
        }))
    });
    
    if (window.drawings.length === 0) {
        Swal.fire('Validation Error', 'At least one drawing is required', 'warning');
        return false;
    }
    
    // Check if drawings have file_path (file uploaded)
    for (let i = 0; i < window.drawings.length; i++) {
        if (!window.drawings[i].file_path) {
            console.warn(`❌ Drawing ${i} has no file_path`);
            Swal.fire('Validation Error', `Drawing #${i + 1}: File is required. Please upload a file.`, 'warning');
            return false;
        }
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
    
    console.log('✅ Validation passed');
    return true;
}

function submitForm() {
    if (!validate()) return;

    if (!window.inspectionStandardId) {
        Swal.fire('Error', 'Form not fully loaded. Please refresh the page and try again.', 'error');
        return;
    }

    const formData = collectFormData();
    
    console.log('📤 Submitting form data:', {
        inspectionStandardId: window.inspectionStandardId,
        drawingsCount: formData.drawings.length,
        itemsCount: formData.inspectionItems.length,
        generalInfo: formData.generalInformation
    });
    
    // Show loading indicator
    Swal.fire({
        title: 'Submitting...',
        text: 'Please wait while we process your submission',
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    fetch(`/api/is/${window.inspectionStandardId}/submit`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(formData)
    }).then(async r => {
        console.log('📥 Response received:', {
            status: r.status,
            statusText: r.statusText,
            contentType: r.headers.get('content-type')
        });
        
        // Check content type before trying to parse
        const contentType = r.headers.get('content-type');
        
        if (!contentType || !contentType.includes('application/json')) {
            // Try to get the response text to see what the server returned
            const responseText = await r.text();
            console.error('❌ Server returned HTML/non-JSON response:', responseText.substring(0, 500));
            console.error('❌ Response status:', r.status, r.statusText);
            
            throw new Error(`Server error (${r.status}): The server encountered an error. Please check the server logs for details.`);
        }
        
        const data = await r.json();
        
        if (!r.ok) {
            throw new Error(data.message || `HTTP ${r.status}: ${r.statusText}`);
        }
        
        return data;
    }).then(result => {
        Swal.close();
        
        if (result.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'Inspection Standard submitted successfully',
                confirmButtonText: 'OK'
            }).then(() => {
                window.location.href = result.redirect || '/supplier/dashboard';
            });
        } else {
            Swal.fire('Error', result.message || 'Failed to submit form', 'error');
        }
    }).catch(error => {
        console.error('❌ Error submitting form:', error);
        Swal.close();
        Swal.fire({
            icon: 'error',
            title: 'Submission Failed',
            text: error.message || 'An unexpected error occurred. Please try again.',
            footer: 'If the problem persists, please contact support.'
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
